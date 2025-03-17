<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'no_telp' => ['required', 'string', 'max:13'],
            'team' => ['string', 'max:255'],
            'address' => ['string', 'max:255'],
            'date_of_birth' => ['date'],
            'profile_photo' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']
        ];
    }
}
