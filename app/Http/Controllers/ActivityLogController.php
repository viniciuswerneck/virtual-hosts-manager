<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

class ActivityLogController extends Controller
{
    public function index(ActivityLogService $logger)
    {
        $logs = $logger->paginate(50);

        return view('activity-logs.index', compact('logs'));
    }
}
