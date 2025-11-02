<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('vehicles', function ($user) {
    return auth()->check();
});

Broadcast::channel('payments', function ($user) {
    // Only marketing admins and global admins can listen to payment updates
    // user_type is an Enum, so we need to get its value
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator' && $user->administrator) {
        // Load the adminRole relationship (not 'role')
        $user->administrator->load('adminRole');

        return $user->administrator->adminRole && $user->administrator->adminRole->name === 'Marketing';
    }

    return false;
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
