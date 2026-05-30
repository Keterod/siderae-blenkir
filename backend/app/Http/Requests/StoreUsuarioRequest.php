<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\UsuarioGestionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'rol' => ['required', 'string', Rule::in(UsuarioGestionService::ROLES_PERMITIDOS)],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
