<?php

namespace App\Http\Requests\Api\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'title' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => [$required, 'numeric', 'min:0'],
            'disPrice' => ['nullable', 'numeric', 'min:0'],
            'priceUnit' => ['nullable', 'string', 'max:64'],
            'categoryId' => ['nullable', 'string', 'max:64'],
            'subCategoryId' => ['nullable', 'string', 'max:64'],
            'sectionId' => ['nullable', 'string', 'max:64'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'publish' => ['nullable', 'boolean'],
            'startTime' => ['nullable', 'string', 'max:32'],
            'endTime' => ['nullable', 'string', 'max:32'],
            'days' => ['nullable', 'array'],
            'photos' => ['nullable', 'array'],
            'phoneNumber' => ['nullable', 'string', 'max:32'],
        ];
    }
}
