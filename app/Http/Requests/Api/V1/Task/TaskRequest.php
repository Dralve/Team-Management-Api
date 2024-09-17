<?php

namespace App\Http\Requests\Api\V1\Task;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:new,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date|after_or_equal:today',
            'project_id' => 'required|exists:projects,id',
            'notes' => 'nullable|string|max:1000'
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
            'title.required' => 'The :attribute is required.',
            'title.string' => 'The :attribute must be a string.',
            'title.max' => 'The :attribute may not be greater than :max characters.',
            'description.string' => 'The :attribute must be a string.',
            'description.max' => 'The :attribute may not be greater than 1000 characters.',
            'status.required' => 'The :attribute is required.',
            'status.in' => 'The :attribute must be one of the following: new, in_progress, completed.',
            'priority.required' => 'The :attribute is required.',
            'priority.in' => 'The :attribute must be one of the following: low, medium, high.',
            'due_date.date' => 'The :attribute must be a valid date.',
            'due_date.after_or_equal' => 'The :attribute must be today or a future date.',
            'project_id.required' => 'The :attribute is required.',
            'project_id.exists' => 'The selected project does not exist.',
            'notes.string' => 'The :attribute must be a string.',
            'notes.max' => 'The :attribute may not be greater than :max characters.',
        ];
    }

    /**
     * Get custom attribute names for validation rules.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'task title',
            'description' => 'task description',
            'status' => 'task status',
            'priority' => 'task priority',
            'due_date' => 'due date',
            'project_id' => 'project ID',
            'notes' => 'notes',
        ];
    }

    /**
     * Handle actions after validation passes.
     *
     * @return void
     */
    public function passedValidation(): void
    {
        Log::info('Validation passed for task creation');
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
