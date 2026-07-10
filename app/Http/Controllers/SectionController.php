<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SectionController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'sections';
    }

    public function index(): View
    {
        $count = DB::table('sections')->count();
        return view($this->viewPrefix() . '.index', $this->moduleViewData([
            'sectionsCount' => $count
        ]));
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value', '');
            $orderCol = (int) $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'asc');

            // Map DataTables column index to SQL field
            $columnsMap = [
                0 => 'name',
                1 => 'serviceType',
                2 => 'isActive',
            ];
            
            $sortBy = $columnsMap[$orderCol] ?? 'name';

            $filters = array_filter([
                'search' => $search,
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(static::class . '@datatable', ['error' => $e->getMessage()]);

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
        $id = $record->id;

        // 1. Section Info: image + name link
        $photo = $record->sectionImage ?: $record->photo;
        if (empty($photo)) {
            static $placeholderImage = null;
            if ($placeholderImage === null) {
                $placeholderRaw = DB::table('settings')->where('id', 'placeHolderImage')->value('value');
                if ($placeholderRaw) {
                    $decoded = json_decode($placeholderRaw, true);
                    $placeholderImage = $decoded['image'] ?? '';
                }
                if (empty($placeholderImage)) {
                    $placeholderImage = asset('images/default_user.png');
                }
            }
            $photo = $placeholderImage;
        }

        $editUrl = route('sections.edit', $id);
        $sectionInfoHtml = '<div class="section-info-container">' .
            '<img class="section-img" src="' . e($photo) . '" alt="image" onerror="this.onerror=null;this.src=\'' . asset('images/default_user.png') . '\'">' .
            '<a href="' . $editUrl . '" class="section-name-link">' . e($record->name) . '</a>' .
            '</div>';

        // 2. Service Type
        $serviceTypeHtml = e($record->serviceType);

        // 3. Status Toggle
        $isChecked = filter_var($record->isActive, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '';
        $statusHtml = '<label class="switch">' .
            '<input type="checkbox" class="status-toggle" data-id="' . e($id) . '" ' . $isChecked . '>' .
            '<span class="slider round"></span>' .
            '</label>';

        // 4. Actions: light blue/cyan circular edit button
        $actionsHtml = '<div class="text-center">' .
            '<a href="' . $editUrl . '" class="btn-circle-edit" data-toggle="tooltip" title="' . trans('lang.edit') . '">' .
            '<i class="mdi mdi-lead-pencil"></i>' .
            '</a>' .
            '</div>';

        return [
            $sectionInfoHtml,
            $serviceTypeHtml,
            $statusHtml,
            $actionsHtml
        ];
    }

    public function create(): View
    {
        if ($this->moduleConfig()['readonly'] ?? false) {
            abort(403);
        }

        return view('section.create', $this->legacySectionViewData());
    }

    public function edit(...$params): View
    {
        if ($this->moduleConfig()['readonly'] ?? false) {
            abort(403);
        }

        $id = (string) end($params);

        return view('section.edit', $this->legacySectionViewData([
            'id' => $id,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->storeSection($request);
    }

    public function update(Request $request, ...$params): RedirectResponse
    {
        return $this->updateSection($request, ...$params);
    }

    protected function storeSection(Request $request): RedirectResponse
    {
        $validated = $request->validate(\App\Http\Requests\Admin\StoreModuleRequest::buildRules($this->moduleSlug(), true));
        $validated = $this->applyServiceTypeFlag($validated);

        try {
            $this->crudService()->store($validated);

            return redirect()
                ->route($this->routePrefix() . '.index')
                ->with('success', trans('lang.saved_successfully'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(static::class . '@store', ['error' => $e->getMessage()]);

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function updateSection(Request $request, ...$params): RedirectResponse
    {
        $id = (string) end($params);
        $validated = $request->validate(\App\Http\Requests\Admin\UpdateModuleRequest::buildRules($this->moduleSlug(), false));
        $validated = $this->applyServiceTypeFlag($validated);

        try {
            $this->crudService()->update($id, $validated);

            return redirect()
                ->route($this->routePrefix() . '.index')
                ->with('success', trans('lang.update_success'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(static::class . '@update', ['error' => $e->getMessage()]);

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function legacySectionViewData(array $extra = []): array
    {
        return array_merge([
            'indexRoute' => $this->routePrefix() . '.index',
        ], $extra);
    }

    protected function serviceTypeOptions(): array
    {
        return DB::table('services')
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
    }

    protected function applyServiceTypeFlag(array $data): array
    {
        if (empty($data['serviceType'])) {
            return $data;
        }

        $flag = Service::query()
            ->where('name', $data['serviceType'])
            ->value('flag');

        if ($flag) {
            $data['serviceTypeFlag'] = $flag;
        }

        return $data;
    }
}
