<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> The validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->user->id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string|in:admin,user',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string> The custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'name' => 'User Name',
            'email' => 'Email Address',
            'password' => 'Password',
            'role' => 'User Role',
        ];
    }

    /**
     * Get custom validation error messages.
     *
     * @return array<string, string> The custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'name.sometimes' => 'The :attribute field is optional but if provided, it must be a string and no longer than 255 characters.',
            'name.string' => 'The :attribute must be a string.',
            'name.max' => 'The :attribute may not be greater than :max characters.',
            'email.sometimes' => 'The :attribute field is optional but if provided, it must be a valid email address.',
            'email.email' => 'The :attribute must be a valid email address.',
            'email.unique' => 'This :attribute is already taken.',
            'password.sometimes' => 'The :attribute field is optional but if provided, it must be at least :min characters long.',
            'password.string' => 'The :attribute must be a string.',
            'password.min' => 'The :attribute must be at least :min characters long.',
            'role.sometimes' => 'The :attribute field is optional but if provided, it must be one of the following values: admin, user.',
            'role.string' => 'The :attribute must be a string.',
            'role.in' => 'The :attribute must be one of the following values: admin, user.',
        ];
    }

    /**
     * Handle a successful validation.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        Log::info('User Request Validation Successful');
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator The validator instance.
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->all();

        throw new HttpResponseException(
            response()->json([
                'status' => 'validation_error',
                'message' => 'Validation failed for user update.',
                'errors' => $errors,
            ], 422)
        );
    }
}
