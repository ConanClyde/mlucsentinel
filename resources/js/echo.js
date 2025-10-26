import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Get configuration from environment
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST || 'localhost';
const reverbPort = import.meta.env.VITE_REVERB_PORT || 8080;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

// Log configuration for debugging
console.log('Initializing Laravel Echo with Reverb...');
console.log('Config:', {
    key: reverbKey ? `${reverbKey.substring(0, 4)}...` : 'NOT SET',
    host: reverbHost,
    port: reverbPort,
    scheme: reverbScheme
});

if (!reverbKey) {
    console.error('❌ VITE_REVERB_APP_KEY is not set! Please run: php artisan reverb:setup');
}

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
    });

    // Add connection event listeners
    if (window.Echo.connector && window.Echo.connector.pusher) {
        window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('✅ Connected to Reverb WebSocket server');
        });

        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            console.warn('⚠️ Disconnected from Reverb WebSocket server');
        });

        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('❌ Reverb connection error:', err);
        });

        window.Echo.connector.pusher.connection.bind('state_change', (states) => {
            console.log('Connection state changed:', states.previous, '→', states.current);
        });
    }

    console.log('✅ Laravel Echo initialized successfully');
} catch (error) {
    console.error('❌ Failed to initialize Laravel Echo:', error);
}
