<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\UniversityProgram;

final class UniversityProgramRepository
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public function all(): array
    {
        return array_map(
            static fn (UniversityProgram $program): array => [
                'value' => $program->value,
                'label' => $program->getLabel(),
            ],
            UniversityProgram::cases()
        );
    }
}

