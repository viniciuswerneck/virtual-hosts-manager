<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request, ActivityLogService $logger)
    {
        $query = ActivityLog::query();

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo.' 23:59:59');
        }

        $logs = $query->latest()->paginate(50)->withQueryString();
        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();

        return view('activity-logs.index', compact('logs', 'actions'));
    }

    public function export(Request $request)
    {
        $query = ActivityLog::query();

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo.' 23:59:59');
        }

        $logs = $query->latest()->get();

        $csv = "ID,Ação,Descrição,IP,Criado em\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                '"'.$log->action.'"',
                '"'.str_replace('"', '""', $log->description).'"',
                '"'.$log->ip.'"',
                '"'.$log->created_at->format('d/m/Y H:i:s').'"',
            ])."\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity-log-'.date('Y-m-d').'.csv"',
        ]);
    }
}
