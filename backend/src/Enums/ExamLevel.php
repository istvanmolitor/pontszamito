<?php

declare(strict_types=1);

namespace App\Enums;

use InvalidArgumentException;

enum ExamLevel: string
{
    case MIDDLE = 'middle';
    case ADVANCED = 'advanced';

    public function getLabel(): string
    {
        return match ($this) {
            self::MIDDLE => 'Középszint',
            self::ADVANCED => 'Emelt szint',
        };
    }

    public static function fromInput(string $input): self
    {
        $normalized = mb_strtolower(trim($input), 'UTF-8');

        return match ($normalized) {
            'middle', 'kozep', 'középszint', 'közép' => self::MIDDLE,
            'advanced', 'emelt', 'emelt szint' => self::ADVANCED,
            default => throw new InvalidArgumentException(
                'Exam level must be one of: middle, advanced, kozep, emelt.'
            ),
        };
    }
}
