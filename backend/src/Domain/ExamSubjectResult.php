<?php

declare(strict_types=1);

namespace App\Domain;

use App\Enums\ExamLevel;
use App\Enums\Subject;
use InvalidArgumentException;

final readonly class ExamSubjectResult
{
    public function __construct(
        public Subject $subject,
        public ExamLevel $level,
        public int $resultPercentage
    ) {
        $this->validateResultPercentage($this->resultPercentage);
    }

    /** @return array{subject:string, level:string, resultPercentage:int} */
    public function toArray(): array
    {
        return [
            'subject' => $this->subject->value,
            'level' => $this->level->value,
            'resultPercentage' => $this->resultPercentage,
        ];
    }

    private function validateResultPercentage(int $resultPercentage): void
    {
        if ($resultPercentage < 0 || $resultPercentage > 100) {
            throw new InvalidArgumentException('Result percentage must be an integer between 0 and 100.');
        }
    }
}
