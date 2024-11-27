<?php
header("Content-Type: application/json");

function generateQuestion($number)
{
    $variables = ['x', 'y', 'z', 'w', 'v', 's', 't', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm'];
    $terms = [];
    $operations = [];

    // Select random values for each variable
    foreach ($variables as $variable) {
        $terms[$variable] = rand(1, 4);
    }

    // Randomly assign addition or subtraction to each operation
    for ($i = 0; $i < 9; $i++) { // 9 operations for 10 terms
        $operations[] = rand(0, 1) === 1 ? '+' : '-';
    }

    $equation = $variables[0];
    $answer = $terms[$variables[0]];
    $steps = [
        [
            "variable" => $variables[0],
            "value" => $terms[$variables[0]],
            "operation" => "+"
        ]
    ];

    for ($i = 0; $i < count($operations); $i++) {
        $operation = $operations[$i];
        $currentVar = $variables[$i + 1];
        $equation .= " $operation $currentVar";

        if ($operation === '+') {
            $answer += $terms[$currentVar];
        } else {
            $answer -= $terms[$currentVar];
        }

        $steps[] = [
            "variable" => $currentVar,
            "value" => $terms[$currentVar],
            "operation" => $operation
        ];
    }

    // Ensure answer is within valid range
    if ($answer < 0 || $answer > 4) {
        return generateQuestion($number); // Recurse until a valid question is generated
    }

    return [
        "number" => $number,
        "equation" => $equation,
        "steps" => $steps,
        "answer" => $answer
    ];
}

function generateQuestions($count = 275)
{
    $questions = [];
    for ($i = 0; $i < $count; $i++) {
        $questions[] = generateQuestion($i + 1);
    }
    return $questions;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $count = isset($_GET['count']) ? intval($_GET['count']) : 275;

    if ($count < 1 || $count > 275) {
        $count = 275;
    }

    $questions = generateQuestions($count);
    echo json_encode([
        "level" => "Intermediate Small Friend Level",
        "stage" => "10",
        "seconds_for_display" => 8,
        "seconds_for_answer" => 5,
        "status" => "success",
        "count" => count($questions),
        "questions" => $questions
    ]);
    exit;
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method. Please use GET."
    ]);
    exit;
}
