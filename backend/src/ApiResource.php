<?php

declare(strict_types=1);

namespace App;

use JsonSerializable;

final class ApiResource implements JsonSerializable
{
    /** @var array<mixed> */
    private array $data;
    private int $statusCode;

    /** @param array<mixed> $data */
    public function __construct(array $data, int $statusCode = 200)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    /** @return array<mixed> */
    public function toArray(): array
    {
        return $this->data;
    }

    /** @return array<mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function send(): void
    {
        self::sendDefaultHeaders();
        http_response_code($this->statusCode);

        echo json_encode(
            $this,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    public static function handlePreflight(): bool
    {
        self::sendDefaultHeaders();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'OPTIONS') {
            return false;
        }

        http_response_code(204);

        return true;
    }

    private static function sendDefaultHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json; charset=utf-8');
    }
}
