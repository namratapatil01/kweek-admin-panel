<?php

namespace App\Services\Provider;

use App\Models\AppNotification;
use App\Models\AppUser;
use App\Models\ChatDriver;
use App\Models\ChatProvider;
use App\Models\ChatStore;
use App\Models\ChatThread;
use App\Models\ChatWorker;
use App\Models\ProviderOrder;
use Illuminate\Support\Carbon;

class ProviderRealtimeService
{
    public function __construct(
        protected ProviderBookingService $bookingService
    ) {
    }

    /**
     * Optimized polling endpoint replacing Firebase realtime listeners.
     */
    public function poll(AppUser $provider, ?string $since = null, ?string $bookingTab = null): array
    {
        $sinceAt = $this->parseSince($since);

        $bookings = $this->changedBookings($provider->id, $sinceAt, $bookingTab);
        $notifications = $this->changedNotifications($sinceAt);
        $inbox = $this->changedInbox($provider->id, $sinceAt);

        return [
            'server_time' => now()->toIso8601String(),
            'since' => $sinceAt->toIso8601String(),
            'has_changes' => $bookings !== [] || $notifications !== [] || $inbox !== [],
            'wallet_amount' => (float) ($provider->fresh()->wallet_amount ?? 0),
            'bookings' => $bookings,
            'notifications' => $notifications,
            'inbox' => $inbox,
        ];
    }

    protected function parseSince(?string $since): Carbon
    {
        if ($since) {
            try {
                return Carbon::parse($since);
            } catch (\Throwable) {
                // fall through
            }
        }

        return now()->subMinutes(2);
    }

    protected function changedBookings(string $providerId, Carbon $sinceAt, ?string $tab): array
    {
        $query = ProviderOrder::query()->where(function ($q) use ($providerId) {
            $q->where('provider->author', $providerId)
                ->orWhere('payload->provider.author', $providerId)
                ->orWhere('payload->providerId', $providerId);
        })->where(function ($q) use ($sinceAt) {
            $q->where('updated_at', '>=', $sinceAt)
                ->orWhere('createdAt', '>=', $sinceAt);
        });

        if ($tab) {
            match ($tab) {
                'new' => $query->where('status', ProviderBookingService::STATUS_PLACED),
                'today' => $query->whereIn('status', [
                    ProviderBookingService::STATUS_ACCEPTED,
                    ProviderBookingService::STATUS_ASSIGNED,
                    ProviderBookingService::STATUS_ONGOING,
                ]),
                'completed' => $query->where('status', ProviderBookingService::STATUS_COMPLETED),
                'cancelled' => $query->whereIn('status', [
                    ProviderBookingService::STATUS_REJECTED,
                    ProviderBookingService::STATUS_CANCELLED,
                ]),
                default => null,
            };
        }

        return $query->orderByDesc('updated_at')
            ->limit(50)
            ->get()
            ->map(fn ($item) => $item->toDocumentArray())
            ->values()
            ->all();
    }

    protected function changedNotifications(Carbon $sinceAt): array
    {
        return AppNotification::query()
            ->where(function ($q) {
                $q->where('role', 'provider')->orWhereNull('role');
            })
            ->where(function ($q) use ($sinceAt) {
                $q->where('updated_at', '>=', $sinceAt)
                    ->orWhere('createdAt', '>=', $sinceAt);
            })
            ->orderByDesc('createdAt')
            ->limit(20)
            ->get()
            ->map(fn ($item) => $item->toDocumentArray())
            ->values()
            ->all();
    }

    protected function changedInbox(string $providerId, Carbon $sinceAt): array
    {
        $inboxes = [];

        foreach ([ChatProvider::class, ChatWorker::class, ChatDriver::class, ChatStore::class] as $modelClass) {
            $items = $modelClass::query()
                ->where('restaurantId', $providerId)
                ->where(function ($q) use ($sinceAt) {
                    $q->where('updated_at', '>=', $sinceAt)
                        ->orWhere('createdAt', '>=', $sinceAt);
                })
                ->orderByDesc('createdAt')
                ->limit(20)
                ->get();

            foreach ($items as $item) {
                $doc = $item->toDocumentArray();
                $doc['chat_model'] = class_basename($modelClass);
                $inboxes[] = $doc;
            }
        }

        $threads = ChatThread::query()
            ->where('createdAt', '>=', $sinceAt)
            ->whereIn('chat_type', ['chat_provider', 'chat_worker', 'chat_driver', 'chat_store'])
            ->orderByDesc('createdAt')
            ->limit(50)
            ->get()
            ->map(fn ($item) => $item->toDocumentArray())
            ->values()
            ->all();

        return [
            'conversations' => $inboxes,
            'messages' => $threads,
        ];
    }
}
