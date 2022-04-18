<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class UserCreateRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    #[ArrayShape( [ 'name' => "string", 'email' => "string", 'role' => "string" ] )] public function rules(): array {
        return [
            'name'  => 'required',
            'email' => 'required|email|unique:users,email',
            'role'  => 'required|exists:roles,id',
        ];
    }
}
