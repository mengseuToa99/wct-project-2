<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorereportRequest extends FormRequest
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
            'reporter_id' => ['required', 'integer'],
            // 'location_id' => ['required', 'integer'],
            // 'report_detail_id' => ['required', 'integer'],
            // 'category_id' => ['required', 'integer'],
            'status' => ['required', Rule::in(['nostatus', 'pending', 'deny', 'complete'])],
            'title' => ['required'],
            'building' => ['required'],
            'floor' => ['required', 'integer'],
            'description' => ['required'],
            'anonymous' => ['required', 'boolean'],
            'feedback' => ['string'],
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg'],
            'room' => ['required', 'integer'],
            'category' => ['required', 'string'],
            'type' => ['required', 'string']
        ];
    }
}
