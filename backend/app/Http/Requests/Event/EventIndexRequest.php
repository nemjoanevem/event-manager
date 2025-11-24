<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public endpoint
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],

            'search' => ['sometimes', 'string', 'max:255'],

            'sort_by' => [
                'sometimes',
                'string',
                Rule::in(['starts_at', 'ends_at', 'title', 'price', 'capacity', 'created_at']),
            ],
            'sort_dir' => [
                'sometimes',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }

    /**
     * Normalized filters with defaults.
     */
    public function filters(): array
    {
        return [
            'page' => (int) ($this->input('page', 1)),
            'per_page' => (int) ($this->input('per_page', 15)),

            'search' => $this->input('search'),

            'sort_by' => $this->input('sort_by', 'starts_at'),
            'sort_dir' => $this->input('sort_dir', 'asc'),
        ];
    }
}
