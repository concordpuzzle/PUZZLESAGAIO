<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Concord Puzzle</title>
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
            margin-bottom: 0.5rem;
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

        .sudoku-grid {
            width: 90%;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .sudoku-cell {
            width: 100%;
            aspect-ratio: 1;
            font-size: 24px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: white;
        }

        /* Style for the number input spinners */
        .sudoku-cell::-webkit-inner-spin-button {
            opacity: 1;
            background: rgba(255,255,255,0.15);  /* Slightly darker than the cell background */
            height: 100%;
            position: absolute;
            right: 0;
            top: 0;
            width: 20px;
        }

        .sudoku-cell:focus {
            background: rgba(255,255,255,0.2);
            outline: none;
        }

        .sudoku-cell.preset {
            background: rgba(255,255,255,0.15);
            font-weight: bold;
        }

        .border-right {
            border-right: 2px solid white;
        }

        .border-bottom {
            border-bottom: 2px solid white;
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
<body class="min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="title">Concord Puzzle</h1>
        <h2 class="subtitle">4x4 Sudoku</h2>

        <!-- Add error message container -->
        <div id="errorMessage" class="error-message"></div>

        <!-- Timer and Score -->
        <div class="text-center mb-8">
            <div class="text-4xl mb-4">
                <span id="timer" class="font-bold">10:00</span>
            </div>
            <div class="text-xl">
                Streak: <span id="streak" class="font-bold">0</span>
            </div>
        </div>

        <!-- Sudoku Grid -->
        <div class="flex justify-center mb-8">
            <div id="sudokuGrid" class="sudoku-grid grid grid-cols-4 gap-0 p-2 bg-white/10 rounded">
                <!-- Grid will be populated by JavaScript -->
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center mb-12">
            <button onclick="submitSolution()" 
                    class="bg-white/20 hover:bg-white/30 text-white font-bold py-3 px-6 rounded text-xl transition-colors">
                Submit Solution
            </button>
        </div>

        <!-- Leaderboard -->
        <div class="mt-8">
            <h2 class="text-2xl mb-4 text-center title">Leaderboard</h2>
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="py-2 text-left">#</th>
                        <th class="py-2 text-left">Player</th>
                        <th class="py-2 text-right">Streak</th>
                    </tr>
                </thead>
                <tbody id="leaderboardBody">
                    <!-- Leaderboard rows will be inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const playerName = "{{ session('player_name') }}";
        let streak = 0;
        let currentPuzzle = null;
        let currentTimeLeft = 600;

        function createSudokuGrid(puzzle) {
            const grid = document.getElementById('sudokuGrid');
            grid.innerHTML = '';
            
            for (let i = 0; i < 4; i++) {
                for (let j = 0; j < 4; j++) {
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.min = 1;
                    input.max = 4;
                    input.classList.add('sudoku-cell');
                    
                    // Add borders for 2x2 boxes
                    if (j === 1) input.classList.add('border-right');
                    if (i === 1) input.classList.add('border-bottom');
                    
                    if (puzzle[i][j] !== 0) {
                        input.value = puzzle[i][j];
                        input.readOnly = true;
                        input.classList.add('preset');
                    }
                    
                    // Prevent invalid input
                    input.addEventListener('input', function() {
                        if (this.value > 4) this.value = 4;
                        if (this.value < 1) this.value = '';
                    });

                    grid.appendChild(input);
                }
            }
        }

        function getCurrentSolution() {
            const cells = document.getElementsByClassName('sudoku-cell');
            const solution = Array(4).fill().map(() => Array(4).fill(0));
            
            console.log('Getting current solution...'); // Debug log
            
            for (let i = 0; i < 16; i++) {
                const row = Math.floor(i / 4);
                const col = i % 4;
                solution[row][col] = parseInt(cells[i].value) || 0;
            }
            
            console.log('Current solution:', solution); // Debug log
            return solution;
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            // Hide after 3 seconds
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 3000);
        }

        function submitSolution() {
            const solution = getCurrentSolution();
            
            // Check if puzzle is complete
            if (solution.some(row => row.some(cell => cell === 0))) {
                showError('Please fill in all cells!');
                return;
            }

            fetch('/puzzle/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    solution: solution,
                    playerName: playerName
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Server response:', text);
                        throw new Error('Server error');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Response:', data);
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.correct) {
                    streak++;
                    showError('Correct! Well done!');
                } else {
                    streak = 0;
                    showError('Sorry, that\'s not correct. Try again!');
                }
                
                document.getElementById('streak').textContent = streak;
                if (data.leaderboard) {
                    updateLeaderboard(data.leaderboard);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error checking solution. Please try again.');
            });
        }

        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        }

        function updateLeaderboard(leaderboard) {
            const tbody = document.getElementById('leaderboardBody');
            tbody.innerHTML = leaderboard.map((player, index) => `
                <tr class="${player.name === playerName ? 'bg-white/5' : ''}">
                    <td class="py-4 text-lg">${index + 1}</td>
                    <td class="py-4 text-lg">
                        ${player.name}
                        ${player.name === playerName ? ' (You)' : ''}
                    </td>
                    <td class="py-4 text-lg text-right">${player.streak}</td>
                </tr>
            `).join('');
        }

        function updateGameState() {
            fetch('/puzzle/state')
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Server response:', text);
                        throw new Error('Server error');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    showError(data.error);
                    return;
                }

                // Update timer
                currentTimeLeft = data.timeLeft;
                document.getElementById('timer').textContent = formatTime(currentTimeLeft);
                
                // Update puzzle if it's new
                if (!currentPuzzle || JSON.stringify(currentPuzzle) !== JSON.stringify(data.puzzle)) {
                    currentPuzzle = data.puzzle;
                    createSudokuGrid(data.puzzle);
                }

                // Update leaderboard
                if (data.leaderboard) {
                    updateLeaderboard(data.leaderboard);
                }

                // Warning color when time is low
                if (currentTimeLeft <= 60) {
                    document.getElementById('timer').classList.add('text-red-500');
                } else {
                    document.getElementById('timer').classList.remove('text-red-500');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Initial load
        updateGameState();

        // Update every second
        setInterval(updateGameState, 1000);
    </script>
</body>
</html>
