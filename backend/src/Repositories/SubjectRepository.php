<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Subject;

final class SubjectRepository
{
    /**
     * @return list<string>
     */
    public function all(): array
    {
        return array_map(
            static fn (Subject $subject): string => $subject->value,
            Subject::cases()
        );
    }
}
