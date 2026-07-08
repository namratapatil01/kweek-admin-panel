<?php

namespace App\Services\Worker;

use App\Models\AppUser;
use App\Models\OnBoarding;
use App\Models\ProviderOrder;
use App\Models\Currency;
use App\Services\SettingsService;
use Illuminate\Support\Carbon;

class WorkerDashboardService
{
    public function __construct(
        protected WorkerProfileService $profileService,
        protected SettingsService $settingsService
    ) {
    }

    public function home(): array
    {
        return [
            'on_boarding' => OnBoarding::query()
                ->where('type', 'worker')
                ->get()
                ->map(fn ($item) => $item->toDocumentArray())
                ->values()
                ->all(),
            'currency' => Currency::query()
                ->where('isActive', true)
                ->first()?->toDocumentArray(),
            'settings' => [
                'globalSettings' => $this->settingsService->get('globalSettings', []),
                'Version' => $this->settingsService->get('Version', []),
                'termsAndConditions' => $this->settingsService->get('termsAndConditions', []),
                'privacyPolicy' => $this->settingsService->get('privacyPolicy', []),
            ],
        ];
    }

    public function dashboard(AppUser $user): array
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $workerId = $worker->id;

        $upcoming = $this->ordersForWorker($workerId)
            ->whereIn('status', ['Order Accepted', 'Order Assigned', 'Order Ongoing'])
            ->count();

        $assigned = $this->ordersForWorker($workerId)
            ->where('status', 'Order Assigned')
            ->count();

        $ongoing = $this->ordersForWorker($workerId)
            ->where('status', 'Order Ongoing')
            ->count();

        $completed = $this->ordersForWorker($workerId)
            ->where('status', 'Order Completed')
            ->count();

        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $today = $this->ordersForWorker($workerId)
            ->whereIn('status', ['Order Accepted', 'Order Assigned', 'Order Ongoing'])
            ->where(function ($q) use ($todayStart, $todayEnd) {
                $q->whereBetween('payload->newScheduleDateTime', [$todayStart->toIso8601String(), $todayEnd->toIso8601String()])
                    ->orWhereBetween('createdAt', [$todayStart, $todayEnd]);
            })
            ->count();

        return [
            'worker' => $worker->toDocumentArray(),
            'counts' => [
                'upcoming' => $upcoming,
                'assigned' => $assigned,
                'ongoing' => $ongoing,
                'completed' => $completed,
                'today' => $today,
            ],
            'online' => (bool) ($worker->toDocumentArray()['online'] ?? false),
            'provider' => $this->profileService->providerInfo($user),
            'settings' => [
                'globalSettings' => $this->settingsService->get('globalSettings', []),
            ],
        ];
    }

    protected function ordersForWorker(string $workerId)
    {
        return ProviderOrder::query()->where('workerId', $workerId);
    }
}
