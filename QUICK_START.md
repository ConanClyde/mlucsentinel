# Broadcasting Quick Start Guide

## Prerequisites

- Laravel application running
- Node.js and npm installed
- Required packages already in package.json

## Setup Steps

### 1. Run Setup Command

This will automatically configure your .env file:

```bash
php artisan reverb:setup
```

### 2. Build Frontend Assets

```bash
npm run build
```

Or for development with hot reload:

```bash
npm run dev
```

### 3. Start Services

Open **three** terminal windows:

**Terminal 1 - Laravel:**
```bash
php artisan serve
```

**Terminal 2 - Reverb:**
```bash
php artisan reverb:start
```

**Terminal 3 - Vite:**
```bash
npm run dev
```

### 4. Test

1. Visit: http://localhost:8000/users/administrators
2. Click the "Test" button
3. You should see: "Echo is working!"
4. Connection indicator should be green

## Verify It's Working

### Test with Two Browser Windows

1. Open http://localhost:8000/users/administrators in two windows
2. In one window, try to delete or update an administrator
3. Watch the other window update automatically

### Check Console Logs

Open browser DevTools (F12) and look for:
```
Setting up Echo connection...
Successfully subscribed to administrators channel
Real-time administrator updates initialized
```

## Common Issues

| Issue | Solution |
|-------|----------|
| Echo is undefined | Run `npm run build` or `npm run dev` |
| Red connection indicator | Start Reverb: `php artisan reverb:start` |
| No updates appearing | Check all 3 services are running |
| Port 8080 in use | Change REVERB_PORT in .env and restart |
| import.meta error | Run `npm run build` to rebuild assets |
| WebSocket closed error | Ensure Reverb is running and .env is configured |

### Quick Diagnostics

Run this to test broadcasting:
```bash
php test-broadcast.php
```

Check browser console for:
- "Initializing Laravel Echo with Reverb..."
- "Connected to Reverb WebSocket server"
- "Real-time administrator updates initialized"

## What Was Changed

### Backend
- `app/Events/AdministratorUpdated.php` - Now uses `ShouldBroadcastNow`
- `config/broadcasting.php` - Default set to 'reverb'
- `.env.example` - Added Reverb configuration

### Frontend
- `resources/js/admin/administrators-realtime.js` - New real-time handler
- `resources/js/app.js` - Imports the new module
- `resources/views/admin/users/administrators.blade.php` - Updated with new JavaScript

## How It Works

```
User Action → Event Fired → Reverb Broadcast → WebSocket → Echo → JavaScript → DOM Update
```

All connected clients see changes instantly without refreshing the page.

## Next Steps

- Read BROADCASTING_SETUP.md for detailed documentation
- Extend to other models (reports, vehicles, etc.)
- Configure for production deployment

## Need Help?

1. Check browser console for errors
2. Verify all three services are running
3. Review BROADCASTING_SETUP.md for troubleshooting
