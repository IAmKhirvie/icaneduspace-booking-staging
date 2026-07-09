<?php

namespace App\Http\Requests;

use App\Services\BookingService;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'time_block' => ['required', 'string', 'in:'.implode(',', array_keys(BookingService::TIME_BLOCKS))],
            'reschedule_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reschedule_note' => strip_tags((string) $this->input('reschedule_note')),
        ]);
    }
}
