<?php

namespace App\Http\Requests;

use App\Models\Location;
use App\Models\TraineeProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TraineeProfileStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'sex' => ['required', Rule::in(['Male', 'Female'])],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'city_municipality' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'lgu_income_class' => ['nullable', Rule::in(Location::INCOME_CLASSES)],
            'educational_level' => ['nullable', Rule::in(TraineeProfile::EDUCATIONAL_LEVELS)],
            'organization_affiliation' => ['nullable', 'string', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'create_login' => ['boolean'],
            'username' => ['required_if:create_login,true', 'nullable', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required_if:create_login,true', 'nullable', 'string', 'min:8'],
        ];
    }
}
