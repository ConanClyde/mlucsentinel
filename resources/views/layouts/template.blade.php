<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Components Showcase</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        {{-- Button Components --}}
        @include('template.buttons')

        {{-- Camera Modal --}}
        @include('template.media')

        {{-- QR Scanner Modal --}}
        @include('template.qr-scanner')
        
        {{-- User Management Table --}}
        @include('template.tables')

        {{-- CRUD Modals --}}
        @include('template.modals')

      

        {{-- Registration Form --}}
        @include('template.registration')
    </div>

    
</body>
</html>
