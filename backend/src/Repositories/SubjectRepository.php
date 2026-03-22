<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Subject;

final class SubjectRepository
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public function all(): array
    {
        return array_map(
            static fn (Subject $subject): array => [
                'value' => $subject->value,
                'label' => $subject->getLabel(),
            ],
            Subject::cases()
        );
    }
}
