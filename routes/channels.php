<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Internal board — any authenticated manager or staff member may subscribe.
Broadcast::channel('board.{projectId}', function ($user, int $projectId) {
    return $user->hasRole(['manager', 'staff']);
});

// public-board.{projectId} is a public channel — no authorization callback needed.
