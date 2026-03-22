<?php

declare(strict_types=1);

namespace App\Domain;

use App\Enums\ExamLevel;
use App\Enums\Subject;

abstract class AbstractUniversityProgram
{
    /**
     * @return list<Subject>
     */
    abstract public function getMandatorySubjects(): array;

    /**
     * @return list<list<Subject>>
     */
    abstract public function getMandatorySelectableSubjects(): array;

    abstract public function getName(): string;

    /**
     * Get subjects that must be taken at advanced level
     * @return list<Subject>
     */
    public function getAdvancedLevelRequiredSubjects(): array
    {
        return [];
    }
}


