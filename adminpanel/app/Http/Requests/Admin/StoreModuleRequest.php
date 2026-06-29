<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\AdminModuleRegistry;
use Illuminate\Foundation\Http\FormRequest;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [];
    }

    public static function buildRules(string $slug, bool $isCreate): array
    {
        $config = app(AdminModuleRegistry::class)->get($slug);
        $rules = [];

        foreach ($config['form'] ?? [] as $field) {
            $name = $field['name'];
            $fieldRules = [];

            if (($field['required'] ?? false) && $isCreate) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules[] = match ($field['type'] ?? 'text') {
                'email' => 'email',
                'number' => 'numeric',
                'checkbox' => 'boolean',
                'json' => 'json',
                'password' => 'string|min:6',
                default => 'string',
            };

            $rules[$name] = $fieldRules;
        }

        if ($isCreate && ($config['scope'] ?? null) === 'customers') {
            $rules['email'][] = 'unique:app_users,email';
        }

        return $rules;
    }
}
