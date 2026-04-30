<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Comment;
use App\Events\CommentAdded;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Task $task)
    {
        $this->authorize('view', $task->column->board);
        return response()->json($task->comments()->with('user:id,name,email')->oldest()->get());
    }

    public function store(Request $request, Task $task)
    {
        $this->authorize('view', $task->column->board);

        $request->validate(['body' => 'required|string|max:1000']);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        $comment->load('user:id,name,email');

        broadcast(new CommentAdded($comment, $task->column->board_id));

        return response()->json($comment, 201);
    }

    public function destroy(Comment $comment)
    {
        $board = $comment->task->column->board;
        $this->authorize('update', $board);

        // Only the comment author or board owner can delete
        if ($comment->user_id !== request()->user()->id && $board->owner_id !== request()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return response()->noContent();
    }
}
