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

    protected function indexRouteName(): string
    {
        $config = $this->moduleConfig();
        $routeName = $this->routePrefix();
        $indexRoute = $config['index_route'] ?? ($config['legacy_route'] ?? "{$routeName}.index");
        if (!\Route::has($indexRoute) && \Route::has($routeName)) {
            $indexRoute = $routeName;
        }
        return $indexRoute;
    }

    protected function moduleViewData(array $extra = []): array
    {
        $config = $this->moduleConfig();

        return array_merge([
            'module' => $config,
            'moduleSlug' => $this->moduleSlug(),
            'routePrefix' => $this->routePrefix(),
            'indexRoute' => $this->indexRouteName(),
            'viewPrefix' => $this->viewPrefix(),
            'label' => $config['label'] ?? ucfirst($this->moduleSlug()),
            'columns' => $config['columns'] ?? [],
            'formFields' => $config['form'] ?? [],
            'readonly' => (bool) ($config['readonly'] ?? false),
            'permission' => $config['permission'] ?? $this->moduleSlug(),
            'defaultSortColumnIndex' => $this->defaultSortColumnIndex($config),
            'defaultSortDirection' => $config['default_sort_dir'] ?? 'desc',
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
                ->route($this->indexRouteName())
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
                ->route($this->indexRouteName())
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
                ->route($this->indexRouteName())
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
            $orderCol = (int) $request->input('order.0.column', 2);
            $orderDir = $request->input('order.0.dir', 'desc');

            $dataColumns = array_column($this->moduleConfig()['columns'] ?? [], 'field');
            $sortIndex = $orderCol - 2;
            $sortBy = ($sortIndex >= 0 && isset($dataColumns[$sortIndex]))
                ? $dataColumns[$sortIndex]
                : 'created_at';

            $filters = array_filter([
                'search' => $search,
                'status' => $request->input('status'),
            ]);

            // Skip section filtering for global modules that are not section-scoped.
            $sectionScopedModules = ['users'];
            $config = $this->moduleConfig();
            $skipSection = in_array($this->moduleSlug(), $sectionScopedModules, true)
                || ($config['section_scoped'] ?? null) === false;

            if (! $skipSection) {
                if ($request->filled('section_id')) {
                    $filters['section_id'] = $request->input('section_id');
                }
                $secId = $request->input('sectionId') ?: $request->cookie('section_id');
                if ($secId) {
                    $filters['sectionId'] = $secId;
                    $filters['section_id'] = $secId;
                }
            }

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

        static $placeholderImage = null;
        if ($placeholderImage === null) {
            $placeholderImage = asset('images/default_user.png');
            $placeholderRaw = \DB::table('settings')->where('id', 'placeHolderImage')->value('value');
            if ($placeholderRaw) {
                $decoded = json_decode($placeholderRaw, true);
                if (!empty($decoded['image'])) {
                    $placeholderImage = $decoded['image'];
                }
            }
        }

        $canDelete = $this->userCanDeleteModule($config);

        $row[] = $canDelete
            ? '<input type="checkbox" class="row-select" data-id="' . e($id) . '">'
            : '';

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
                $row[] = filter_var($value, FILTER_VALIDATE_BOOLEAN)
                    ? '<span class="badge badge-success">Yes</span>'
                    : '<span class="badge badge-secondary">No</span>';
            } elseif (($column['type'] ?? null) === 'datetime') {
                if (! $value && $field === 'created_at') {
                    $value = data_get($record, 'createdAt');
                }
                $row[] = $value ? e((string) $value) : '';
            } elseif (
                in_array(strtolower($field), ['photo', 'image', 'coverimage', 'profilepictureurl', 'flag'], true) || 
                (is_string($value) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $value)) ||
                (is_string($value) && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) && (str_contains($value, 'firebasestorage') || str_contains($value, 'storage/')))
            ) {
                if ($value) {
                    $row[] = '<img class="rounded" style="width:50px; height:50px; object-fit:cover;" src="' . e($value) . '" alt="image" onerror="this.onerror=null;this.src=\'' . e($placeholderImage) . '\'">';
                } else {
                    $row[] = '<img class="rounded" style="width:50px; height:50px; object-fit:cover;" src="' . e($placeholderImage) . '" alt="placeholder">';
                }
            } else {
                $row[] = e((string) ($value ?? ''));
            }
        }

        return $row;
    }

    protected function defaultSortColumnIndex(array $config): int
    {
        $dataColumns = array_column($config['columns'] ?? [], 'field');
        $defaultSort = $config['default_sort'] ?? ($dataColumns[0] ?? 'created_at');
        $index = array_search($defaultSort, $dataColumns, true);

        return $index !== false ? $index + 2 : 2;
    }

    protected function userCanDeleteModule(array $config): bool
    {
        $user = auth()->user();
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];

        return in_array(($config['permission'] ?? '') . '.delete', $permissions, true);
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
