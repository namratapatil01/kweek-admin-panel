<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class NotificationController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "notifications";
    }

    protected function buildDatatableRow($record): array
    {
        $config = $this->moduleConfig();
        $id = $record->id;
        $row = [];

        $canDelete = $this->userCanDeleteModule($config);

        $row[] = $canDelete
            ? '<div style="display: flex; align-items: center; justify-content: center;"><input type="checkbox" class="row-select" data-id="' . e($id) . '" style="opacity: 1 !important; visibility: visible !important; width: 18px !important; height: 18px !important; appearance: auto !important; display: inline-block !important; position: static !important; z-index: 9999;"></div>'
            : '';

        foreach ($config['columns'] ?? [] as $column) {
            $field = $column['field'];
            $value = data_get($record, $field);
            
            if ($field === 'created_at' || $field === 'createdAt') {
                $row[] = $value ? \Carbon\Carbon::parse($value)->format('D M d Y g:i:s A') : '';
            } else {
                $row[] = e((string) ($value ?? ''));
            }
        }

        $actions = '<span class="action-btn">';
        if ($canDelete) {
            $actions .= '<a href="javascript:void(0)" class="delete-row" data-id="' . e($id) . '" title="Delete"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';

        $row[] = $actions;

        return $row;
    }
}
