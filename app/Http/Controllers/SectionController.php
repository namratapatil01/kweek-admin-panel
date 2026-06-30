<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;
use App\Models\Service;
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
