<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:90'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Insira um nome válido.',
            'email.required' => 'Insira um e-mail válido.',
            'email.unique' => 'O e-mail inserido já existe em nosso sistema.',
            'password.required' => 'Insira uma senha válida.',
            'password.min' => 'Insira pelo menos 8 caracteres na senha.'
        ];
    }
}
