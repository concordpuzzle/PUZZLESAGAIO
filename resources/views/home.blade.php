<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Arvo:wght@700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #0c2461;
            color: #ffffff;
            font-family: 'Cabin', sans-serif;
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

        #addToHomeScreen {
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        @supports (backdrop-filter: blur(10px)) {
            #addToHomeScreen {
                background: rgba(255,255,255,0.1);
                backdrop-filter: blur(10px);
            }
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

    <div id="addToHomeScreen" class="fixed bottom-0 left-0 right-0 bg-white/10 backdrop-blur-lg p-4 transform translate-y-full transition-transform duration-300 ease-in-out">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="text-sm">Add to Home Screen for the best experience!</span>
            </div>
            <button onclick="dismissAddToHome()" class="text-sm underline">Dismiss</button>
        </div>
    </div>

    <script>
        // Check if the user is on iOS
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        
        // Check if the web app is in standalone mode (already added to home screen)
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                            window.navigator.standalone || 
                            document.referrer.includes('android-app://');

        // Check if we've already shown the prompt
        const hasShownPrompt = localStorage.getItem('addToHomePromptShown');

        // Show the prompt if:
        // 1. User is on iOS
        // 2. Not already in standalone mode
        // 3. Haven't shown the prompt before
        if (isIOS && !isStandalone && !hasShownPrompt) {
            // Wait a few seconds before showing the prompt
            setTimeout(() => {
                const prompt = document.getElementById('addToHomeScreen');
                prompt.classList.remove('translate-y-full');
            }, 2000);
        }

        // Function to dismiss the prompt
        function dismissAddToHome() {
            const prompt = document.getElementById('addToHomeScreen');
            prompt.classList.add('translate-y-full');
            // Remember that we've shown the prompt
            localStorage.setItem('addToHomePromptShown', 'true');
        }

        // Add styles for Safari's "Add to Home" button
        if (isIOS) {
            const style = document.createElement('style');
            style.textContent = `
                @supports (-webkit-touch-callout: none) {
                    .game-icon {
                        -webkit-touch-callout: none;
                        -webkit-user-select: none;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    </script>
</body>
</html>
