<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Board $board)
    {
        $this->authorize('view', $board);

        $logs = ActivityLog::where('board_id', $board->id)
            ->with('user:id,name,email')
            ->latest()
            ->take(50)
            ->get();

        return response()->json($logs);
    }
}
