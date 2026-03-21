<?php

declare(strict_types=1);

namespace App\Enums;

use InvalidArgumentException;

enum Subject: string
{
    case HUNGARIAN_LANGUAGE_AND_LITERATURE = 'magyar nyelv és irodalom';
    case HISTORY = 'történelem';
    case MATHEMATICS = 'matematika';
    case PHYSICS = 'fizika';
    case BIOLOGY = 'biológia';
    case INFORMATICS = 'informatika';
    case CHEMISTRY = 'kémia';

    public static function fromInput(string $input): self
    {
        return match (self::normalize($input)) {
            'magyar_nyelv_es_irodalom', 'magyar', 'hun101' => self::HUNGARIAN_LANGUAGE_AND_LITERATURE,
            'tortenelem', 'history', 'his101' => self::HISTORY,
            'matematika', 'math', 'mat101' => self::MATHEMATICS,
            'fizika', 'physics', 'phy101', 'fiz110' => self::PHYSICS,
            'biologia', 'biology', 'bio101' => self::BIOLOGY,
            'informatika', 'informatics', 'inf101', 'inf201' => self::INFORMATICS,
            'kemia', 'chemistry', 'che101' => self::CHEMISTRY,
            default => throw new InvalidArgumentException(
                'Subject must be one of: magyar nyelv és irodalom, történelem, matematika, fizika, biológia, informatika, kémia.'
            ),
        };
    }

    private static function normalize(string $input): string
    {
        $normalized = strtr(trim($input), [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ö' => 'o',
            'ő' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ű' => 'u',
            'Á' => 'a',
            'É' => 'e',
            'Í' => 'i',
            'Ó' => 'o',
            'Ö' => 'o',
            'Ő' => 'o',
            'Ú' => 'u',
            'Ü' => 'u',
            'Ű' => 'u',
        ]);

        $normalized = strtolower($normalized);

        return str_replace([' ', '-'], '_', $normalized);
    }
}
