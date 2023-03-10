<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class AuthLoginRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    #[ArrayShape( [ 'email' => "string", 'password' => "string" ] )] public function rules(): array {
        return [
            'email'    => 'required | string | email',
            'password' => 'required | string',
        ];
    }
}
