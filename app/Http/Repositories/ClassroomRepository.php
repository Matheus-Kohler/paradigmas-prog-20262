<?php

namespace App\Http\Repositories;

use App\Models\Classroom;

class ClassroomRepository extends BaseRepository
{
    public function __construct(private Classroom $model)
    {
        parent::__construct($model);
    }

    public function getWithFilters(array $data)
    {
        $builder = $this->model->query()->with('course')->where(function ($query) use($data) {
            if (data_get($data, 'name')) {
                $query->where('name', 'like', '%' . $data['name'] . '%');
            }
            if (data_get($data, 'course_id')) {
                $query->where('course_id', $data['course_id']);
            }
        });

        return $this->index($builder);
    }

    public function show(string $id)
    {
        return $this->model->with(['course', 'users'])->findOrFail($id);
    }

    public function attachUser(Classroom $classroom, int $userId): void
    {
        $classroom->users()->syncWithoutDetaching([$userId]);
    }

    public function detachUser(Classroom $classroom, int $userId): void
    {
        $classroom->users()->detach($userId);
    }
}