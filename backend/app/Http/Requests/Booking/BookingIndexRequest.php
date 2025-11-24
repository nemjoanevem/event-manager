<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],

            'status' => ['sometimes', 'string', Rule::in(['active', 'cancelled'])],

            'sort_by' => [
                'sometimes',
                'string',
                Rule::in(['created_at', 'seats_booked', 'status']),
            ],
            'sort_dir' => [
                'sometimes',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }

    public function filters(): array
    {
        return [
            'page' => (int) ($this->input('page', 1)),
            'per_page' => (int) ($this->input('per_page', 15)),
            'status' => $this->input('status'),
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_dir' => $this->input('sort_dir', 'desc'),
        ];
    }
}
