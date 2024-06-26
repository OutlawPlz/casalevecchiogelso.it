<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('Reservations.{ulid}', function (User $user, $ulid) {
    return $user->reservations()->where('ulid', $ulid)->exists();
});
