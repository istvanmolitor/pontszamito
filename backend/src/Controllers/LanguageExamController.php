<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ApiResource;
use App\Controller;
use App\Enums\Language;
use App\Enums\LanguageExamType;

final class LanguageExamController extends Controller
{
    public function index(): ApiResource
    {
        return $this->resource([
            'languages' => array_map(
                fn (Language $language): array => [
                    'value' => $language->value,
                    'label' => $language->getLabel(),
                ],
                Language::cases(),
            ),
            'examTypes' => array_map(
                fn (LanguageExamType $examType): array => [
                    'value' => $examType->value,
                    'label' => $examType->getLabel(),
                ],
                LanguageExamType::cases(),
            ),
        ]);
    }
}
