<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('vehicles', function ($user) {
    return auth()->check();
});

Broadcast::channel('payments', function ($user) {
    return auth()->check();
});

Broadcast::channel('administrators', function ($user) {
    return auth()->check();
});

Broadcast::channel('students', function ($user) {
    return auth()->check();
});

Broadcast::channel('staff', function ($user) {
    return auth()->check();
});

Broadcast::channel('security', function ($user) {
    return auth()->check();
});

Broadcast::channel('stakeholders', function ($user) {
    return auth()->check();
});

Broadcast::channel('reporters', function ($user) {
    return auth()->check();
});
