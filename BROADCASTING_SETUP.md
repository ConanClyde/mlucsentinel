# Laravel Broadcasting with Reverb - Setup Guide

This guide explains how to set up and use Laravel Broadcasting with Reverb for real-time updates in the MLUC Sentinel application.

## Overview

The broadcasting system enables real-time updates across the application without page reloads. When an administrator is created, updated, or deleted, all connected clients receive instant notifications and their UI updates automatically.

## Architecture Flow

1. **Action occurs** - Administrator updates a record (create/update/delete)
2. **Event fired** - AdministratorUpdated event implements ShouldBroadcastNow
3. **Laravel broadcasts** - Event sent to Reverb via the configured channel
4. **Reverb pushes** - WebSocket server pushes to all connected clients
5. **Laravel Echo receives** - Frontend receives the event via WebSocket
6. **JavaScript updates UI** - DOM updates without page reload

## Setup Instructions

### 1. Environment Configuration

Copy the Reverb configuration from .env.example to your .env file and generate secure keys.

### 2. Install Dependencies

```bash
npm install
```

### 3. Start Required Services

You need to run three separate processes:

- Terminal 1: php artisan serve
- Terminal 2: php artisan reverb:start
- Terminal 3: npm run dev

### 4. Verify Setup

1. Navigate to /users/administrators in your browser
2. Open browser DevTools Console (F12)
3. Click the Test button in the page header
4. You should see Echo is working alert and green connection indicator

## Testing Real-Time Updates

### Method 1: Multiple Browser Windows

Open the administrators page in two browser windows side-by-side and watch updates sync in real-time.

### Method 2: Using Tinker

Use php artisan tinker to manually broadcast events and test the system.

## File Structure

### Backend Files

- app/Events/AdministratorUpdated.php - Broadcast event class
- app/Http/Controllers/Admin/Users/AdministratorsController.php - Fires events on CRUD operations
- config/broadcasting.php - Broadcasting configuration
- config/reverb.php - Reverb server configuration

### Frontend Files

- resources/js/echo.js - Echo initialization
- resources/js/admin/administrators-realtime.js - Real-time update handler module
- resources/views/admin/users/administrators.blade.php - Administrators page with real-time UI

## Troubleshooting

### Connection Status Indicators

- Yellow - Connecting to Reverb
- Green - Connected successfully
- Red - Connection error
- Gray - Disconnected

### Common Issues

1. Echo is undefined - Ensure Vite dev server is running
2. Connection stays yellow/red - Verify Reverb is running on port 8080
3. Events not received - Check event name matches .administrator.updated
4. CORS errors - Reverb config already allows all origins

## Extending to Other Models

To add real-time updates for other resources, create an event, fire it in the controller, create a JavaScript module, and use it in the blade view.
