<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\Admin\StoreModuleRequest;
use App\Http\Requests\Admin\UpdateModuleRequest;
use App\Services\Admin\AdminCrudService;
use App\Services\Admin\AdminModuleRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Production MySQL CRUD for admin modules.
 *
 * Controllers only need: use ProvidesMySqlCrud + moduleSlug() returning config key.
 */
trait ProvidesMySqlCrud
{
    abstract protected function moduleSlug(): string;

    protected function moduleConfig(): array
    {
        return app(AdminModuleRegistry::class)->get($this->moduleSlug());
    }

    protected function crudService(): AdminCrudService
    {
        $config = $this->moduleConfig();
        $modelClass = $config['model'];

        return new AdminCrudService(new $modelClass(), $config);
    }

    protected function viewPrefix(): string
    {
        return $this->moduleConfig()['view'];
    }

    protected function routePrefix(): string
    {
        return $this->moduleConfig()['route'];
    }

    protected function moduleViewData(array $extra = []): array
    {
        $config = $this->moduleConfig();

        return array_merge([
            'module' => $config,
            'moduleSlug' => $this->moduleSlug(),
            'routePrefix' => $this->routePrefix(),
            'viewPrefix' => $this->viewPrefix(),
            'label' => $config['label'] ?? ucfirst($this->moduleSlug()),
            'columns' => $config['columns'] ?? [],
            'formFields' => $config['form'] ?? [],
            'readonly' => (bool) ($config['readonly'] ?? false),
            'permission' => $config['permission'] ?? $this->moduleSlug(),
        ], $extra);
    }

    public function index(): View
    {
        return view($this->viewPrefix() . '.index', $this->moduleViewData());
    }

    public function create(): View
    {
        if ($this->moduleConfig()['readonly'] ?? false) {
            abort(403);
        }

        return view($this->viewPrefix() . '.create', $this->moduleViewData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(StoreModuleRequest::buildRules($this->moduleSlug(), true));

        try {
            $this->crudService()->store($validated);

            return redirect()
                ->route($this->routePrefix() . '.index')
                ->with('success', trans('lang.saved_successfully'));
        } catch (Throwable $e) {
            Log::error(static::class . '@store', ['error' => $e->getMessage()]);

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(...$params): View
    {
        $id = (string) end($params);
        $record = $this->crudService()->findOrFail($id);

        return view($this->viewPrefix() . '.show', $this->moduleViewData([
            'record' => $record,
        ]));
    }

    public function edit(...$params): View
    {
        if ($this->moduleConfig()['readonly'] ?? false) {
            abort(403);
        }

        $id = (string) end($params);
        $record = $this->crudService()->findOrFail($id);

        return view($this->viewPrefix() . '.edit', $this->moduleViewData([
            'record' => $record,
        ]));
    }

    public function update(Request $request, ...$params): RedirectResponse
    {
        $id = (string) end($params);
        $validated = $request->validate(UpdateModuleRequest::buildRules($this->moduleSlug(), false));

        try {
            $this->crudService()->update($id, $validated);

            return redirect()
                ->route($this->routePrefix() . '.index')
                ->with('success', trans('lang.update_success'));
        } catch (Throwable $e) {
            Log::error(static::class . '@update', ['error' => $e->getMessage()]);

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, ...$params): JsonResponse|RedirectResponse
    {
        try {
            if ($request->filled('ids')) {
                $this->crudService()->bulkDestroy($request->input('ids', []));
            } else {
                $id = $request->input('id') ?? (string) end($params);
                $this->crudService()->destroy((string) $id);
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route($this->routePrefix() . '.index')
                ->with('success', trans('lang.delete_success'));
        } catch (Throwable $e) {
            Log::error(static::class . '@destroy', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value', '');
            $orderCol = (int) $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');

            $columns = array_merge(['id'], array_column($this->moduleConfig()['columns'] ?? [], 'field'));
            $sortBy = $columns[$orderCol] ?? 'created_at';

            $filters = array_filter([
                'search' => $search,
                'section_id' => $request->input('section_id'),
                'sectionId' => $request->input('sectionId') ?: $request->cookie('section_id'),
                'status' => $request->input('status'),
            ]);

            $result = $this->crudService()->datatable($filters, $start, $length, $sortBy, $orderDir);

            $rows = [];
            foreach ($result['items'] as $record) {
                $rows[] = $this->buildDatatableRow($record);
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $result['total'],
                'recordsFiltered' => $result['total'],
                'data' => $rows,
            ]);
        } catch (Throwable $e) {
            Log::error(static::class . '@datatable', ['error' => $e->getMessage()]);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function buildDatatableRow($record): array
    {
        $config = $this->moduleConfig();
        $route = $this->routePrefix();
        $id = $record->id;
        $row = [];

        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];
        $canDelete = in_array(($config['permission'] ?? '') . '.delete', $permissions, true);

        if ($canDelete) {
            $row[] = '<input type="checkbox" class="row-select" data-id="' . e($id) . '">';
        }

        $actions = '<span class="action-btn">';
        $actions .= '<a href="' . route($route . '.show', $id) . '" title="View"><i class="mdi mdi-eye"></i></a>';
        if (! ($config['readonly'] ?? false)) {
            $actions .= '<a href="' . route($route . '.edit', $id) . '" title="Edit"><i class="mdi mdi-lead-pencil"></i></a>';
        }
        if ($canDelete) {
            $actions .= '<a href="javascript:void(0)" class="delete-row" data-id="' . e($id) . '" title="Delete"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';
        $row[] = $actions;

        foreach ($config['columns'] ?? [] as $column) {
            $field = $column['field'];
            $value = data_get($record, $field);

            if (($column['type'] ?? null) === 'boolean') {
                $row[] = $value ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>';
            } elseif (($column['type'] ?? null) === 'datetime' && $value) {
                $row[] = e((string) $value);
            } else {
                $row[] = e((string) ($value ?? ''));
            }
        }

        return $row;
    }

    /** Legacy route aliases used by existing web.php */
    public function brandCreate(): View
    {
        return $this->create();
    }

    public function brandEdit(string $id): View
    {
        return $this->edit($id);
    }
}
