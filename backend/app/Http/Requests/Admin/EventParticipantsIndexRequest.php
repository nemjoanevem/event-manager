<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventParticipantsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy in controller
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],

            'search' => ['sometimes', 'string', 'max:255'],

            'sort_by' => [
                'sometimes',
                'string',
                Rule::in(['name', 'email', 'seats_booked', 'created_at']),
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
            'per_page' => (int) ($this->input('per_page', 25)),
            'search' => $this->input('search'),
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_dir' => $this->input('sort_dir', 'desc'),
        ];
    }
}
