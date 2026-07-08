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
    protected function normalizeCustomerInput(array $data): array
    {
        if (array_key_exists('active', $data)) {
            $data['isActive'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN);
            $data['active'] = $data['isActive'];
        }

        return $data;
    }
}
