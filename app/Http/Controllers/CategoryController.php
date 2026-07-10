<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use App\Http\Requests\Admin\StoreModuleRequest;
use App\Models\ReviewAttribute;
use App\Models\Section;
use App\Models\VendorCategory;
use App\Models\VendorProduct;
use App\Services\Storage\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CategoryController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'vendor-categories';
    }

    public function index(): View
    {
        return view('categories.index', $this->moduleViewData([
            'canDelete' => $this->userCanDeleteCategories(),
            'canEdit' => $this->userCanEdit(),
        ]));
    }

    public function create(): View
    {
        if ($this->moduleConfig()['readonly'] ?? false) {
            abort(403);
        }

        return view('categories.create', $this->moduleViewData([
            'reviewAttributes' => ReviewAttribute::query()->orderBy('title')->get(),
            'section' => $this->currentSection(),
            'showInHomeOption' => $this->shouldShowInHomeOption(),
        ]));
    }

    public function edit(...$params): View
    {
        if ($this->moduleConfig()['readonly'] ?? false) {
            abort(403);
        }

        $id = (string) end($params);
        $record = $this->crudService()->findOrFail($id);

        return view('categories.edit', $this->moduleViewData([
            'record' => $record,
            'id' => $id,
            'reviewAttributes' => ReviewAttribute::query()->orderBy('title')->get(),
            'section' => $this->currentSection($record->section_id ?? data_get($record, 'payload.section_id')),
            'showInHomeOption' => $this->shouldShowInHomeOption($record->section_id ?? data_get($record, 'payload.section_id')),
            'selectedReviewAttributes' => (array) (data_get($record, 'review_attributes') ?? data_get($record, 'payload.review_attributes') ?? []),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->categoryRules(true));
        $this->validateShowInHomepage($request);

        try {
            if ($request->hasFile('photo')) {
                $upload = app(FileStorageService::class)->upload($request->file('photo'), 'images');
                $validated['photo'] = asset(ltrim($upload['url'], '/'));
            }

            $validated['section_id'] = $validated['section_id'] ?? $request->cookie('section_id');
            $validated['review_attributes'] = array_values($request->input('review_attributes', []));
            $validated['show_in_homepage'] = $request->boolean('show_in_homepage');
            $validated['publish'] = $request->boolean('publish');
            $validated['order'] = (int) (VendorCategory::query()->count() + 1);

            $this->crudService()->store($validated);

            return redirect()
                ->route($this->indexRouteName())
                ->with('success', trans('lang.saved_successfully'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error(static::class . '@store', ['error' => $e->getMessage()]);

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, ...$params): RedirectResponse
    {
        $id = (string) end($params);
        $validated = $request->validate($this->categoryRules(false));
        $this->validateShowInHomepage($request, $id);

        try {
            $record = $this->crudService()->findOrFail($id);
            $existingPhoto = data_get($record, 'photo') ?? data_get($record, 'payload.photo');

            if (! $request->hasFile('photo') && ! $existingPhoto) {
                throw ValidationException::withMessages([
                    'photo' => trans('lang.upload_image_error'),
                ]);
            }

            if ($request->hasFile('photo')) {
                $upload = app(FileStorageService::class)->upload($request->file('photo'), 'images');
                $validated['photo'] = asset(ltrim($upload['url'], '/'));
            } elseif (! $request->filled('photo')) {
                $validated['photo'] = data_get($record, 'photo') ?? data_get($record, 'payload.photo');
            }

            $validated['section_id'] = $validated['section_id'] ?? $record->section_id ?? $request->cookie('section_id');
            $validated['review_attributes'] = array_values($request->input('review_attributes', []));
            $validated['show_in_homepage'] = $request->boolean('show_in_homepage');
            $validated['publish'] = $request->boolean('publish');
            $validated['order'] = data_get($record, 'order') ?? data_get($record, 'payload.order') ?? 0;

            $this->crudService()->update($id, $validated);

            return redirect()
                ->route($this->indexRouteName())
                ->with('success', trans('lang.update_success'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error(static::class . '@update', ['error' => $e->getMessage()]);

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function togglePublish(Request $request, string $id): JsonResponse
    {
        $request->validate(['publish' => ['required', 'boolean']]);

        try {
            $record = $this->crudService()->findOrFail($id);
            $record->publish = $request->boolean('publish');
            $record->save();

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error(static::class . '@togglePublish', ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = (string) $request->input('search.value', '');
            $orderCol = (int) $request->input('order.0.column', 1);
            $orderDir = $request->input('order.0.dir', 'asc');
            $canDelete = $this->userCanDeleteCategories();

            $orderableColumns = $canDelete
                ? ['', 'title', 'totalProducts', '', '']
                : ['title', 'totalProducts', '', ''];
            $sortBy = $orderableColumns[$orderCol] ?? 'title';
            if ($sortBy === '' || $sortBy === 'totalProducts') {
                $sortBy = $sortBy === 'totalProducts' ? 'totalProducts' : 'title';
            }

            $filters = array_filter(['search' => $search]);
            $secId = $request->input('sectionId') ?: $request->cookie('section_id');
            if ($secId) {
                $filters['section_id'] = $secId;
                $filters['sectionId'] = $secId;
            }

            $crudSortBy = $sortBy === 'totalProducts' ? 'title' : $sortBy;
            $result = $this->crudService()->datatable($filters, 0, PHP_INT_MAX, $crudSortBy, $orderDir);

            $productCounts = VendorProduct::query()
                ->select('categoryID', DB::raw('COUNT(*) as total'))
                ->groupBy('categoryID')
                ->pluck('total', 'categoryID');

            $items = $result['items']->map(function ($record) use ($productCounts) {
                $record->totalProducts = (int) ($productCounts[$record->id] ?? 0);

                return $record;
            });

            if ($sortBy === 'totalProducts') {
                $items = $orderDir === 'desc'
                    ? $items->sortByDesc('totalProducts')->values()
                    : $items->sortBy('totalProducts')->values();
            }

            if ($search !== '') {
                $needle = strtolower($search);
                $items = $items->filter(function ($record) use ($needle) {
                    $title = strtolower((string) ($record->title ?? ''));
                    $count = (string) ($record->totalProducts ?? 0);

                    return str_contains($title, $needle) || str_contains($count, $needle);
                })->values();
            }

            $total = $items->count();
            $pageItems = $items->slice($start, $length > 0 ? $length : 10);

            $rows = [];
            foreach ($pageItems as $record) {
                $rows[] = $this->buildCategoryDatatableRow($record, $canDelete);
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
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

    protected function buildCategoryDatatableRow($record, bool $canDelete): array
    {
        $id = $record->id;
        $row = [];
        $placeholder = $this->placeholderImage();
        $photo = data_get($record, 'photo') ?? data_get($record, 'payload.photo') ?? '';
        $title = e((string) ($record->title ?? ''));
        $editUrl = route('categories.edit', $id);
        $itemsUrl = url('items?categoryID=' . $id);
        $isPublished = filter_var(data_get($record, 'publish'), FILTER_VALIDATE_BOOLEAN);
        $totalProducts = (int) ($record->totalProducts ?? 0);

        if ($canDelete) {
            $row[] = '<input type="checkbox" class="is_open row-select" data-id="' . e($id) . '" id="is_open_' . e($id) . '">';
        }

        if ($photo) {
            $row[] = '<img class="rounded" style="width:50px;height:50px;object-fit:cover;" src="' . e($photo) . '" alt="image" onerror="this.onerror=null;this.src=\'' . e($placeholder) . '\'"> <a href="' . $editUrl . '" class="left_space">' . $title . '</a>';
        } else {
            $row[] = '<img class="rounded" style="width:50px;height:50px;object-fit:cover;" src="' . e($placeholder) . '" alt="image"> <a href="' . $editUrl . '" class="left_space">' . $title . '</a>';
        }

        $row[] = '<a href="' . $itemsUrl . '">' . $totalProducts . '</a>';

        $checked = $isPublished ? ' checked' : '';
        $row[] = '<label class="switch"><input type="checkbox"' . $checked . ' class="publish-toggle" data-id="' . e($id) . '" name="isSwitch"><span class="slider round"></span></label>';

        $actions = '<span class="action-btn">';
        $actions .= '<a href="' . $editUrl . '" data-toggle="tooltip" title="' . e(trans('lang.edit')) . '"><i class="mdi mdi-lead-pencil"></i></a>';
        if ($canDelete) {
            $actions .= '<a href="javascript:void(0)" class="delete-btn category-delete" data-id="' . e($id) . '" data-toggle="tooltip" title="' . e(trans('lang.delete')) . '"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';
        $row[] = $actions;

        return $row;
    }

    protected function categoryRules(bool $isCreate): array
    {
        $rules = StoreModuleRequest::buildRules($this->moduleSlug(), $isCreate);

        $rules['title'] = [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'];
        $rules['description'] = ['nullable', 'string'];
        $rules['photo'] = $isCreate
            ? ['required', 'image', 'max:10240']
            : ['nullable', 'image', 'max:10240'];
        $rules['publish'] = ['nullable', 'boolean'];
        $rules['show_in_homepage'] = ['nullable', 'boolean'];
        $rules['review_attributes'] = ['nullable', 'array'];
        $rules['review_attributes.*'] = ['string', 'max:64'];
        $rules['section_id'] = ['nullable', 'string', 'max:64'];

        return $rules;
    }

    protected function validateShowInHomepage(Request $request, ?string $exceptId = null): void
    {
        if (! $request->boolean('show_in_homepage')) {
            return;
        }

        $sectionId = $request->input('section_id') ?: $request->cookie('section_id');
        if (! $sectionId) {
            return;
        }

        $query = VendorCategory::query()->where(function ($q) use ($sectionId) {
            $q->where('section_id', $sectionId)
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.section_id')) = ?", [$sectionId]);
        })->where(function ($q) {
            $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.show_in_homepage')) IN ('true', '1', 1)")
                ->orWhere('payload->show_in_homepage', true);
        });

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->count() >= 5) {
            throw ValidationException::withMessages([
                'show_in_homepage' => trans('lang.max_category_alert'),
            ]);
        }
    }

    protected function placeholderImage(): string
    {
        $placeholderImage = asset('images/default_user.png');
        $placeholderRaw = DB::table('settings')->where('id', 'placeHolderImage')->value('value');
        if ($placeholderRaw) {
            $decoded = json_decode($placeholderRaw, true);
            if (! empty($decoded['image'])) {
                $placeholderImage = $decoded['image'];
            }
        }

        return $placeholderImage;
    }

    protected function currentSection(?string $sectionId = null): ?Section
    {
        $sectionId = $sectionId ?: request()->cookie('section_id');
        if (! $sectionId) {
            return null;
        }

        return Section::query()->find($sectionId);
    }

    protected function shouldShowInHomeOption(?string $sectionId = null): bool
    {
        $section = $this->currentSection($sectionId);
        if (! $section) {
            return false;
        }

        $flag = $section->serviceTypeFlag ?? data_get($section, 'payload.serviceTypeFlag') ?? $section->serviceType ?? null;

        return in_array($flag, ['ecommerce-service', 'delivery-service'], true);
    }

    protected function userCanEdit(): bool
    {
        $user = auth()->user();
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];

        return in_array('categories.edit', $permissions, true)
            || in_array('category.edit', $permissions, true);
    }

    protected function userCanDeleteCategories(): bool
    {
        $user = auth()->user();
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];

        return in_array('categories.delete', $permissions, true)
            || in_array('category.delete', $permissions, true);
    }
}
