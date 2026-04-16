<?php

namespace App\Http\Controllers;

use App\Events\TaskMoved;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function move(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $oldColumnId = $task->column_id;
        $task->update([
            'column_id' => $request->column_id,
            'position'  => $request->position,
        ]);

        // Broadcast minimal payload to all board members
        $boardId = $task->column->board_id;
        broadcast(new TaskMoved([
            'task_id'     => $task->id,
            'column_id'   => $request->column_id,
            'position'    => $request->position,
            'old_column'  => $oldColumnId,
        ], $boardId))->toOthers();

        return response()->json($task);
    }
}
