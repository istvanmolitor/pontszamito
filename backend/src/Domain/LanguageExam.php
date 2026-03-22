<?php

declare(strict_types=1);

namespace App\Domain;

use App\Enums\Language;
use App\Enums\LanguageExamType;
use InvalidArgumentException;

final readonly class LanguageExam
{
    public function __construct(
        public Language $language,
        public LanguageExamType $type = LanguageExamType::B2
    ) {
    }

    /** @return array{language:string, type:string} */
    public function toArray(): array
    {
        return [
            'language' => $this->language->value,
            'type' => $this->type->value,
        ];
    }
}
