<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\ActivePlayer;
use Cache;
use Log;

class PuzzleController extends Controller
{
    private function generateNewTrivia()
    {
        Log::info('Generating new trivia question from OpenAI');
        
        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert trivia question generator. Generate short,challenging questions from a wide variety of subjects including:
                    - Advanced Science (quantum physics, biochemistry, astronomy)
                    - World History (ancient civilizations, revolutions, obscure events)
                    - Art & Architecture (movements, techniques, famous works)
                    - Literature & Philosophy (classic works, authors, philosophical concepts)
                    - Technology & Computing (programming, AI, innovations)
                    - Geography & Geology (lesser-known locations, geological phenomena)
                    - Music Theory & History (classical, jazz, world music)
                    - Mathematics & Logic (theorems, famous problems)
                    - Sports & Olympics (records, historic moments, rules)
                    - Cinema & Television (directors, techniques, cult classics)
                    - Biology & Medicine (rare diseases, scientific discoveries)
                    - Chemistry & Materials (compounds, reactions, properties)
                    - Space Exploration (missions, discoveries, technology)
                    - Economics & Finance (concepts, history, terminology)
                    - Language & Linguistics (etymology, grammar, foreign phrases)
                    - Mythology & Religion (world religions, ancient myths)
                    - Politics & Law (systems, landmark cases, international relations)
                    - Environmental Science (ecosystems, climate, conservation)
                    - Psychology & Neuroscience (theories, studies, brain function)
                    - Food & Cuisine (techniques, world cuisine, ingredients)

                    Make questions challenging but not impossible. Target knowledgeable adults.
                    Questions should require critical thinking and specific knowledge.
                    Avoid obvious or common knowledge questions.'
                ],
                [
                    'role' => 'user',
                    'content' => 'Generate a challenging trivia question. Respond ONLY with JSON in this format: 
                    {
                        "question": "What is the question?",
                        "answers": ["correct answer", "wrong answer 1", "wrong answer 2", "wrong answer 3"],
                        "correct_index": 0,
                        "category": "category name"
                    }'
                ]
            ],
            'temperature' => 1.0,  // Maximum randomness
            'presence_penalty' => 1.0,  // Maximum penalty for repeated content
            'frequency_penalty' => 1.0  // Maximum penalty for repeated content
        ]);

        $content = $result->choices[0]->message->content;
        $data = json_decode($content, true);
        
        Log::info('Received question from OpenAI:', ['question' => $data['question']]);

        // Shuffle answers but remember the correct one
        $correctAnswer = $data['answers'][$data['correct_index']];
        shuffle($data['answers']);
        $newCorrectIndex = array_search($correctAnswer, $data['answers']);

        return [
            'question' => $data['question'],
            'answers' => $data['answers'],
            'correct_index' => $newCorrectIndex,
            'category' => $data['category']
        ];
    }

    public function getGameState()
    {
        try {
            $roundEndsAt = Cache::get('round_ends_at');
            $trivia = Cache::get('current_trivia');
            
            // Check if we need a new question
            $needsNewQuestion = false;
            
            if (!$roundEndsAt || !$trivia) {
                $needsNewQuestion = true;
            } else {
                // Convert to timestamp for reliable comparison
                $roundEndsAtTime = $roundEndsAt->timestamp;
                $currentTime = now()->timestamp;
                
                if ($currentTime > $roundEndsAtTime) {
                    $needsNewQuestion = true;
                }
            }
            
            // Only generate new question if needed
            if ($needsNewQuestion) {
                Log::info('Generating new question at: ' . now());
                $trivia = $this->generateNewTrivia();
                $roundEndsAt = now()->addSeconds(10);
                
                Cache::put('current_trivia', $trivia, $roundEndsAt);
                Cache::put('round_ends_at', $roundEndsAt, $roundEndsAt);
            }

            $timeLeft = max(0, now()->diffInSeconds($roundEndsAt));
            
            // Get current leaderboard
            $leaderboard = ActivePlayer::where('streak', '>', 0)
                ->orderByDesc('streak')
                ->take(10)
                ->get(['name', 'streak']);

            return response()->json([
                'trivia' => [
                    'question' => $trivia['question'],
                    'answers' => $trivia['answers'],
                    'category' => $trivia['category'],
                    'correct_index' => $timeLeft === 0 ? $trivia['correct_index'] : null
                ],
                'timeLeft' => $timeLeft,
                'leaderboard' => $leaderboard
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getGameState: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkAnswer(Request $request)
    {
        try {
            $trivia = Cache::get('current_trivia');
            if (!$trivia) {
                throw new \Exception('No active trivia found');
            }

            $submittedAnswer = (int) $request->answer;
            $correctAnswer = (int) $trivia['correct_index'];
            $playerName = $request->playerName;
            
            $correct = $submittedAnswer === $correctAnswer;

            // Update player streak
            $player = ActivePlayer::firstOrCreate(
                ['name' => $playerName],
                ['streak' => 0]
            );

            if ($correct) {
                $player->increment('streak');
            } else {
                $player->update(['streak' => 0]);
            }

            // Get updated leaderboard
            $leaderboard = ActivePlayer::where('streak', '>', 0)
                ->orderByDesc('streak')
                ->take(10)
                ->get(['name', 'streak']);

            return response()->json([
                'correct' => $correct,
                'correct_index' => $trivia['correct_index'],
                'correct_answer' => $trivia['answers'][$trivia['correct_index']],
                'leaderboard' => $leaderboard
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking answer: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
