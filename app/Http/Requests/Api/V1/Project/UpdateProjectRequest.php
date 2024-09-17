<?php

namespace App\Http\Requests\Api\V1\Project;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'users' => 'sometimes|nullable|array',
            'users.*.user_id' => 'required|exists:users,id',
            'users.*.role' => [
                'nullable',
                Rule::in(['manager', 'developer', 'tester']),
            ],
            'users.*.contribution_hours' => 'nullable|integer|min:0',
            'users.*.last_activity' => 'nullable|date',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The project name is required.',
            'name.string' => 'The project name must be a string.',
            'name.max' => 'The project name may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'users.array' => 'The users field must be an array.',
            'users.*.user_id.required' => 'The user ID is required for each user.',
            'users.*.user_id.exists' => 'The user ID must exist in the users table.',
            'users.*.role.required' => 'The role is required for each user.',
            'users.*.role.in' => 'The role must be one of the following: manager, developer, tester.',
            'users.*.contribution_hours.integer' => 'The contribution hours must be an integer.',
            'users.*.contribution_hours.min' => 'The contribution hours must be at least 0.',
            'users.*.last_activity.date' => 'The last activity must be a valid date.',
        ];
    }

    /**
     * Handle actions after validation passes.
     *
     * @return void
     */
    public function passedValidation(): void
    {
        Log::info('Validation passed for project update');
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
