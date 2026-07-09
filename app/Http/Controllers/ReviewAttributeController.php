<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ReviewAttributeController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'review-attributes';
    }

    public function index(): View
    {
        return view('reviewattributes.index', $this->moduleViewData([
            'canDelete' => $this->userCanDeleteReviewAttributes(),
        ]));
    }

    public function create(): View
    {
        if ($this->moduleConfig()['readonly'] ?? false) {
            abort(403);
        }

        return view('reviewattributes.create', $this->moduleViewData());
    }

    public function edit(...$params): View
    {
        if ($this->moduleConfig()['readonly'] ?? false) {
            abort(403);
        }

        $id = (string) end($params);
        $record = $this->crudService()->findOrFail($id);

        return view('reviewattributes.edit', $this->moduleViewData([
            'record' => $record,
            'id' => $id,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reviewAttributeRules());

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

    public function update(Request $request, ...$params): RedirectResponse
    {
        $id = (string) end($params);
        $validated = $request->validate($this->reviewAttributeRules());

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

    public function datatable(Request $request): JsonResponse
    {
        try {
            $draw = (int) $request->input('draw', 1);
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = (string) $request->input('search.value', '');
            $orderDir = $request->input('order.0.dir', 'asc');
            $canDelete = $this->userCanDeleteReviewAttributes();

            $result = $this->crudService()->datatable(
                array_filter(['search' => $search]),
                $start,
                $length,
                'title',
                $orderDir
            );

            $rows = [];
            foreach ($result['items'] as $record) {
                $rows[] = $this->buildReviewAttributeDatatableRow($record, $canDelete);
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

    protected function buildReviewAttributeDatatableRow($record, bool $canDelete): array
    {
        $id = $record->id;
        $title = e((string) ($record->title ?? ''));
        $editUrl = route('reviewattributes.edit', $id);

        $actions = '<span class="action-btn">';
        $actions .= '<a href="' . $editUrl . '" data-toggle="tooltip" title="' . e(trans('lang.edit')) . '"><i class="mdi mdi-lead-pencil"></i></a>';
        if ($canDelete) {
            $actions .= '<a href="javascript:void(0)" class="delete-btn reviewattribute-delete" data-id="' . e($id) . '" data-toggle="tooltip" title="' . e(trans('lang.delete')) . '"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</span>';

        return [$title, $actions];
    }

    protected function reviewAttributeRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    protected function userCanDeleteReviewAttributes(): bool
    {
        $user = auth()->user();
        if ($user && (int) $user->role_id === 1) {
            return true;
        }

        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];

        return in_array('review.attributes.delete', $permissions, true)
            || in_array('review-attribute.delete', $permissions, true);
    }
}
