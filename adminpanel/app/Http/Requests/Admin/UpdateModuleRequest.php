<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [];
    }

    public static function buildRules(string $slug, bool $isCreate = false): array
    {
        return StoreModuleRequest::buildRules($slug, false);
    }
}
