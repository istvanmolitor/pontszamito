<?php

declare(strict_types=1);

namespace App;

final class SubjectRepository
{
    /**
     * @return array<int, array{id:int, code:string, name:string, credits:int}>
     */
    public function all(): array
    {
        return [
            ['id' => 1, 'code' => 'MAT101', 'name' => 'Matematika', 'credits' => 5],
            ['id' => 2, 'code' => 'INF201', 'name' => 'Programozás alapjai', 'credits' => 6],
            ['id' => 3, 'code' => 'FIZ110', 'name' => 'Fizika', 'credits' => 4],
            ['id' => 4, 'code' => 'ANG100', 'name' => 'Szakmai angol', 'credits' => 3],
        ];
    }
}
