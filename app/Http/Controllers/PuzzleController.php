<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\ActivePlayer;
use OpenAI\Laravel\Facades\OpenAI;

class PuzzleController extends Controller
{
    private function generateNewPuzzle()
    {
        Log::info('Generating new 9x9 Sudoku puzzle');
        
        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a 9x9 Sudoku puzzle generator. Generate puzzles where:
                    - Each row must contain digits 1-9
                    - Each column must contain digits 1-9
                    - Each 3x3 box must contain digits 1-9
                    - Puzzle must have a unique solution
                    - Include at least three numbers in each 3x3 box
                    - Include 30-35 starting numbers total
                    - Ensure the puzzle is solvable with basic Sudoku strategies'
                ],
                [
                    'role' => 'user',
                    'content' => 'Generate a 9x9 Sudoku puzzle. Respond ONLY with JSON in this format: 
                    {
                        "puzzle": [
                            [5,3,0,0,7,0,0,0,0],
                            [6,0,0,1,9,5,0,0,0],
                            [0,9,8,0,0,0,0,6,0],
                            [8,0,0,0,6,0,0,0,3],
                            [4,0,0,8,0,3,0,0,1],
                            [7,0,0,0,2,0,0,0,6],
                            [0,6,0,0,0,0,2,8,0],
                            [0,0,0,4,1,9,0,0,5],
                            [0,0,0,0,8,0,0,7,9]
                        ],
                        "solution": [
                            [5,3,4,6,7,8,9,1,2],
                            [6,7,2,1,9,5,3,4,8],
                            [1,9,8,3,4,2,5,6,7],
                            [8,5,9,7,6,1,4,2,3],
                            [4,2,6,8,5,3,7,9,1],
                            [7,1,3,9,2,4,8,5,6],
                            [9,6,1,5,3,7,2,8,4],
                            [2,8,7,4,1,9,6,3,5],
                            [3,4,5,2,8,6,1,7,9]
                        ]
                    }
                    Use 0 for empty cells in the puzzle.'
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ]);

        try {
            $content = $result->choices[0]->message->content;
            $data = json_decode($content, true);
            
            if (!$data || !isset($data['puzzle']) || !isset($data['solution'])) {
                throw new \Exception('Invalid puzzle format received');
            }
            
            // Validate that each 3x3 box has at least three numbers
            for ($boxRow = 0; $boxRow < 3; $boxRow++) {
                for ($boxCol = 0; $boxCol < 3; $boxCol++) {
                    $count = 0;
                    for ($i = 0; $i < 3; $i++) {
                        for ($j = 0; $j < 3; $j++) {
                            if ($data['puzzle'][$boxRow * 3 + $i][$boxCol * 3 + $j] !== 0) {
                                $count++;
                            }
                        }
                    }
                    if ($count < 3) {
                        throw new \Exception('Invalid puzzle: not enough numbers in 3x3 box');
                    }
                }
            }
            
            // Count total starting numbers
            $startingNumbers = 0;
            for ($i = 0; $i < 9; $i++) {
                for ($j = 0; $j < 9; $j++) {
                    if ($data['puzzle'][$i][$j] !== 0) {
                        $startingNumbers++;
                    }
                }
            }
            
            if ($startingNumbers < 30) {
                throw new \Exception('Invalid puzzle: not enough starting numbers');
            }
            
            return $data;
        } catch (\Exception $e) {
            Log::error('Error processing OpenAI response:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getGameState()
    {
        try {
            $roundEndsAt = Cache::get('round_ends_at');
            $puzzle = Cache::get('current_puzzle');
            
            if (!$roundEndsAt || !$puzzle || now()->gt($roundEndsAt)) {
                $puzzle = $this->generateNewPuzzle();
                $roundEndsAt = now()->addMinutes(10);
                
                Cache::put('current_puzzle', $puzzle, $roundEndsAt);
                Cache::put('round_ends_at', $roundEndsAt, $roundEndsAt);
            }

            $timeLeft = max(0, now()->diffInSeconds($roundEndsAt));
            
            $leaderboard = ActivePlayer::where('streak', '>', 0)
                ->orderByDesc('streak')
                ->take(10)
                ->get(['name', 'streak']);

            return response()->json([
                'puzzle' => $puzzle['puzzle'],
                'timeLeft' => $timeLeft,
                'leaderboard' => $leaderboard
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getGameState: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkSolution(Request $request)
    {
        try {
            Log::info('Received solution check request', [
                'request_data' => $request->all()
            ]);

            $puzzle = Cache::get('current_puzzle');
            if (!$puzzle) {
                Log::error('No puzzle found in cache');
                throw new \Exception('No active puzzle found');
            }

            Log::info('Current puzzle from cache', [
                'puzzle' => $puzzle
            ]);

            $submittedSolution = $request->solution;
            
            if (!is_array($submittedSolution)) {
                Log::error('Invalid solution format', [
                    'submitted' => $submittedSolution
                ]);
                throw new \Exception('Invalid solution format');
            }
            
            // Debug logging
            Log::info('Comparing solutions:', [
                'submitted' => $submittedSolution,
                'correct' => $puzzle['solution']
            ]);
            
            // Compare solutions
            $correct = $this->compareSolutions($submittedSolution, $puzzle['solution']);

            Log::info('Solution comparison result:', [
                'correct' => $correct
            ]);

            $playerName = $request->playerName;
            if (!$playerName) {
                throw new \Exception('Player name is required');
            }

            // Update player streak
            $player = ActivePlayer::firstOrCreate(
                ['name' => $playerName],
                ['streak' => 0]
            );

            if ($correct) {
                $player->increment('streak');
                Log::info('Player streak incremented', [
                    'player' => $playerName,
                    'new_streak' => $player->streak
                ]);
            } else {
                $player->update(['streak' => 0]);
                Log::info('Player streak reset', [
                    'player' => $playerName
                ]);
            }

            // Get updated leaderboard
            $leaderboard = ActivePlayer::where('streak', '>', 0)
                ->orderByDesc('streak')
                ->take(10)
                ->get(['name', 'streak']);

            return response()->json([
                'correct' => $correct,
                'leaderboard' => $leaderboard
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking solution: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => $e->getMessage(),
                'details' => 'Check server logs for more information'
            ], 500);
        }
    }

    private function compareSolutions($submitted, $correct)
    {
        Log::info('Comparing solutions in detail:', [
            'submitted' => $submitted,
            'correct' => $correct
        ]);

        if (!is_array($submitted) || !is_array($correct)) {
            Log::error('Invalid array format in comparison');
            return false;
        }

        for ($i = 0; $i < 9; $i++) {
            for ($j = 0; $j < 9; $j++) {
                if (!isset($submitted[$i][$j]) || !isset($correct[$i][$j])) {
                    Log::error("Missing value at position [$i][$j]");
                    return false;
                }
                if ($submitted[$i][$j] != $correct[$i][$j]) {
                    Log::info("Mismatch at position [$i][$j]: submitted={$submitted[$i][$j]}, correct={$correct[$i][$j]}");
                    return false;
                }
            }
        }

        return true;
    }
}
