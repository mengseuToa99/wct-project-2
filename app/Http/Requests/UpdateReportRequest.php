<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportRequest extends FormRequest
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
        $method = $this->method();

        if ($method == 'PUT') {
            return [
                'reporter_id' => ['required', 'integer'],
                'status' => ['required', Rule::in(['pending', 'complete', 'deny', 'nostatus'])],
                'approved' => ['required', 'boolean'],
                'title' => ['required'], 
                'feedback' =>['string'],
                'building' => ['required'],
                'floor' => ['required', 'integer'],
                'anonymous' => ['required', 'boolean'],
                'image' => ['required', 'url'],
                'like' => ['required', 'integer'],
                'deny' => ['required', 'boolean'],
            ];
        } else {
            return [

                'status' => ['sometimes', 'required', Rule::in(['pending', 'complete', 'deny', 'nostatus'])],
                'feedback' =>['string']
            ];
        }
    }
}
