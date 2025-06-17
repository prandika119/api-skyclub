<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => floor($this->input('amount')), // Pembulatan ke bawah
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'bank_ewallet' => 'required|string',
            'number' => 'required|string',
        ];
    }
}
