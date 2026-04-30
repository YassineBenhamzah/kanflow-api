<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\User;
use Illuminate\Http\Request;

class BoardMemberController extends Controller
{
    public function store(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $data['email'])->first();

        // Don't add if already a member
        if ($board->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User is already a member'], 422);
        }

        $board->members()->attach($user->id, ['role' => 'member']);

        // Send invitation email
        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\BoardInvitation($board, $user, $request->user()));

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'pivot' => ['role' => 'member'],
        ], 201);
    }

    public function destroy(Board $board, User $user)
    {
        $this->authorize('update', $board);

        // Can't remove the owner
        $membership = $board->members()->where('user_id', $user->id)->first();
        if ($membership && $membership->pivot->role === 'owner') {
            return response()->json(['message' => 'Cannot remove the board owner'], 422);
        }

        $board->members()->detach($user->id);

        return response()->noContent();
    }
}
