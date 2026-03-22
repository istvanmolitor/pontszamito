<?php

declare(strict_types=1);

namespace App\Enums;

use InvalidArgumentException;

enum Subject: string
{
    case HUNGARIAN_LANGUAGE_AND_LITERATURE = 'hungarian language and literature';
    case HISTORY = 'history';
    case MATHEMATICS = 'mathematics';
    case PHYSICS = 'physics';
    case BIOLOGY = 'biology';
    case INFORMATICS = 'informatics';
    case CHEMISTRY = 'chemistry';

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
        $normalized = mb_strtolower(trim($input), 'UTF-8');
        
        return match ($normalized) {
            'hungarian language and literature', 'magyar nyelv és irodalom', 'magyar nyelv es irodalom', 'magyar', 'hun101' => self::HUNGARIAN_LANGUAGE_AND_LITERATURE,
            'history', 'történelem', 'tortenelem', 'his101' => self::HISTORY,
            'mathematics', 'matematika', 'math', 'mat101' => self::MATHEMATICS,
            'physics', 'fizika', 'phy101', 'fiz110' => self::PHYSICS,
            'biology', 'biológia', 'biologia', 'bio101' => self::BIOLOGY,
            'informatics', 'informatika', 'inf101', 'inf201' => self::INFORMATICS,
            'chemistry', 'kémia', 'kemia', 'che101' => self::CHEMISTRY,
            default => throw new InvalidArgumentException(
                'Subject must be one of: hungarian language and literature, history, mathematics, physics, biology, informatics, chemistry.'
            ),
        };
    }
}
