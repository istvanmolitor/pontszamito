<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ApiResource;
use App\Controller;

final class LanguageExamController extends Controller
{
    public function index(): ApiResource
    {
        return $this->resource([
            'languageExams' => [
                ['id' => 1, 'language' => 'angol', 'level' => 'B2', 'type' => 'complex'],
                ['id' => 2, 'language' => 'nemet', 'level' => 'B2', 'type' => 'complex'],
                ['id' => 3, 'language' => 'francia', 'level' => 'C1', 'type' => 'complex'],
            ],
        ]);
    }
}
