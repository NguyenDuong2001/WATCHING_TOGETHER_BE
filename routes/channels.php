<?php

use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleType;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('room.{id}', function ($user, $id) {
    return Auth::user()->role->name === RoleType::SuperAdmin ||
        Auth::user()->role->name === RoleType::Admin ||
        Auth::user()->id == Room::findOrFail($id)->user_id;
});
