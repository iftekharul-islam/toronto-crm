<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class UserUpdateRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    #[ArrayShape( [ 'role' => "string" ] )] public function rules(): array {
        return [
            'role' => 'required|exists:roles,id',
        ];
    }
}
