<?php

namespace App\Services\Worker;

use App\Models\AppNotification;
use App\Models\AppUser;
use App\Models\ChatThread;
use App\Models\ChatWorker;
use App\Models\ProviderOrder;
use Illuminate\Support\Carbon;

class WorkerRealtimeService
{
    public function __construct(protected WorkerProfileService $profileService)
    {
    }

    /**
     * Polling endpoint replacing Firestore StreamBuilder listeners in Nexa_worker.
     */
    public function poll(AppUser $user, ?string $since = null, ?string $jobsTab = null): array
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $sinceAt = $this->parseSince($since);

        $jobs = $this->changedJobs($worker->id, $sinceAt, $jobsTab);
        $notifications = $this->changedNotifications($sinceAt);
        $inbox = $this->changedInbox($worker->id, $sinceAt);

        return [
            'server_time' => now()->toIso8601String(),
            'since' => $sinceAt->toIso8601String(),
            'has_changes' => $jobs !== [] || $notifications !== [] || $inbox['conversations'] !== [] || $inbox['messages'] !== [],
            'online' => (bool) ($worker->toDocumentArray()['online'] ?? false),
            'jobs' => $jobs,
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

    protected function changedJobs(string $workerId, Carbon $sinceAt, ?string $tab): array
    {
        $query = ProviderOrder::query()
            ->where('workerId', $workerId)
            ->where(function ($q) use ($sinceAt) {
                $q->where('updated_at', '>=', $sinceAt)
                    ->orWhere('createdAt', '>=', $sinceAt);
            });

        if ($tab) {
            match ($tab) {
                'upcoming', 'assigned' => $query->whereIn('status', [
                    WorkerJobService::STATUS_ACCEPTED,
                    WorkerJobService::STATUS_ASSIGNED,
                    WorkerJobService::STATUS_ONGOING,
                ]),
                'today' => $query->whereIn('status', [
                    WorkerJobService::STATUS_ACCEPTED,
                    WorkerJobService::STATUS_ASSIGNED,
                    WorkerJobService::STATUS_ONGOING,
                ])->where(function ($q) {
                    $start = Carbon::today()->startOfDay();
                    $end = Carbon::today()->endOfDay();
                    $q->whereBetween('payload->newScheduleDateTime', [$start->toIso8601String(), $end->toIso8601String()])
                        ->orWhereBetween('createdAt', [$start, $end]);
                }),
                'ongoing' => $query->where('status', WorkerJobService::STATUS_ONGOING),
                'completed', 'history' => $query->where('status', WorkerJobService::STATUS_COMPLETED),
                'cancelled' => $query->whereIn('status', [
                    WorkerJobService::STATUS_REJECTED,
                    WorkerJobService::STATUS_CANCELLED,
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
                $q->where('role', 'worker')->orWhereNull('role');
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

    protected function changedInbox(string $workerId, Carbon $sinceAt): array
    {
        $conversations = ChatWorker::query()
            ->where('restaurantId', $workerId)
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

        $messages = ChatThread::query()
            ->where('chat_type', 'chat_worker')
            ->where('createdAt', '>=', $sinceAt)
            ->orderByDesc('createdAt')
            ->limit(50)
            ->get()
            ->map(fn ($item) => $item->toDocumentArray())
            ->values()
            ->all();

        return [
            'conversations' => $conversations,
            'messages' => $messages,
        ];
    }
}
