<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Concord Puzzle</title>
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
            font-size: 30px;
            text-align: center;
            color: white;
            margin-bottom: 1.5rem;
        }
        
        .subtitle {
            font-family: 'Arvo', serif;
            font-weight: 400;
            font-size: 18px;
            text-align: center;
            color: white;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }
        
        .error-message {
            background-color: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: white;
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            text-align: center;
            display: none;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8">
        <h1 class="title mb-8">Concord Puzzle</h1>
        <h2 class="subtitle">4x4 Sudoku</h2>
        
        @if ($errors->any())
            <div class="error-message" style="display: block">
                {{ $errors->first() }}
            </div>
        @endif
        
        <div class="bg-white/10 rounded-lg p-8">
            <form action="{{ route('guest.login') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium mb-2">Enter Your Name</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           required 
                           class="w-full px-4 py-2 rounded bg-white/5 border border-white/20 focus:border-white/40 focus:outline-none text-white"
                           placeholder="Guest Name">
                </div>

                <div>
                    <button type="submit" 
                            class="w-full py-2 px-4 bg-white/20 hover:bg-white/30 rounded transition-colors font-medium">
                        Play as Guest
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
