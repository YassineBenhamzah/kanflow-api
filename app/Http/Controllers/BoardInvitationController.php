<?php

namespace App\Http\Controllers;

use App\Models\BoardInvitation;
use Illuminate\Http\Request;

class BoardInvitationController extends Controller
{
    /**
     * Show details of a pending invitation.
     */
    public function show($token)
    {
        $invitation = BoardInvitation::with('board:id,name,owner_id')->where('token', $token)->firstOrFail();

        // Load the inviter (the board owner)
        $inviter = $invitation->board->owner;

        return response()->json([
            'email' => $invitation->email,
            'board_name' => $invitation->board->name,
            'inviter_name' => $inviter ? $inviter->name : 'Someone',
        ]);
    }

    /**
     * Accept a pending invitation.
     */
    public function accept(Request $request, $token)
    {
        $invitation = BoardInvitation::where('token', $token)->firstOrFail();

        // The user must be authenticated
        $user = $request->user();

        // Check if the authenticated user's email matches the invitation email.
        // Optional: you can remove this check if you want anyone with the link to be able to accept it
        // regardless of the email they used to register. Let's keep it secure for now.
        if (strtolower($user->email) !== strtolower($invitation->email)) {
            return response()->json(['message' => 'This invitation was sent to a different email address.'], 403);
        }

        // Add user to the board if not already a member
        if (!$invitation->board->members()->where('user_id', $user->id)->exists()) {
            $invitation->board->members()->attach($user->id, ['role' => 'member']);
        }

        // Delete the invitation
        $invitation->delete();

        return response()->json([
            'message' => 'Invitation accepted successfully.',
            'board_id' => $invitation->board_id
        ]);
    }
}
