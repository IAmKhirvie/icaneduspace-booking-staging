<?php

namespace App\Http\Requests;

use App\Services\BookingService;
use App\Services\TurnstileVerifier;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Honeypot: must be empty. Bots fill every field; humans never see this.
            'website'             => ['nullable', 'size:0'],

            'classroom_id'        => ['nullable', 'integer', 'exists:classrooms,id'],
            'service_package_id'  => ['nullable', 'integer', 'exists:service_packages,id'],
            'purpose'             => ['required', 'string', 'max:160'],
            'booking_date'        => ['required', 'date', 'after_or_equal:today'],
            'booking_end_date'    => ['nullable', 'date', 'after_or_equal:booking_date'],
            'time_block'          => ['required', 'string', 'in:'.implode(',', array_keys(BookingService::TIME_BLOCKS))],
            'participant_count'   => ['nullable', 'integer', 'min:1', 'max:500'],
            'format'              => ['required', 'string', 'max:80'],
            'equipment_requests'  => ['nullable', 'array'],
            'equipment_requests.*'=> ['string', Rule::in(array_keys(Booking::EQUIPMENT_OPTIONS))],
            'equipment_notes'     => ['nullable', 'string', 'max:1000'],
            'snack_beverage_requests' => ['nullable', 'array'],
            'snack_beverage_requests.*' => ['string', Rule::in(array_keys(Booking::SNACK_BEVERAGE_OPTIONS))],
            'snack_beverage_notes' => ['nullable', 'string', 'max:1000'],
            'customer_name'       => ['required', 'string', 'max:120'],
            'organization'        => ['nullable', 'string', 'max:160'],
            'contact'             => ['required', 'string', 'max:160'],
            'payment_method'      => ['required', 'string', Rule::in(array_keys(Booking::PAYMENT_METHOD_OPTIONS))],
            'customer_notes'      => ['nullable', 'string', 'max:2000'],
            'confirm_additional_booking' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            try {
                app(BookingService::class)->dailyDatesForRange(
                    (string) $this->input('booking_date'),
                    $this->input('booking_end_date'),
                );

                app(TurnstileVerifier::class)->validate($this);
            } catch (ValidationException $exception) {
                foreach ($exception->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add($field, $message);
                    }
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // Strip stray HTML from free-text fields to reduce stored-XSS surface,
        // even though Blade auto-escapes on output.
        $this->merge([
            'customer_name'  => strip_tags((string) $this->input('customer_name')),
            'organization'   => strip_tags((string) $this->input('organization')),
            'contact'        => strip_tags((string) $this->input('contact')),
            'payment_method' => strip_tags((string) $this->input('payment_method')),
            'purpose'        => strip_tags((string) $this->input('purpose')),
            'booking_end_date' => $this->input('booking_end_date') ?: null,
            'equipment_notes' => strip_tags((string) $this->input('equipment_notes')),
            'snack_beverage_notes' => strip_tags((string) $this->input('snack_beverage_notes')),
            'customer_notes' => strip_tags((string) $this->input('customer_notes')),
        ]);
    }

    public function messages(): array
    {
        return [
            'website.size'                => 'Spam detected.',
            'booking_date.after_or_equal' => 'Please choose a date that is today or later.',
            'booking_end_date.after_or_equal' => 'Please choose an end date that is the same as or after the start date.',
            'time_block.in'               => 'Please choose a valid time block.',
        ];
    }
}
