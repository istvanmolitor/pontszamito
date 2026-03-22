<?php

declare(strict_types=1);

namespace App;

use App\Domain\AbstractUniversityProgram;
use App\Domain\ExamSubjectResult;
use App\Domain\LanguageExam;
use App\Enums\ExamLevel;
use App\Enums\Language;
use App\Enums\LanguageExamType;
use App\Enums\Subject;
use App\Exceptions\DuplicateSubjectException;
use App\Exceptions\FailedExamException;
use App\Exceptions\MandatorySubjectsMissingException;
use App\Exceptions\MandatorySelectableSubjectsMissingException;

final class ScoreCalculator
{
    /** @var list<ExamSubjectResult> */
    private array $examSubjectResults = [];

    /** @var list<LanguageExam> */
    private array $languageExams = [];

    private AbstractUniversityProgram $universityProgram;

    public function __construct(AbstractUniversityProgram $universityProgram)
    {
        $this->universityProgram = $universityProgram;
    }

    public function addExamSubjectResult(string $subject, string $level, int $resultPercentage): void
    {
        $subjectEnum = Subject::fromInput($subject);
        $levelEnum = ExamLevel::fromInput($level);

        // Check if this subject with the same level already exists
        foreach ($this->examSubjectResults as $existingResult) {
            if ($existingResult->subject === $subjectEnum && $existingResult->level === $levelEnum) {
                throw new DuplicateSubjectException(
                    "A(z) {$subjectEnum->getLabel()} tantárgy ({$levelEnum->getLabel()} szint) már hozzá van adva."
                );
            }
        }

        $this->examSubjectResults[] = new ExamSubjectResult(
            subject: $subjectEnum,
            level: $levelEnum,
            resultPercentage: $resultPercentage,
        );
    }

    public function addLanguageExam(string $language, string $type = 'B2'): void
    {
        $this->languageExams[] = new LanguageExam(
            language: Language::fromInput($language),
            type: LanguageExamType::fromInput($type),
        );
    }

    /** @return list<array{subject:string, level:string, resultPercentage:int}> */
    public function getExamSubjectResults(): array
    {
        return array_map(
            static fn (ExamSubjectResult $item): array => $item->toArray(),
            $this->examSubjectResults,
        );
    }

    /** @return list<array{language:string, type:string}> */
    public function getLanguageExams(): array
    {
        return array_map(
            static fn (LanguageExam $item): array => $item->toArray(),
            $this->languageExams,
        );
    }

    /** @return list<Subject> */
    public function getMandatorySubjects(): array
    {
        return $this->universityProgram->getMandatorySubjects();
    }

    /** @return list<list<Subject>> */
    public function getMandatorySelectableSubjects(): array
    {
        return $this->universityProgram->getMandatorySelectableSubjects();
    }

    public function validate(): void
    {
        $this->validateMandatorySubjects();
        $this->validateMandatorySelectableSubjects();
        $this->validateAdvancedLevelRequiredSubjects();
        $this->validateMinimumPercentage();
    }

    public function calculateTotalScore(): int
    {
        $this->validate();

        return $this->calculateSubjectScore() + $this->calculateExtraScore();
    }

    private function validateMandatorySubjects(): void
    {
        $requiredSubjects = $this->getMandatorySubjects();

        $providedSubjects = array_map(
            static fn(ExamSubjectResult $result): Subject => $result->subject,
            $this->examSubjectResults
        );

        $missingSubjects = [];
        foreach ($requiredSubjects as $required) {
            if (!in_array($required, $providedSubjects, true)) {
                $missingSubjects[] = $required->getLabel();
            }
        }

        if ($missingSubjects !== []) {
            $missingList = implode(', ', $missingSubjects);
            throw new MandatorySubjectsMissingException(
                "A következő kötelező tantárgyak hiányoznak: {$missingList}"
            );
        }
    }

    private function validateMandatorySelectableSubjects(): void
    {
        $selectableGroups = $this->getMandatorySelectableSubjects();

        if (empty($selectableGroups)) {
            return;
        }

        $providedSubjects = array_map(
            static fn(ExamSubjectResult $result): Subject => $result->subject,
            $this->examSubjectResults
        );

        foreach ($selectableGroups as $group) {
            $hasAtLeastOne = false;

            foreach ($group as $selectableSubject) {
                if (in_array($selectableSubject, $providedSubjects, true)) {
                    $hasAtLeastOne = true;
                    break;
                }
            }

            if (!$hasAtLeastOne) {
                $subjectLabels = array_map(
                    static fn(Subject $subject): string => $subject->getLabel(),
                    $group
                );
                $subjectList = implode(', ', $subjectLabels);
                throw new MandatorySelectableSubjectsMissingException(
                    "Legalább egy kötelezően választható tantárgyból érettségit kell tenni. Válasszon egyet a következők közül: {$subjectList}"
                );
            }
        }
    }

    private function validateAdvancedLevelRequiredSubjects(): void
    {
        $advancedLevelRequired = $this->universityProgram->getAdvancedLevelRequiredSubjects();

        if (empty($advancedLevelRequired)) {
            return;
        }

        foreach ($advancedLevelRequired as $requiredSubject) {
            $found = false;
            $foundAtAdvancedLevel = false;

            foreach ($this->examSubjectResults as $result) {
                if ($result->subject === $requiredSubject) {
                    $found = true;
                    if ($result->level === ExamLevel::ADVANCED) {
                        $foundAtAdvancedLevel = true;
                        break;
                    }
                }
            }

            if (!$found) {
                throw new MandatorySubjectsMissingException(
                    "A következő kötelező tantárgy hiányzik: {$requiredSubject->getLabel()}"
                );
            }

            if (!$foundAtAdvancedLevel) {
                throw new MandatorySubjectsMissingException(
                    "A(z) {$requiredSubject->getLabel()} tantárgyból emelt szintű érettségi szükséges."
                );
            }
        }
    }

    private function validateMinimumPercentage(): void
    {
        foreach ($this->examSubjectResults as $result) {
            if ($result->resultPercentage < 20) {
                throw new FailedExamException(
                    "A vizsga sikertelen: {$result->subject->getLabel()} tantárgy eredménye ({$result->resultPercentage}%) 20% alatt van."
                );
            }
        }
    }

    private function calculateSubjectScore(): int
    {
        $mandatorySubjects = $this->getMandatorySubjects();
        $mandatorySelectableGroups = $this->getMandatorySelectableSubjects();

        // Kötelező tantárgyak pontértékeinek összege
        $mandatoryScore = 0;
        foreach ($this->examSubjectResults as $result) {
            if (in_array($result->subject, $mandatorySubjects, true)) {
                // Base score from percentage (0-100)
                $baseScore = $result->resultPercentage;

                // Multiply by 2 for advanced level, 1 for middle level
                $multiplier = match ($result->level) {
                    ExamLevel::ADVANCED => 2,
                    ExamLevel::MIDDLE => 1,
                };

                $mandatoryScore += $baseScore * $multiplier;
            }
        }

        // Legjobb kötelezően választható tárgy pontértéke
        $bestSelectableScore = 0;

        // Minden kötelezően választható tárgy csoportot végigjárunk
        foreach ($mandatorySelectableGroups as $group) {
            foreach ($this->examSubjectResults as $result) {
                if (in_array($result->subject, $group, true)) {
                    // Base score from percentage (0-100)
                    $baseScore = $result->resultPercentage;

                    // Multiply by 2 for advanced level, 1 for middle level
                    $multiplier = match ($result->level) {
                        ExamLevel::ADVANCED => 2,
                        ExamLevel::MIDDLE => 1,
                    };

                    $score = $baseScore * $multiplier;

                    if ($score > $bestSelectableScore) {
                        $bestSelectableScore = $score;
                    }
                }
            }
        }

        // Az alappontszám = (kötelező pontok + legjobb választható pontok) * 2
        return ($mandatoryScore + $bestSelectableScore) * 2;
    }

    private function calculateLanguageExamBonus(): int
    {
        $languageBonuses = [];

        foreach ($this->languageExams as $exam) {
            $points = match ($exam->type) {
                LanguageExamType::B2 => 28,
                LanguageExamType::C2 => 40,
            };

            $languageKey = $exam->language->value;
            if (!isset($languageBonuses[$languageKey]) || $points > $languageBonuses[$languageKey]) {
                $languageBonuses[$languageKey] = $points;
            }
        }

        return array_sum($languageBonuses);
    }

    public function calculateAdvancedSubjectBonus(): int
    {
        $bonus = 0;

        foreach ($this->examSubjectResults as $result) {
            if ($result->level === ExamLevel::ADVANCED) {
                $bonus += 50;
            }
        }

        return $bonus;
    }

    public function calculateExtraScore(): int
    {
        $totalScore = $this->calculateLanguageExamBonus() + $this->calculateAdvancedSubjectBonus();
        if( $totalScore > 100) {
            $totalScore = 100;
        }
        return $totalScore;
    }
}
