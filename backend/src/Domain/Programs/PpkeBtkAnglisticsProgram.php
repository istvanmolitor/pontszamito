<?php

declare(strict_types=1);

namespace App\Domain\Programs;

use App\Domain\AbstractUniversityProgram;
use App\Enums\Subject;

final class PpkeBtkAnglisticsProgram extends AbstractUniversityProgram
{
    public function getName(): string
    {
        return 'PPKE BTK – Anglisztika';
    }

    /**
     * @return list<Subject>
     */
    public function getMandatorySubjects(): array
    {
        return [
            Subject::ENGLISH,
        ];
    }

    /**
     * @return list<list<Subject>>
     */
    public function getMandatorySelectableSubjects(): array
    {
        return [
            [
                Subject::FRENCH,
                Subject::GERMAN,
                Subject::ITALIAN,
                Subject::RUSSIAN,
                Subject::SPANISH,
                Subject::HISTORY,
            ],
        ];
    }

    /**
     * @return list<Subject>
     */
    public function getAdvancedLevelRequiredSubjects(): array
    {
        return [
            Subject::ENGLISH,
        ];
    }
}



