<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\UsuarioGestionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User $usuario */
        $usuario = $this->route('user');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($usuario->id),
            ],
            'rol' => ['sometimes', 'required', 'string', Rule::in(UsuarioGestionService::ROLES_PERMITIDOS)],
        ];
    }
}
