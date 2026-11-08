<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'action' => 'required|string|in:attach,detach,add,remove,enroll,unenroll',
            'classroom_id' => 'sometimes|integer|exists:classrooms,id',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'O campo ID do usuário é obrigatório.',
            'user_id.integer' => 'O campo ID do usuário deve ser um número inteiro.',
            'user_id.exists' => 'O usuário informado não existe.',
            'action.required' => 'O campo ação é obrigatório.',
            'action.string' => 'O campo ação deve ser um texto.',
            'action.in' => 'A ação deve ser: attach, detach, add, remove, enroll ou unenroll.',
            'classroom_id.integer' => 'O campo ID da turma deve ser um número inteiro.',
            'classroom_id.exists' => 'A turma informada não existe.',
        ];
    }
}
