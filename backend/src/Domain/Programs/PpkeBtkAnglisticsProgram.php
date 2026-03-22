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
            Subject::HUNGARIAN_LANGUAGE_AND_LITERATURE,
            Subject::HISTORY,
            Subject::MATHEMATICS,
        ];
    }

    /**
     * @return list<list<Subject>>
     */
    public function getMandatorySelectableSubjects(): array
    {
        return [
            [
                Subject::PHYSICS,
                Subject::BIOLOGY,
                Subject::INFORMATICS,
                Subject::CHEMISTRY,
            ],
        ];
    }
}



