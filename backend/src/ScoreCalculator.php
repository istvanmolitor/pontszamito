<?php

declare(strict_types=1);

namespace App;

use App\Domain\ExamSubjectResult;
use App\Domain\LanguageExam;
use App\Enums\ExamLevel;
use App\Enums\Language;
use App\Enums\LanguageExamType;
use App\Enums\Subject;

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
}
