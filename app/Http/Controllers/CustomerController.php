<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use App\Http\Requests\Admin\StoreModuleRequest;
use App\Http\Requests\Admin\UpdateModuleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CustomerController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function moduleSlug(): string
    {
        return 'users';
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function edit(...$params): View
    {
        return view('users.edit', [
            'id' => (string) end($params),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(StoreModuleRequest::buildRules($this->moduleSlug(), true));

        try {
            $this->crudService()->store($this->normalizeCustomerInput($validated));

            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route($this->indexRouteName())
                ->with('success', trans('lang.saved_successfully'));
        } catch (Throwable $e) {
            Log::error(static::class . '@store', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, ...$params): JsonResponse|RedirectResponse
    {
        $id = (string) end($params);
        $validated = $request->validate(UpdateModuleRequest::buildRules($this->moduleSlug(), false));

        try {
            $this->crudService()->update($id, $this->normalizeCustomerInput($validated));

            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()
                ->route($this->indexRouteName())
                ->with('success', trans('lang.update_success'));
        } catch (Throwable $e) {
            Log::error(static::class . '@update', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function show(...$params): View
    {
        $id = (string) end($params);
        return view('users.view', ['id' => $id]);
    }

    protected function normalizeCustomerInput(array $data): array
    {
        if (array_key_exists('active', $data)) {
            $data['isActive'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN);
            $data['active'] = $data['isActive'];
        }

        return $data;
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

        // Checkbox
        $row[] = $canDelete ? '<div class="d-flex align-items-center"><input type="checkbox" class="row-select" id="row_' . e($id) . '" data-id="' . e($id) . '"><label class="control-label mb-0" for="row_' . e($id) . '" style="min-width: 20px; padding-left: 0;"></label></div>' : '';

        // User Info (Image + Name)
        $profilePic = ($record->profilePictureURL && $record->profilePictureURL !== 'undefined' && $record->profilePictureURL !== 'null') ? $record->profilePictureURL : $placeholderImage;
        $name = e(trim($record->firstName . ' ' . $record->lastName));
        $userInfo = '<div class="d-flex align-items-center">
                        <img src="' . e($profilePic) . '" onerror="this.onerror=null;this.src=\'' . e($placeholderImage) . '\'" alt="user" class="rounded-circle mr-3" style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <a href="' . route($route . '.show', $id) . '" class="font-weight-bold" style="color: #000; text-decoration: underline; font-family: \'Urbanist\', sans-serif;">' . $name . '</a>
                        </div>
                    </div>';
        $row[] = $userInfo;

        // Contact Info
        $contactInfo = '<div style="font-family: \'Urbanist\', sans-serif;">
                            <div style="color: #334155; font-size: 14px;">' . e($record->email ?: '-') . '</div>
                            <div style="color: #64748b; font-size: 13px; margin-top: 2px;">' . e($record->phoneNumber ?: '-') . '</div>
                        </div>';
        $row[] = $contactInfo;

        // Date
        $datePart = $record->created_at ? $record->created_at->format('D M d Y') : '-';
        $timePart = $record->created_at ? $record->created_at->format('h:i:s A') : '';
        $dateHtml = '<div style="font-family: \'Urbanist\', sans-serif; font-size: 14px; color: #334155;">
                        <div>' . $datePart . '</div>
                        <div style="color: #64748b; font-size: 13px; margin-top: 2px;">' . $timePart . '</div>
                    </div>';
        $row[] = $dateHtml;

        // Active Toggle
        $isActive = filter_var($record->active, FILTER_VALIDATE_BOOLEAN);
        $checked = $isActive ? 'checked' : '';
        $row[] = '<label class="switch"><input type="checkbox" class="status-toggle" data-id="' . e($id) . '" ' . $checked . '><span class="slider round"></span></label>';

        // Actions
        $btnStyle = 'width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid; margin-right: 5px; text-decoration: none; transition: 0.3s;';
        $actions = '<div class="d-flex align-items-center">';
        $actions .= '<a href="' . route('users.walletstransaction', $id) . '" title="Wallet History" style="' . $btnStyle . ' color: #d97706; border-color: #fcd34d; background: #fff;"><i class="mdi mdi-wallet" style="font-size: 16px;"></i></a>';
        $actions .= '<a href="' . route($route . '.show', $id) . '" title="View" style="' . $btnStyle . ' color: #9333ea; border-color: #d8b4fe; background: #fff;"><i class="mdi mdi-eye" style="font-size: 16px;"></i></a>';
        if (! ($config['readonly'] ?? false)) {
            $actions .= '<a href="' . route($route . '.edit', $id) . '" title="Edit" style="' . $btnStyle . ' color: #0284c7; border-color: #7dd3fc; background: #fff;"><i class="mdi mdi-lead-pencil" style="font-size: 16px;"></i></a>';
        }
        if ($canDelete) {
            $actions .= '<a href="javascript:void(0)" class="delete-row" data-id="' . e($id) . '" title="Delete" style="' . $btnStyle . ' color: #dc2626; border-color: #fca5a5; background: #fff;"><i class="mdi mdi-delete" style="font-size: 16px;"></i></a>';
        }
        $actions .= '</div>';
        $row[] = $actions;

        return $row;
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $active = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        try {
            \DB::table('users')->where('id', $id)->update([
                'active' => $active,
                'isActive' => (bool)$active
            ]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
