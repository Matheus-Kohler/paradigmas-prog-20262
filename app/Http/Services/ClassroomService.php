<?php

namespace App\Http\Services;

use App\Http\Repositories\ClassroomRepository;

class ClassroomService extends BaseService
{
    public function __construct(private ClassroomRepository $classroomRepository)
    {
        parent::__construct($classroomRepository);
    }

    public function getWithFilters(array $data)
    {
        return $this->classroomRepository->getWithFilters($data);
    }

    public function manageEnrollment(string $classroomId, array $data)
    {
        $classroom = $this->classroomRepository->show($classroomId);
        $userId = (int) $data['user_id'];
        $action = strtolower($data['action'] ?? 'attach');

        if (in_array($action, ['detach', 'remove', 'unenroll'])) {
            $this->classroomRepository->detachUser($classroom, $userId);
            $message = 'Usuário desvinculado da turma com sucesso.';
        } else {
            $this->classroomRepository->attachUser($classroom, $userId);
            $message = 'Usuário vinculado à turma com sucesso.';
        }

        return [
            'message' => $message,
            'classroom' => $classroom->fresh(['users', 'course']),
        ];
    }
}
