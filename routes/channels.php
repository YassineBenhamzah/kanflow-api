<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Board;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('board.{boardId}', function (User $user, int $boardId) {
    return Board::find($boardId)?->isMember($user);
});

// Presence channel — returns user data to show online avatars
Broadcast::channel('presence-board.{boardId}', function (User $user, int $boardId) {
    if (Board::find($boardId)?->isMember($user)) {
        return ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar];
    }
});
