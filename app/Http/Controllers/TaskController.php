<?php

namespace App\Http\Controllers;

use App\Events\TaskMoved;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Events\TaskDeleted;
use App\Models\Column;
use App\Models\Task;
use App\Models\ActivityLog;
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
        $task->load('assignee', 'checklistItems');

        broadcast(new TaskCreated([
            'task' => $task->toArray(),
            'column_id' => $column->id,
        ], $column->board_id))->toOthers();

        ActivityLog::create([
            'board_id' => $column->board_id,
            'user_id' => $request->user()->id,
            'action' => 'task.created',
            'meta' => ['task_title' => $task->title, 'column_name' => $column->name],
        ]);

        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task) {
        $this->authorize('update', $task);
        $task->update($request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]));
        $task->load('assignee', 'checklistItems');

        broadcast(new TaskUpdated([
            'task' => $task->toArray(),
        ], $task->column->board_id))->toOthers();

        ActivityLog::create([
            'board_id' => $task->column->board_id,
            'user_id' => $request->user()->id,
            'action' => 'task.updated',
            'meta' => ['task_title' => $task->title],
        ]);

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task) {
        $this->authorize('update', $task);
        $boardId = $task->column->board_id;
        $taskId = $task->id;
        $columnId = $task->column_id;
        $taskTitle = $task->title;
        $task->delete();

        broadcast(new TaskDeleted([
            'task_id' => $taskId,
            'column_id' => $columnId,
        ], $boardId))->toOthers();

        ActivityLog::create([
            'board_id' => $boardId,
            'user_id' => $request->user()->id,
            'action' => 'task.deleted',
            'meta' => ['task_title' => $taskTitle],
        ]);

        return response()->noContent();
    }

    public function move(Request $request, Task $task) {
        $this->authorize('update', $task);
        $oldColumnId = $task->column_id;
        $task->update([
            'column_id' => $request->column_id,
            'position'  => $request->position,
        ]);
        // Broadcast the move event to other users
        $boardId = $task->column->board_id;
        broadcast(new TaskMoved([
            'task_id'     => $task->id,
            'column_id'   => $request->column_id,
            'position'    => $request->position,
            'old_column'  => $oldColumnId,
        ], $boardId))->toOthers();

        $oldColumn = Column::find($oldColumnId);
        $newColumn = Column::find($request->column_id);
        ActivityLog::create([
            'board_id' => $boardId,
            'user_id' => $request->user()->id,
            'action' => 'task.moved',
            'meta' => [
                'task_title' => $task->title,
                'from_column' => $oldColumn?->name,
                'to_column' => $newColumn?->name,
            ],
        ]);

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
