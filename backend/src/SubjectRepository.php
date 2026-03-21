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
            ['id' => 1, 'code' => 'HUN101', 'name' => 'Magyar nyelv és irodalom', 'credits' => 5],
            ['id' => 2, 'code' => 'HIS101', 'name' => 'Történelem', 'credits' => 5],
            ['id' => 3, 'code' => 'MAT101', 'name' => 'Matematika', 'credits' => 5],
            ['id' => 4, 'code' => 'PHY101', 'name' => 'Fizika', 'credits' => 4],
            ['id' => 5, 'code' => 'BIO101', 'name' => 'Biológia', 'credits' => 4],
            ['id' => 6, 'code' => 'INF101', 'name' => 'Informatika', 'credits' => 5],
            ['id' => 7, 'code' => 'CHE101', 'name' => 'Kémia', 'credits' => 4],
        ];
    }
}
