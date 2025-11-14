<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - MLUC Sentinel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1b1b18 0%, #3a3a36 100%);
            color: #EDEDEC;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .offline-container {
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .offline-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        .offline-icon svg {
            width: 100%;
            height: 100%;
            fill: #EDEDEC;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #A1A09A;
            margin-bottom: 30px;
        }

        .retry-btn {
            display: inline-block;
            padding: 12px 30px;
            background: #EDEDEC;
            color: #1b1b18;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .retry-btn:hover {
            background: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(237, 237, 236, 0.3);
        }

        .status {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #706f6c;
        }

        .status.online {
            color: #10b981;
        }

        .pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 0.9;
            }
            50% {
                opacity: 0.5;
            }
        }

        @media (max-width: 640px) {
            h1 {
                font-size: 1.5rem;
            }
            
            p {
                font-size: 1rem;
            }

            .offline-icon {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon pulse">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>

        <h1>You're Offline</h1>
        
        <p>
            It looks like you've lost your internet connection. 
            Don't worry, your work is safe! We'll automatically reconnect 
            when you're back online.
        </p>

        <button class="retry-btn" onclick="location.reload()">
            Try Again
        </button>

        <div class="status" id="connectionStatus">
            Checking connection...
        </div>
    </div>

    <script>
        // Check online status
        function updateOnlineStatus() {
            const statusEl = document.getElementById('connectionStatus');
            
            if (navigator.onLine) {
                statusEl.textContent = '✓ Connected! Refreshing...';
                statusEl.classList.add('online');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                statusEl.textContent = '✗ Still offline';
                statusEl.classList.remove('online');
            }
        }

        // Listen for online/offline events
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);

        // Check status on load
        updateOnlineStatus();

        // Auto-check every 5 seconds
        setInterval(updateOnlineStatus, 5000);
    </script>
</body>
</html>

