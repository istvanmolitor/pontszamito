<?php

declare(strict_types=1);

namespace App\Enums;

use InvalidArgumentException;

enum LanguageExamType: string
{
    case B2 = 'B2';
    case C2 = 'C2';

    public function getLabel(): string
    {
        return match ($this) {
            self::B2 => 'B2 (Középfok)',
            self::C2 => 'C2 (Felsőfok)',
        };
    }

    public static function fromInput(string $input): self
    {
        return match (strtolower(trim($input))) {
            'b2', 'középfok', 'kozepfok' => self::B2,
            'c2', 'felsőfok', 'felsofok' => self::C2,
            default => throw new InvalidArgumentException(
                'Language exam type must be one of: B2, C2.'
            ),
        };
    }
}
