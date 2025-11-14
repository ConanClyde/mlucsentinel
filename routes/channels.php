<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
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

Broadcast::channel('colleges', function ($user) {
    return auth()->check();
});

Broadcast::channel('programs', function ($user) {
    return auth()->check();
});

Broadcast::channel('fees', function ($user) {
    // Only marketing admins and global admins can listen to fee updates
    // user_type is an Enum, so we need to get its value
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator' && $user->administrator) {
        // Load the adminRole relationship
        $user->administrator->load('adminRole');

        return $user->administrator->adminRole && $user->administrator->adminRole->name === 'Marketing';
    }

    return false;
});

Broadcast::channel('patrol-logs', function ($user) {
    // Only security admins and global admins can listen to patrol log updates
    // user_type is an Enum, so we need to get its value
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator' && $user->administrator) {
        // Load the adminRole relationship
        $user->administrator->load('adminRole');

        return $user->administrator->adminRole && $user->administrator->adminRole->name === 'Security';
    }

    return false;
});

Broadcast::channel('reports', function ($user) {
    // General reports channel - allows all report admins to subscribe
    // Actual report visibility is filtered by separate channels (student-reports and non-student-reports)
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator' && $user->administrator) {
        $user->administrator->load('adminRole');
        $roleName = $user->administrator->adminRole->name ?? null;

        return in_array($roleName, ['SAS (Student Affairs & Services)', 'Security', 'Chancellor']);
    }

    return false;
});

Broadcast::channel('student-reports', function ($user) {
    // Student violation reports - ONLY for SAS Admin and Global Admin
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator' && $user->administrator) {
        $user->administrator->load('adminRole');
        $roleName = $user->administrator->adminRole->name ?? null;

        // Only SAS Admin
        return $roleName === 'SAS (Student Affairs & Services)';
    }

    return false;
});

Broadcast::channel('non-student-reports', function ($user) {
    // Non-student violation reports - ONLY for Security Admin, Chancellor Admin, and Global Admin
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator' && $user->administrator) {
        $user->administrator->load('adminRole');
        $roleName = $user->administrator->adminRole->name ?? null;

        // Only Security and Chancellor Admins
        return in_array($roleName, ['Security', 'Chancellor']);
    }

    return false;
});

Broadcast::channel('map-locations', function ($user) {
    // Only administrators can listen to map location updates
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator') {
        return true;
    }

    return false;
});

Broadcast::channel('vehicle-types', function ($user) {
    // Only administrators can listen to vehicle type updates
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator') {
        return true;
    }

    return false;
});

Broadcast::channel('map-location-types', function ($user) {
    // Only administrators can listen to map location type updates
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator') {
        return true;
    }

    return false;
});

Broadcast::channel('notifications.user.{id}', function ($user, $id) {
    // Users can only listen to their own notifications
    return (int) $user->id === (int) $id;
});

Broadcast::channel('stickers', function ($user) {
    // Only administrators can listen to sticker updates
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator') {
        return true;
    }

    return false;
});

Broadcast::channel('admin-notifications', function ($user) {
    // Only administrators can listen to admin notifications
    $userType = $user->user_type->value ?? $user->user_type;

    if ($userType === 'global_administrator') {
        return true;
    }

    if ($userType === 'administrator') {
        return true;
    }

    return false;
});
