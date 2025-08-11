<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat', function (User $user) {
    return in_array($user->role, [\App\Enums\Role::TEACHER, \App\Enums\Role::HEADTEACHER]);
});

Broadcast::channel('dm.{channel_ids}', function (User $user, string $channel_ids) {
    $ids = explode('-', $channel_ids);
    return in_array($user->id, $ids);
});
