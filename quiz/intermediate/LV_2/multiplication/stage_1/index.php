<?php
header("Content-Type: application/json");

// Function to generate a single question
function generateQuestion($number)
{
    $max_x = 99;
    $max_y = 9;
    $averge_length = 9;

    $variable_x = rand(1, $max_x);
    $variable_y = rand(1, $max_y);
    $operation = 'x';

    // Compute the equation and answer
    $equation = $variable_x . $operation . $variable_y;
    $answer = $variable_x * $variable_y;

    $steps = [
        "a" => $variable_x,
        "b" => $variable_y,
        "operation" => $operation,
    ];
    // Ensure the answer is within the valid range
    if ($answer < 0 || $answer > $averge_length) {
        return generateQuestion($number);
    }

    return [
        "number" => $number,
        "format" => $equation,
        "equation" => $steps,
        "answer" => $answer
    ];
}

// Function to generate multiple questions
function generateQuestions($count = 150)
{
    $questions = [];
    for ($i = 0; $i < $count; $i++) {
        $questions[] = generateQuestion($i + 1);
    }
    return $questions;
}

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $count = isset($_GET['count']) ? intval($_GET['count']) : 150;

    // Limit the count to a valid range
    if ($count < 1 || $count > 150) {
        $count = 150;
    }

    $questions = generateQuestions($count);
    echo json_encode([
        "level" => "Intermediate Level 2 Multiplication",
        "stage" => "1",
        "seconds_for_display" => 30,
        "seconds_for_answer" => 10,
        "status" => "success",
        "count" => count($questions),
        "questions" => $questions
    ]);
    exit;
} else {
    // Handle invalid request method
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method. Please use GET."
    ]);
    exit;
}
