<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EntityIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string', 'max:64'],
            'sort_dir' => ['sometimes', 'in:asc,desc'],
            'search' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 15);
    }

    public function sortBy(): string
    {
        return (string) $this->input('sort_by', 'created_at');
    }

    public function sortDir(): string
    {
        return (string) $this->input('sort_dir', 'desc');
    }

    public function filters(): array
    {
        return $this->except(['per_page', 'sort_by', 'sort_dir', 'page']);
    }
}
