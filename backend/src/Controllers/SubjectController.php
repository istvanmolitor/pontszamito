<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ApiResource;
use App\Controller;
use App\Repositories\SubjectRepository;

final class SubjectController extends Controller
{
    private SubjectRepository $repository;

    public function __construct(?SubjectRepository $repository = null)
    {
        $this->repository = $repository ?? new SubjectRepository();
    }

    public function index(): ApiResource
    {
        return $this->resource(['subjects' => $this->repository->all()]);
    }
}
