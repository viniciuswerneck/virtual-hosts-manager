<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function log(string $action, string $description, ?Model $subject = null): void
    {
        ActivityLog::create([
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'ip' => request()->ip() ?? '127.0.0.1',
        ]);
    }

    public function latest(int $limit = 50): array
    {
        return ActivityLog::latest()->take($limit)->get()->toArray();
    }

    public function paginate(int $perPage = 30)
    {
        return ActivityLog::latest()->paginate($perPage);
    }
}
