<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class DynamicNotificationController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "dynamic-notifications";
    }

    protected function buildDatatableRow($record): array
    {
        $config = $this->moduleConfig();
        $row = [];

        foreach ($config['columns'] ?? [] as $column) {
            $field = $column['field'];
            $value = data_get($record, $field);
            
            if ($field === 'service_type') {
                $row[] = '<div style="display: flex; align-items: center; gap: 8px;"><i class="mdi mdi-plus-circle" style="color: #22c55e; font-size: 18px;"></i> ' . e((string) ($value ?? '')) . '</div>';
            } else {
                $row[] = e((string) ($value ?? ''));
            }
        }

        return $row;
    }
}
