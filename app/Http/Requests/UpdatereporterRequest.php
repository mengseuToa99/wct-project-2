<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReporterRequest extends FormRequest
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
                'username' => 'sometimes|string', // Allow username to be nullable and only present sometimes
                'profile_pic' => 'sometimes|file|mimes:png,jpg,jpeg',
                'email' => 'nullable|email',
 // Allow profile_pic to be nullable and only present sometimes
            ];
        }

}
