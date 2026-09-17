<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrainingHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'training_program_id' => ['required', 'exists:training_programs,id'],
            'date_attended' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
