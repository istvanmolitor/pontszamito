<?php

declare(strict_types=1);

namespace App\Enums;

use InvalidArgumentException;

enum ExamLevel: string
{
    case MIDDLE = 'middle';
    case ADVANCED = 'advanced';

    public static function fromInput(string $input): self
    {
        return match (strtolower(trim($input))) {
            'middle' => self::MIDDLE,
            'advanced' => self::ADVANCED,
            default => throw new InvalidArgumentException(
                'Exam level must be one of: middle, advanced, kozep, emelt.'
            ),
        };
    }
}
