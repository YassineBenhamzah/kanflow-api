<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function store(Request $request, Board $board) {
        $this->authorize('update', $board);
        $data = $request->validate(['name' => 'required|string|max:255']);
        $maxPos = $board->columns()->max('position') ?? 0;
        $column = $board->columns()->create([
            'name' => $data['name'],
            'position' => $maxPos + 1,
        ]);
        return response()->json($column, 201);
    }

    public function update(Request $request, Column $column) {
        $this->authorize('update', $column->board);
        $column->update($request->validate(['name' => 'required|string|max:255']));
        return response()->json($column);
    }

    public function destroy(Column $column) {
        $this->authorize('update', $column->board);
        $column->delete();
        return response()->noContent();
    }

    public function reorder(Request $request) {
        $data = $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|exists:columns,id',
            'columns.*.position' => 'required|numeric',
        ]);
        foreach ($data['columns'] as $item) {
            Column::where('id', $item['id'])->update(['position' => $item['position']]);
        }
        return response()->json(['message' => 'Reordered']);
    }
}
