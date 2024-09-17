<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise.
     */
    public function authorize(): bool
    {
        return auth()->user() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> The validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'nullable|in:admin,user'
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string> The custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
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
            'name.required' => 'The :attribute field is required.',
            'email.required' => 'The :attribute field is required.',
            'email.email' => 'The :attribute must be a valid email address.',
            'email.unique' => 'This :attribute is already taken.',
            'password.required' => 'The :attribute field is required.',
            'password.min' => 'The :attribute must be at least :min characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'role.required' => 'The :attribute field is required.',
            'role.in' => 'The :attribute must be one of the following values: admin, user.',
        ];
    }

    /**
     * Perform any actions after successful validation.
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
                'message' => 'Validation failed for user creation.',
                'errors' => $errors,
            ], 422)
        );
    }
}
