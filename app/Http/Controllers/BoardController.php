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
        return $board->load('columns.tasks.assignee', 'members');
    }

    public function store(Request  $request) {
        $board = $request->user()->ownedBoards()->create($request->all());
        // Owner is also a member
        $board->members()->attach($request->user()->id, ['role' => 'owner']);
        return response()->json($board, 201);
    }

    public function destroy(Board $board) {
        $this->authorize('delete', $board);
        $board->delete();
        return response()->noContent();
    }
}
