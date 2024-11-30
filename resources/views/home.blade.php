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
    </style>
</head>
<body>
    <h1 class="title">Games</h1>

    <div class="game-grid">
        <!-- Sudoku Icon -->
        <a href="{{ route('sudoku') }}" class="game-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
            </svg>
            <span>Sudoku</span>
        </a>

        <!-- Placeholder Icons -->
        @for ($i = 1; $i <= 17; $i++)
            <div class="game-icon placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>Coming Soon</span>
            </div>
        @endfor
    </div>
</body>
</html>
