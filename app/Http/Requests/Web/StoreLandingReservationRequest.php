<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreLandingReservationRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'prefill_token' => ['nullable', 'string'],
            'pickup_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'pickup_time' => ['nullable', 'date_format:H:i'],
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\d{11}$/'],
        ];
    }

    /**
     * Get the custom validation messages for the request.
     */
    public function messages(): array
    {
        return [
            'pickup_date.after_or_equal' => 'Pickup date cannot be in the past.',
            'phone.regex' => 'Phone number must contain exactly 11 digits.',
        ];
    }
}
