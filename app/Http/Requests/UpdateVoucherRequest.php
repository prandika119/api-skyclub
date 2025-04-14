<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVoucherRequest extends FormRequest
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
            'expire_date' => 'required|date',
            'code' => 'required|string|max:255|unique:vouchers,code,' . $this->route('voucher'), // Validasi kode unik
            'quota' => 'required|integer',
            'discount_price' => 'nullable|integer', // Tipe data integer
            'discount_percentage' => 'nullable|integer', // Tipe data integer
            'max_discount' => 'nullable|integer', // Tipe data integer
            'min_price' => 'required|integer', // Tipe data integer
        ];
    }
}
