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

    public function getLabel(): string
    {
        return match ($this) {
            self::HUNGARIAN_LANGUAGE_AND_LITERATURE => 'Magyar nyelv és irodalom',
            self::HISTORY => 'Történelem',
            self::MATHEMATICS => 'Matematika',
            self::PHYSICS => 'Fizika',
            self::BIOLOGY => 'Biológia',
            self::INFORMATICS => 'Informatika',
            self::CHEMISTRY => 'Kémia',
        };
    }

    public static function fromInput(string $input): self
    {
        return match ($input) {
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
}
