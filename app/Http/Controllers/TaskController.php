<?php

namespace App\Http\Controllers;

use App\Events\TaskMoved;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, Column $column) {
        $this->authorize('update', $column->board);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);
        $maxPos = $column->tasks()->max('position') ?? 0;
        $task = $column->tasks()->create([...$data, 'position' => $maxPos + 1]);
        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task) {
        $this->authorize('update', $task);
        $task->update($request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]));
        return response()->json($task);
    }

    public function destroy(Task $task) {
        $this->authorize('update', $task);
        $task->delete();
        return response()->noContent();
    }

    public function move(Request $request, Task $task) {
        $this->authorize('update', $task);
        $oldColumnId = $task->column_id;
        $task->update([
            'column_id' => $request->column_id,
            'position'  => $request->position,
        ]);
        // Temporarily disabled until Pusher is configured
        // $boardId = $task->column->board_id;
        // broadcast(new TaskMoved([
        //     'task_id'     => $task->id,
        //     'column_id'   => $request->column_id,
        //     'position'    => $request->position,
        //     'old_column'  => $oldColumnId,
        // ], $boardId))->toOthers();
        return response()->json($task);
    }

    public function reorder(Request $request) {
        $data = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.position' => 'required|numeric',
        ]);
        foreach ($data['tasks'] as $item) {
            Task::where('id', $item['id'])->update(['position' => $item['position']]);
        }
        return response()->json(['message' => 'Reordered']);
    }
}
