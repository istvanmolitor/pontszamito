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
}
