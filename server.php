<?php

require_once './vendor/autoload.php';

use Workerman\Worker;

// Quiz data storage
$leaderboard = [];
$quizStatus = 'waiting'; // Possible states: 'waiting', 'ongoing', 'completed'
$questions = []; // Array of questions
$currentQuestionIndex = 0;
$responses = []; // Track responses per question
$currentQuizId = null;

// Function to fetch questions dynamically
function getQuestions()
{
    return [
        [
            'text' => 'What is the capital of France?',
            'options' => ['Berlin', 'Madrid', 'Paris', 'Rome'],
            'correctOption' => 2
        ],
        [
            'text' => 'Which planet is known as the Red Planet?',
            'options' => ['Earth', 'Mars', 'Jupiter', 'Venus'],
            'correctOption' => 1
        ],
        [
            'text' => 'Who wrote "Romeo and Juliet"?',
            'options' => ['Shakespeare', 'Dickens', 'Hemingway', 'Austen'],
            'correctOption' => 0
        ],
        [
            'text' => 'How many continents are there?', 
            'options' => ['5', '6', '7', '8'], 
            'correctOption' => 2
        ],
        [
            'text' => 'What is the largest ocean on Earth?', 
            'options' => ['Atlantic Ocean', 'Indian Ocean', 'Arctic Ocean', 'Pacific Ocean'], 
            'correctOption' => 3
        ],
        [
            'text' => 'Which animal is known as the King of the Jungle?', 
            'options' => ['Tiger', 'Elephant', 'Lion', 'Giraffe'], 
            'correctOption' => 2
        ],
    ];
}

// Initialize Workerman WebSocket server
$ws_worker = new Worker('websocket://0.0.0.0:9502'); // WebSocket server on port 9502
$ws_worker->count = 1;

// WebSocket connection handler
$ws_worker->onConnect = function ($connection) {
    echo "New connection established\n";
};

// WebSocket message handler
$ws_worker->onMessage = function ($connection, $data) use (&$leaderboard, &$quizStatus, &$questions, &$currentQuestionIndex, &$responses, &$currentQuizId) {
    $message = json_decode($data, true);

    switch ($message['action']) {
        case 'joinQuiz':
            $userId = $message['userId'];
            $quizId = $message['quizId'];

            // Reset quiz state if a new quizId is provided
            if ($quizId !== $currentQuizId) {
                $currentQuizId = $quizId;
                resetQuizState();
            }

            // Initialize score if the user is new
            if (!isset($leaderboard[$userId])) {
                $leaderboard[$userId] = 0;
            }

            // Send the current question or final leaderboard if quiz completed
            if ($quizStatus === 'completed') {
                $connection->send(json_encode([
                    'action' => 'quizCompleted',
                    'leaderboard' => formatLeaderboard()
                ]));
            } else {
                $connection->send(json_encode([
                    'action' => 'joinConfirmation',
                    'quizId' => $quizId,
                    'userId' => $userId,
                    'quizStatus' => $quizStatus,
                    'question' => $questions[$currentQuestionIndex]
                ]));
            }

            // Broadcast the leaderboard to all connections
            broadcastLeaderboard();
            break;

        case 'submitAnswer':
            $userId = $message['userId'];
            $isCorrect = $message['isCorrect'];

            // Update leaderboard if the answer is correct
            if ($isCorrect) {
                $leaderboard[$userId] += 10;
            }

            // Track the user's response for the current question
            $responses[$userId] = true;

            // Check if all users have answered the current question
            if (count($responses) >= count($leaderboard)) {
                moveToNextQuestionOrEnd();
            }

            // Broadcast leaderboard updates
            broadcastLeaderboard();
            break;

        default:
            $connection->send(json_encode(['error' => 'Unknown action']));
            break;
    }
};

// Function to broadcast leaderboard to all clients
function broadcastLeaderboard()
{
    global $ws_worker, $leaderboard;

    $leaderboardData = formatLeaderboard();

    foreach ($ws_worker->connections as $client) {
        $client->send(json_encode([
            'action' => 'updateLeaderboard',
            'leaderboard' => $leaderboardData
        ]));
    }
}

// Function to format leaderboard
function formatLeaderboard()
{
    global $leaderboard;
    arsort($leaderboard);
    $leaderboardData = [];

    foreach ($leaderboard as $userId => $score) {
        $leaderboardData[] = ['userId' => $userId, 'score' => $score];
    }

    return $leaderboardData;
}

// Function to move to the next question or end the quiz
function moveToNextQuestionOrEnd()
{
    global $ws_worker, $questions, $currentQuestionIndex, $quizStatus, $responses;

    $currentQuestionIndex++;
    $responses = []; // Reset responses for the next question

    if ($currentQuestionIndex >= count($questions)) {
        $quizStatus = 'completed';
        broadcastCompletion();
    } else {
        // Send the next question to all clients
        foreach ($ws_worker->connections as $client) {
            $client->send(json_encode([
                'action' => 'nextQuestion',
                'question' => $questions[$currentQuestionIndex]
            ]));
        }
    }
}

// Function to broadcast quiz completion
function broadcastCompletion()
{
    global $ws_worker, $leaderboard;

    $leaderboardData = formatLeaderboard();

    foreach ($ws_worker->connections as $client) {
        $client->send(json_encode([
            'action' => 'quizCompleted',
            'leaderboard' => $leaderboardData
        ]));
    }
}

// Function to reset quiz state
function resetQuizState()
{
    global $leaderboard, $quizStatus, $questions, $currentQuestionIndex, $responses;

    $leaderboard = [];
    $quizStatus = 'ongoing';
    $questions = getQuestions();
    $currentQuestionIndex = 0;
    $responses = [];
}

// Start the WebSocket server
Worker::runAll();
