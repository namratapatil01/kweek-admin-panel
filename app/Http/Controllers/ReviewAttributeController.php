<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReviewAttributeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** List page */
    public function index(): View
    {
        return view('reviewattributes.index');
    }

    /** DataTables JSON endpoint */
    public function datatable(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim($request->input('search.value', ''));

        $query = DB::table('review_attributes');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();

        $records = $query->orderBy('title', 'asc')
                         ->skip($start)
                         ->take($length > 0 ? $length : 10)
                         ->get();

        $canDelete = $this->userCanDelete();

        $rows = [];
        foreach ($records as $row) {
            $id    = $row->id;
            $title = $row->title ?? $row->name ?? '-';

            $isActive = (bool) ($row->isActive ?? $row->is_active ?? true);
            $toggleHtml = '<label class="ra-toggle-switch">
                <input type="checkbox" class="ra-toggle-enabled" data-id="' . e($id) . '"' . ($isActive ? ' checked' : '') . '>
                <span class="ra-slider"></span>
            </label>';

            $editUrl = route('reviewattributes.edit', $id);
            $actions = '<span class="action-btn-circle-container">';
            $actions .= '<a href="' . $editUrl . '" class="btn-circle btn-circle-edit" title="Edit"><i class="mdi mdi-lead-pencil"></i></a>';
            if ($canDelete) {
                $actions .= '<a href="javascript:void(0)" class="btn-circle btn-circle-delete ra-delete-btn" data-id="' . e($id) . '" title="Delete"><i class="mdi mdi-delete"></i></a>';
            }
            $actions .= '</span>';

            $checkbox = $canDelete
                ? '<input type="checkbox" class="ra-checkbox" data-id="' . e($id) . '">'
                : '';

            $rows[] = [
                $checkbox,
                e($title),
                $actions,
            ];
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $rows,
        ]);
    }

    /** Show create form */
    public function create(): View
    {
        return view('reviewattributes.create');
    }

    /** Store new record */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $id = Str::uuid()->toString();

        DB::table('review_attributes')->insert([
            'id'         => $id,
            'title'      => $request->title,
            'isActive'   => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('reviewattributes')->with('success', 'Review Attribute created successfully.');
    }

    /** Show edit form */
    public function edit(string $id): View
    {
        $record = DB::table('review_attributes')->where('id', $id)->first();
        if (! $record) {
            abort(404);
        }
        return view('reviewattributes.edit', compact('record', 'id'));
    }

    /** Update record */
    public function update(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        DB::table('review_attributes')->where('id', $id)->update([
            'title'      => $request->title,
            'updated_at' => now(),
        ]);

        return redirect()->route('reviewattributes')->with('success', 'Review Attribute updated successfully.');
    }

    /** Delete single record */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        DB::table('review_attributes')->where('id', $id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('reviewattributes')->with('success', 'Deleted successfully.');
    }

    /** Bulk delete */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (! empty($ids)) {
            DB::table('review_attributes')->whereIn('id', $ids)->delete();
        }
        return response()->json(['success' => true]);
    }

    /** Toggle active status */
    public function toggle(Request $request, string $id): JsonResponse
    {
        $record = DB::table('review_attributes')->where('id', $id)->first();
        if (! $record) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $current  = (bool) ($record->isActive ?? $record->is_active ?? true);
        $newValue = ! $current;

        DB::table('review_attributes')->where('id', $id)->update([
            'isActive'   => $newValue ? 1 : 0,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'isActive' => $newValue]);
    }

    private function userCanDelete(): bool
    {
        $user = auth()->user();
        if ($user && (int) $user->role_id === 1) {
            return true;
        }
        $permissions = json_decode(session('user_permissions', '[]'), true) ?: [];
        return in_array('review.attributes.delete', $permissions, true)
            || in_array('review-attributes.delete', $permissions, true);
    }
}
