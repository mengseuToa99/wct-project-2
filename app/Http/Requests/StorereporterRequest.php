<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorereporterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role' => ['required', 'string', 'in:admin,user'],
        ];
    }
}
