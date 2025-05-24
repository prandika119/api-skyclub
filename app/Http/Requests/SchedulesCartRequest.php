<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchedulesCartRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'schedules' => ['required', 'array'],
            'schedules.*.field_id' => ['required'],
            'schedules.*.schedule_date' => ['required', 'date'],
            'schedules.*.schedule_time' => ['required', 'string'],
            'schedules.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
