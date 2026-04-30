<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;
use App\Models\ChecklistItem;

class ChecklistItemController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $this->authorize('update', $task->column->board);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $item = $task->checklistItems()->create([
            'title' => $validated['title'],
            'is_completed' => false,
        ]);

        return response()->json($item, 201);
    }

    public function update(Request $request, ChecklistItem $checklistItem)
    {
        $this->authorize('update', $checklistItem->task->column->board);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'is_completed' => 'sometimes|boolean',
        ]);

        $checklistItem->update($validated);

        return response()->json($checklistItem);
    }

    public function destroy(ChecklistItem $checklistItem)
    {
        $this->authorize('update', $checklistItem->task->column->board);

        $checklistItem->delete();

        return response()->noContent();
    }
}
