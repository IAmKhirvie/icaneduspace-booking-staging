<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'status'             => $this->status,
            'status_label'       => $this->statusLabel(),
            'booking_date'       => optional($this->booking_date)->toDateString(),
            'starts_at'          => optional($this->starts_at)->toIso8601String(),
            'ends_at'            => optional($this->ends_at)->toIso8601String(),
            'purpose'            => $this->purpose,
            'format'             => $this->format,
            'equipment_requests' => $this->equipment_requests,
            'equipment_notes'    => $this->equipment_notes,
            'snack_beverage_requests' => $this->snack_beverage_requests,
            'snack_beverage_notes' => $this->snack_beverage_notes,
            'participant_count'  => $this->participant_count,
            'customer_name'      => $this->customer_name,
            'organization'       => $this->organization,
            'contact'            => $this->contact,
            'payment_method'     => $this->payment_method,
            'payment_method_label' => $this->paymentMethodLabel(),
            'payment_scope'      => $this->paymentScopeFormValue(),
            'payment_scope_label' => $this->paymentScopeLabel(),
            'customer_notes'     => $this->customer_notes,
            'estimated_price'    => $this->estimated_price,
            'special_discount_percent' => $this->special_discount_percent,
            'special_discount_amount' => $this->special_discount_amount,
            'reservation_fee_percent' => $this->reservation_fee_percent,
            'reservation_fee_amount' => $this->reservation_fee_amount,
            'reservation_fee_paid_at' => optional($this->reservation_fee_paid_at)->toIso8601String(),
            'reservation_fee_status' => $this->reservationFeeStatusLabel(),
            'reservation_fee_marked_paid_by' => $this->reservation_fee_marked_paid_by,
            'full_payment_paid_at' => optional($this->full_payment_paid_at)->toIso8601String(),
            'full_payment_status' => $this->fullPaymentStatusLabel(),
            'full_payment_marked_paid_by' => $this->full_payment_marked_paid_by,
            'classroom'          => new ClassroomResource($this->whenLoaded('classroom')),
            'service_package'    => new ServicePackageResource($this->whenLoaded('servicePackage')),
            'approved_at'        => optional($this->approved_at)->toIso8601String(),
            'rejected_at'        => optional($this->rejected_at)->toIso8601String(),
            'cancelled_at'       => optional($this->cancelled_at)->toIso8601String(),
            'cancelled_by'       => $this->cancelled_by,
            'cancellation_reason' => $this->cancellation_reason,
            'special_cases'      => $this->whenLoaded('specialCases', fn () => $this->specialCases->map(fn ($case) => [
                'type' => $case->type,
                'severity' => $case->severity,
                'message' => $case->message,
                'details' => $case->details,
                'resolved_at' => optional($case->resolved_at)->toIso8601String(),
            ])->values()),
            'created_at'         => optional($this->created_at)->toIso8601String(),
        ];
    }
}
