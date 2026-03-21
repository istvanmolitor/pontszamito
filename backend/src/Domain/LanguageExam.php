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
        public string $level,
        public LanguageExamType $type = LanguageExamType::B2
    ) {
        $this->validateLevel($this->level);
    }

    /** @return array{language:string, level:string, type:string} */
    public function toArray(): array
    {
        return [
            'language' => $this->language->value,
            'level' => strtoupper(trim($this->level)),
            'type' => $this->type->value,
        ];
    }

    private function validateLevel(string $level): void
    {
        if (trim($level) === '') {
            throw new InvalidArgumentException('Language exam level is required.');
        }
    }
}
