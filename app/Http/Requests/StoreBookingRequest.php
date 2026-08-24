<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // First/last name + email are only required the first time a phone
        // number is seen — a recognized customer is just greeted by name.
        $isNewCustomer = is_string($this->input('phone'))
            && Customer::findByPhone($this->input('phone')) === null;

        return [
            'service_type' => ['required', 'in:one_way,hourly'],

            'pickup_date' => ['required', 'date'],
            'pickup_time' => ['required', 'date_format:H:i'],
            'pickup_type' => ['required', 'in:location,airport'],
            'pickup_location' => ['required', 'string', 'max:255'],
            'pickup_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['nullable', 'numeric', 'between:-180,180'],

            'stops' => ['sometimes', 'array'],
            'stops.*.location' => ['required', 'string', 'max:255'],
            'stops.*.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'stops.*.lng' => ['nullable', 'numeric', 'between:-180,180'],

            'dropoff_type' => ['required', 'in:location,airport'],
            'dropoff_location' => ['required', 'string', 'max:255'],
            'dropoff_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['nullable', 'numeric', 'between:-180,180'],

            'passengers' => ['required', 'integer', 'min:1', 'max:20'],

            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
            'first_name' => [Rule::requiredIf($isNewCustomer), 'nullable', 'string', 'max:255'],
            'last_name' => [Rule::requiredIf($isNewCustomer), 'nullable', 'string', 'max:255'],
            'email' => [Rule::requiredIf($isNewCustomer), 'nullable', 'email', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone') && is_string($this->input('phone'))) {
            $this->merge(['phone' => Customer::normalizePhone($this->input('phone'))]);
        }
    }
}
