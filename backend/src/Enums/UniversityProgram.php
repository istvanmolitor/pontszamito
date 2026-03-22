<?php

declare(strict_types=1);

namespace App\Enums;

use App\Domain\AbstractUniversityProgram;
use App\Domain\Programs\ElteIkProgrammingProgram;
use App\Domain\Programs\PpkeBtkAnglisticsProgram;

enum UniversityProgram: string
{
    case ELTE_IK_PROGRAMMING = 'elte_ik_programming';
    case PPKE_BTK_ANGLISTICS = 'ppke_btk_anglistics';

    public function getLabel(): string
    {
        return match ($this) {
            self::ELTE_IK_PROGRAMMING => 'ELTE IK – Programtervező informatikus',
            self::PPKE_BTK_ANGLISTICS => 'PPKE BTK – Anglisztika',
        };
    }

    public function getProgramInstance(): AbstractUniversityProgram
    {
        return match ($this) {
            self::ELTE_IK_PROGRAMMING => new ElteIkProgrammingProgram(),
            self::PPKE_BTK_ANGLISTICS => new PpkeBtkAnglisticsProgram(),
        };
    }
}

