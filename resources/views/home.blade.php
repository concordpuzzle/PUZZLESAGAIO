<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Concord Games">
    <meta name="theme-color" content="#0c2461">
    
    <!-- iPhone Icons -->
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('icons/icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('icons/icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('icons/icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/icon-180x180.png') }}">
    
    <!-- Splash Screens -->
    <link rel="apple-touch-startup-image" href="{{ asset('splash/iphone5_splash.png') }}" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash/iphone6_splash.png') }}" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash/iphoneplus_splash.png') }}" media="(device-width: 621px) and (device-height: 1104px) and (-webkit-device-pixel-ratio: 3)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash/iphonex_splash.png') }}" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash/iphonexr_splash.png') }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)">
    <link rel="apple-touch-startup-image" href="{{ asset('splash/iphonexsmax_splash.png') }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)">
    
    <title>Concord Games</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:wght@700&display=swap" rel="stylesheet">
    
    <!-- Prevent pinch zoom -->
    <script>
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });
    </script>
    
    <style>
        /* Existing styles... */
        
        /* Add these styles for better mobile experience */
        html {
            height: 100%;
            overflow: hidden;
        }
        
        body {
            height: 100%;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
            background-color: #0c2461;
            color: #ffffff;
            font-family: 'Cabin', sans-serif;
            /* Prevent text selection */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Remove tap highlight on iOS */
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        .title {
            font-family: 'Arvo', serif;
            font-weight: 700;
            font-size: 36px;
            text-align: center;
            color: white;
            margin: 2rem 0;
        }

        .game-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .game-icon {
            aspect-ratio: 1;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: white;
            transition: transform 0.2s, background-color 0.2s;
        }

        .game-icon:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.2);
        }

        .game-icon svg {
            width: 40px;
            height: 40px;
            margin-bottom: 8px;
        }

        .game-icon span {
            font-size: 14px;
            font-weight: 500;
        }

        .placeholder {
            opacity: 0.5;
            cursor: default;
        }

        .placeholder:hover {
            transform: none;
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <h1 class="title">Games</h1>

    <div class="game-grid">
        <!-- Sudoku Icon -->
        <a href="{{ route('sudoku') }}" class="game-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2z" />
            </svg>
            <span>Sudoku</span>
        </a>

        <!-- Placeholder Icons -->
        @for ($i = 1; $i <= 17; $i++)
            <div class="game-icon placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2z" />
                </svg>
                <span>Game {{ $i }}</span>
            </div>
        @endfor
    </div>
</body>
</html>
