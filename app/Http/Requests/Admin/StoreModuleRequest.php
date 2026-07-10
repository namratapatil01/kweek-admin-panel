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

            $typeRules = match ($field['type'] ?? 'text') {
                'email' => ['string', 'email'],
                'number' => ['numeric'],
                'checkbox' => ['nullable', 'in:0,1,true,false,on,off'],
                'json' => ['json'],
                'password' => ['string', 'min:6'],
                'image' => ['file', 'image', 'max:10240'],
                'file' => ['file', 'max:10240'],
                default => ['string'],
            };

            $fieldRules = array_merge($fieldRules, $typeRules);

            // Image/file fields may be omitted on update when keeping the existing value.
            if (in_array($field['type'] ?? '', ['image', 'file'], true) && ! $isCreate) {
                $fieldRules = array_values(array_filter(
                    $fieldRules,
                    static fn ($rule) => $rule !== 'required'
                ));
                if (! in_array('nullable', $fieldRules, true)) {
                    array_unshift($fieldRules, 'nullable');
                }
            }

            $rules[$name] = $fieldRules;
        }

        if ($isCreate && ($config['scope'] ?? null) === 'customers') {
            $rules['email'][] = 'unique:app_users,email';
        }

        return $rules;
    }
}
