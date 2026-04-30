<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
     public function index(Request $request) {
        return $request->user()
            ->boards()
            ->with('members')
            ->latest()->get();
    }

    public function show(Board $board) {
        $this->authorize('view', $board);
        // Eager load everything — avoids N+1
        return $board->load('columns.tasks.assignee', 'columns.tasks.checklistItems', 'members');
    }

    public function store(Request $request) {
        $board = $request->user()->ownedBoards()->create($request->all());
        // Owner is also a member
        $board->members()->attach($request->user()->id, ['role' => 'owner']);
        
        // Auto-create 3 default columns
        $board->columns()->createMany([
            ['name' => 'To Do',        'position' => 1],
            ['name' => 'In Progress',  'position' => 2],
            ['name' => 'Done',         'position' => 3],
        ]);
        
        return response()->json($board->load('columns'), 201);
    }

    public function destroy(Board $board) {
        $this->authorize('delete', $board);
        $board->delete();
        return response()->noContent();
    }
}
