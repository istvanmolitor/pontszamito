<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ApiResource;
use App\Controller;
use App\Repositories\UniversityProgramRepository;

final class UniversityProgramController extends Controller
{
    private UniversityProgramRepository $repository;

    public function __construct(?UniversityProgramRepository $repository = null)
    {
        $this->repository = $repository ?? new UniversityProgramRepository();
    }

    public function index(): ApiResource
    {
        return $this->resource(['programs' => $this->repository->all()]);
    }
}

