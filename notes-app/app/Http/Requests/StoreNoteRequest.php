<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNoteRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'title'   => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'tags'    => 'nullable|array|max:10',
            'tags.*'  => 'string|max:50',
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'title.required'  => 'A note title is required.',
            'title.max'       => 'Title cannot exceed 255 characters.',
            'content.required'=> 'Note content is required.',
            'content.min'     => 'Note content must be at least 10 characters.',
            'tags.max'        => 'A note can have a maximum of 10 tags.',
            'tags.*.max'      => 'Each tag cannot exceed 50 characters.',
        ];
    }

    /**
     * Return JSON error response on validation failure.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
