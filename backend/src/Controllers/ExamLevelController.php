<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ApiResource;
use App\Controller;
use App\Enums\ExamLevel;

final class ExamLevelController extends Controller
{
    public function index(): ApiResource
    {
        return $this->resource([
            'levels' => array_map(
                fn (ExamLevel $level): array => [
                    'value' => $level->value,
                    'label' => $level->getLabel(),
                ],
                ExamLevel::cases(),
            ),
        ]);
    }
}

