<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Trivia Game</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Moul&family=Cabin:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body {
                background-color: #b71540;
                color: #ffffff;
                font-family: 'Cabin', sans-serif;
            }
            .title {
                font-family: 'Moul', serif;
            }
            #question {
                font-size: 2rem;
                line-height: 1.2;
                margin-bottom: 2rem;
            }
            #answers button {
                font-family: 'Moul', serif;
                font-size: 1.75rem;
                line-height: 1.3;
                background-color: rgba(255, 255, 255, 0.1);
                transition: all 0.2s ease-in-out;
            }
            #answers button:hover:not(:disabled) {
                background-color: rgba(255, 255, 255, 0.2);
                transform: translateY(-2px);
            }
            #answers button:disabled {
                cursor: not-allowed;
            }
            .bg-green-500 {
                background-color: #48bb78 !important;
            }
            .bg-red-500 {
                background-color: #f56565 !important;
            }
        </style>
    </head>
    <body class="min-h-screen">
        <div class="container mx-auto px-4 py-8 max-w-4xl">
            <h1 class="title text-center text-5xl mb-8">Trivia Time</h1>
            
            <!-- Timer and Score -->
            <div class="text-center mb-12">
                <div class="text-4xl mb-4">
                    <span id="timer" class="font-bold">15</span>s
                </div>
                <div class="text-xl">
                    Streak: <span id="streak" class="font-bold">0</span>
                </div>
            </div>

            <!-- Category -->
            <div class="text-center mb-6">
                <span id="category" class="text-lg opacity-75"></span>
            </div>

            <!-- Question -->
            <div class="text-center mb-12">
                <h2 id="question" class="title text-5xl font-bold mb-12 leading-tight"></h2>
                
                <!-- Answer Buttons -->
                <div id="answers" class="grid grid-cols-1 gap-6 max-w-3xl mx-auto">
                    <!-- Buttons will be inserted here -->
                </div>
            </div>

            <!-- Feedback Message -->
            <div id="feedback" class="text-center text-xl mt-6 hidden"></div>

            <!-- Leaderboard -->
            <div class="mt-8">
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
            const playerName = localStorage.getItem('playerName') || prompt('Enter your name:');
            localStorage.setItem('playerName', playerName);

            let streak = 0;
            let currentTimeLeft = 15;
            
            // Get DOM elements
            const timerElement = document.getElementById('timer');
            const streakElement = document.getElementById('streak');
            const categoryElement = document.getElementById('category');
            const questionElement = document.getElementById('question');
            const answersElement = document.getElementById('answers');
            const feedbackDiv = document.getElementById('feedback');

            // Update the initial timer display
            timerElement.textContent = '15';

            function updateGameState() {
                fetch('/api/puzzle/state')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error('Error:', data.error);
                            return;
                        }

                        // Update timer
                        currentTimeLeft = data.timeLeft;
                        timerElement.textContent = currentTimeLeft;
                        
                        // Update question if it's new
                        if (questionElement.textContent !== data.trivia.question) {
                            questionElement.textContent = data.trivia.question;
                            categoryElement.textContent = data.trivia.category;
                            
                            // Create answer buttons with updated styling
                            answersElement.innerHTML = data.trivia.answers.map((answer, index) => `
                                <button 
                                    onclick="submitAnswer(${index})" 
                                    class="w-full p-6 text-2xl text-left rounded transition-all duration-200 title
                                           bg-white/10 hover:bg-white/20">
                                    ${answer}
                                </button>
                            `).join('');
                        }

                        // Add warning color when time is low
                        if (currentTimeLeft <= 5) {
                            timerElement.classList.add('text-red-500');
                        } else {
                            timerElement.classList.remove('text-red-500');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            function submitAnswer(answerIndex) {
                if (currentTimeLeft === 0) return;

                // Disable all buttons
                const buttons = answersElement.getElementsByTagName('button');
                for (let button of buttons) {
                    button.disabled = true;
                }

                fetch('/api/puzzle/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        answer: answerIndex,
                        playerName: playerName
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Response data:', data); // For debugging
                    
                    // Get the clicked button
                    const clickedButton = buttons[answerIndex];
                    
                    if (data.correct) {
                        // Correct answer - show green checkmark
                        streak++;
                        streakElement.textContent = streak;
                        clickedButton.classList.add('bg-green-500', 'text-white');
                        clickedButton.innerHTML = `${clickedButton.textContent} ✓`;
                    } else {
                        // Wrong answer - show red X
                        streak = 0;
                        streakElement.textContent = streak;
                        clickedButton.classList.add('bg-red-500', 'text-white');
                        clickedButton.innerHTML = `${clickedButton.textContent} ✗`;

                        // Highlight the correct answer in green
                        const correctButton = buttons[data.correct_index];
                        correctButton.classList.add('bg-green-500', 'text-white');
                        correctButton.innerHTML = `${correctButton.textContent} ✓`;
                    }

                    // Update leaderboard if provided
                    if (data.leaderboard) {
                        updateLeaderboard(data.leaderboard);
                    }

                    // Wait 1 second then move to next question
                    setTimeout(() => {
                        updateGameState();
                    }, 1000);
                })
                .catch(error => console.error('Error:', error));
            }

            function updateLeaderboard(leaderboard) {
                const tbody = document.getElementById('leaderboardBody');
                if (!tbody) return;

                const leaderboardData = Array.isArray(leaderboard) ? leaderboard : [];
                
                tbody.innerHTML = leaderboardData.map((player, index) => `
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

            // Initial update
            updateGameState();

            // Update every second
            setInterval(updateGameState, 1000);
        </script>
    </body>
</html>
