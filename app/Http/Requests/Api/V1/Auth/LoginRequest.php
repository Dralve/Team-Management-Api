<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class LoginRequest extends FormRequest
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
            'email' => 'required|string',
            'password' => 'required|min:8',
        ];
    }

    /**
     * Customize the field names for validation messages.
     *
     * @return array<string, string>
     * @return array that maps validation attributes to human-readable names
     */
    public function attributes(): array
    {
        return [
            'email' => 'Email Address',
            'password' => 'Password',
        ];
    }

    /**
     * Customize the validation messages.
     *
     * @return array<string, string>
     * @return array of custom error messages for validation rules
     */
    public function messages(): array
    {
        return [
            'email.required' => 'The :attribute field is required',
            'email.email' => 'The :attribute field must be a valid email address',
            'email.max' => 'The :attribute may not be greater than :max characters.',
            'password.required' => 'The :attribute field is required.',
            'password.min' => 'The :attribute must be at least :min characters long.',
        ];
    }


    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     * @return void
     * @return void Logs and throws a response for failed validation
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::error('Login Validation Failed:', ['errors' => $validator->errors()->all()]);

        throw new HttpResponseException(
            response()->json([
                'status' => 'failed',
                'message' => 'Validation errors occurred.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Perform any actions after successful validation.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        Log::info('Registration Validation Successful');
    }
}
