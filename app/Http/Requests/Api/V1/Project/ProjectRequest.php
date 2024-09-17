<?php

namespace App\Http\Requests\Api\V1\Project;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'users' => 'nullable|array',
            'users.*.user_id' => 'required|exists:users,id',
            'users.*.role' => [
                'required',
                Rule::in(['manager', 'developer', 'tester']),
            ],
            'users.*.contribution_hours' => 'nullable|integer|min:0',
            'users.*.last_activity' => 'nullable|date',
        ];
    }

    /**
     * Custom attribute names for validation rules.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Project Name',
            'description' => 'Project Description',
            'users' => 'Users',
            'users.*.user_id' => 'User ID',
            'users.*.role' => 'User Role',
            'users.*.contribution_hours' => 'Contribution Hours',
            'users.*.last_activity' => 'Last Activity Date',
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The :attribute field is required.',
            'name.string' => 'The :attribute must be a string.',
            'name.max' => 'The :attribute may not be greater than :max characters.',
            'description.string' => 'The :attribute must be a string.',
            'description.max' => 'The :attribute may not be greater than :max characters.',
            'users.array' => 'The :attribute must be an array.',
            'users.*.user_id.required' => 'The :attribute field is required.',
            'users.*.user_id.exists' => 'The selected :attribute does not exist.',
            'users.*.role.required' => 'The :attribute field is required.',
            'users.*.role.in' => 'The selected :attribute is invalid.',
            'users.*.contribution_hours.integer' => 'The :attribute must be an integer.',
            'users.*.contribution_hours.min' => 'The :attribute must be at least :min.',
            'users.*.last_activity.date' => 'The :attribute is not a valid date.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'failed',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * Handle actions after validation passes.
     *
     * @return void
     */
    public function passedValidation(): void
    {
        Log::info('Validation passed for project creation');
    }

}
