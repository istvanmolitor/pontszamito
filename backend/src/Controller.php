<?php

declare(strict_types=1);

namespace App;

abstract class Controller
{
    /** @param array<mixed> $data */
    protected function resource(array $data): ApiResource
    {
        return new ApiResource($data);
    }

    public function errorResponse(string $message, int $statusCode = 400): ApiResource
    {
        return new ApiResource([
            'error' => $message,
        ], $statusCode);
    }

    /** @return array<mixed> */
    protected function getJsonBody(): array
    {
        $content = file_get_contents('php://input');
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }
}
