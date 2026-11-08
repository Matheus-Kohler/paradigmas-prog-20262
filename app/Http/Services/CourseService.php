<?php

namespace App\Http\Services;

use App\Http\Repositories\CourseRepository;

class CourseService extends BaseService
{
    public function __construct(private CourseRepository $courseRepository)
    {
        parent::__construct($courseRepository);
    }

    public function getWithFilters(array $data)
    {
        return $this->courseRepository->getWithFilters($data);
    }
}
