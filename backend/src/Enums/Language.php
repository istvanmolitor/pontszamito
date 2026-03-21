<?php

declare(strict_types=1);

namespace App\Enums;

use InvalidArgumentException;

enum Language: string
{
    case ENGLISH = 'english';
    case GERMAN = 'german';
    case FRENCH = 'french';
    case SPANISH = 'spanish';
    case ITALIAN = 'italian';
    case RUSSIAN = 'russian';

    public function getLabel(): string
    {
        return match ($this) {
            self::ENGLISH => 'Angol',
            self::GERMAN => 'Német',
            self::FRENCH => 'Francia',
            self::SPANISH => 'Spanyol',
            self::ITALIAN => 'Olasz',
            self::RUSSIAN => 'Orosz',
        };
    }

    public static function fromInput(string $input): self
    {
        return match (strtolower(trim($input))) {
            'english', 'angol' => self::ENGLISH,
            'german', 'német', 'nemet' => self::GERMAN,
            'french', 'francia' => self::FRENCH,
            'spanish', 'spanyol' => self::SPANISH,
            'italian', 'olasz' => self::ITALIAN,
            'russian', 'orosz' => self::RUSSIAN,
            default => throw new InvalidArgumentException(
                'Language must be one of: english, german, french, spanish, italian, russian.'
            ),
        };
    }
}
