<?php

namespace App\Http\Requests;

use App\Models\TrainingProgram;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'resource_person_name' => ['nullable', 'string', 'max:200'],
            'topics' => ['nullable', 'array'],
            'topics.*.name' => ['required_with:topics', 'string', 'max:200'],
            'topics.*.duration' => ['nullable', 'string', 'max:100'],
            'chain_levels' => ['required', 'array', 'min:1'],
            'chain_levels.*' => [Rule::in(TrainingProgram::CHAIN_LEVELS)],
            'venue' => ['nullable', 'string', 'max:200'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in(TrainingProgram::STATUSES)],
            'is_posted' => ['nullable', 'boolean'],
        ];
    }
}
