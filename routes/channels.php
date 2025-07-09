<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
Broadcast::channel('chat.{receiver_id}', function ($user, $receiver_id) {
    // return (int) $user->id === (int) $id;
    return true;
});

Broadcast::channel('presence.chat', function ( $user) {
    return ['id' => $user->id, 'name' => $user->name];
});

