<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\User;
use App\Models\BoardInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BoardMemberController extends Controller
{
    public function store(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($user) {
            // User exists: Add directly
            if ($board->members()->where('user_id', $user->id)->exists()) {
                return response()->json(['message' => 'User is already a member'], 422);
            }

            $board->members()->attach($user->id, ['role' => 'member']);
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\BoardInvitation($board, $user, $request->user()));

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'pivot' => ['role' => 'member'],
                'status' => 'added'
            ], 201);
        } else {
            // User does not exist: Create an invitation
            // Check if invitation already exists
            if (BoardInvitation::where('board_id', $board->id)->where('email', $data['email'])->exists()) {
                return response()->json(['message' => 'An invitation has already been sent to this email'], 422);
            }

            $token = Str::random(32);
            BoardInvitation::create([
                'board_id' => $board->id,
                'email' => $data['email'],
                'token' => $token,
            ]);

            \Illuminate\Support\Facades\Mail::to($data['email'])->send(new \App\Mail\GuestBoardInvitation($board, $data['email'], $token, $request->user()));

            return response()->json([
                'message' => 'Invitation sent to guest',
                'status' => 'invited',
                'email' => $data['email']
            ], 201);
        }
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
