<?php

namespace App\Http\Repositories;

use App\Models\Course;

class CourseRepository extends BaseRepository
{
    public function __construct(private Course $model)
    {
        parent::__construct($model);
    }

    public function getWithFilters(array $data)
    {
        $builder = $this->model->query()->where(function ($query) use ($data) {
            if (data_get($data, 'name')) {
                $query->where('name', 'like', '%' . $data['name'] . '%');
            }
        });

        return $this->index($builder);
    }
}
