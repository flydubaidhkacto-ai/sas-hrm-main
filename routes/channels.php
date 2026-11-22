<?php

use Illuminate\Support\Facades\Broadcast;

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

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });


// Broadcast::channel('chat.{id}', function ($user, $id) {
//     return true; //(int) $user->id === (int) $id;
// });
// Broadcast::channel('private.chat.{id}', function ($user, $id) {
//     return true; //(int) $user->id === (int) $id;
// });

// Broadcast::channel('presence.chat', function ($user) {
//     return true; // ['id' => $user->id, 'name' => $user->name];
// });


// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });


// Broadcast::channel('chat', function ($user) {
//     // Ensure that the authenticated user matches the userId in the channel
//     // dd($user);

//     return true; // (int) $user->id === (int) $userId;
// });
