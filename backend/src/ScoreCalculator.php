<?php

declare(strict_types=1);

namespace App;

use App\Domain\ExamSubjectResult;
use App\Domain\LanguageExam;
use App\Enums\ExamLevel;
use App\Enums\Language;
use App\Enums\LanguageExamType;
use App\Enums\Subject;
use App\Exceptions\MandatorySubjectsMissingException;

final class ScoreCalculator
{
    /** @var list<ExamSubjectResult> */
    private array $examSubjectResults = [];

    /** @var list<LanguageExam> */
    private array $languageExams = [];

    public function addExamSubjectResult(string $subject, string $level, int $resultPercentage): void
    {
        $this->examSubjectResults[] = new ExamSubjectResult(
            subject: Subject::fromInput($subject),
            level: ExamLevel::fromInput($level),
            resultPercentage: $resultPercentage,
        );
    }

    public function addLanguageExam(string $language, string $level, string $type = 'B2'): void
    {
        $this->languageExams[] = new LanguageExam(
            language: Language::fromInput($language),
            level: strtoupper(trim($level)),
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

    /** @return list<array{language:string, level:string, type:string}> */
    public function getLanguageExams(): array
    {
        return array_map(
            static fn (LanguageExam $item): array => $item->toArray(),
            $this->languageExams,
        );
    }

    public function calculateTotalScore(): int
    {
        $this->validateMandatorySubjects();
        
        $subjectScore = $this->calculateSubjectScore();
        $languageExamBonus = $this->calculateLanguageExamBonus();

        return $this->calculateSubjectScore() + $this->calculateExtraScore();
    }

    private function validateMandatorySubjects(): void
    {
        $requiredSubjects = [
            Subject::HUNGARIAN_LANGUAGE_AND_LITERATURE,
            Subject::HISTORY,
            Subject::MATHEMATICS,
        ];

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

    private function calculateSubjectScore(): int
    {
        $totalScore = 0;

        foreach ($this->examSubjectResults as $result) {
            // Base score from percentage (0-100)
            $baseScore = $result->resultPercentage;

            // Multiply by 2 for advanced level, 1 for middle level
            $multiplier = match ($result->level) {
                ExamLevel::ADVANCED => 2,
                ExamLevel::MIDDLE => 1,
            };

            $totalScore += $baseScore * $multiplier;
        }

        return $totalScore;
    }

    private function calculateLanguageExamBonus(): int
    {
        $bonus = 0;

        foreach ($this->languageExams as $exam) {
            $bonus += match ($exam->type) {
                LanguageExamType::B2 => 28,
                LanguageExamType::C2 => 40,
            };
        }

        return $bonus;
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
