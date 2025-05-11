<?php

namespace App\Http\Requests\Api\V1\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class AssignTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only task creator can assign tasks
        return $this->user()->id === $this->route('task')->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
        ];
    }

    /**
     * Configure additional validation logic after initial validation rules pass.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $task = $this->route('task');

            if ($task->assignees()->where('user_id', $this->input('user_id'))->exists()) {
                $validator->errors()->add('user_id', 'Task is already assigned to this user');
            }
        });
    }
}
