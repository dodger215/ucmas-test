<?php
header("Content-Type: application/json");

function generateQuestion($number)
{
    $variables = ['x', 'y', 'z', 'w', 'v'];
    $terms = [];
    $operations = [];

    foreach ($variables as $variable) {
        $terms[$variable] = rand(1, 99);
    }

    for ($i = 0; $i < 4; $i++) {
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

    if ($answer < 0 || $answer > 99) {
        return generateQuestion($number);
    }

    return [
        "number" => $number,
        "equation" => $equation,
        "steps" => $steps,
        "answer" => $answer
    ];
}

function generateQuestions($count = 200)
{
    $questions = [];
    for ($i = 0; $i < $count; $i++) {
        $questions[] = generateQuestion($i + 1);
    }
    return $questions;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $count = isset($_GET['count']) ? intval($_GET['count']) : 200;

    if ($count < 1 || $count > 200) {
        $count = 200;
    }

    $questions = generateQuestions($count);
    echo json_encode([
        "level" => "Beginner Level Three",
        "stage" => "7",
        "seconds_for_display" => 6,
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
