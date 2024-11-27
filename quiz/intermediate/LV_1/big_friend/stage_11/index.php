<?php
header("Content-Type: application/json");

function generateQuestion($number)
{
    $variables = ['x', 'y', 'z', 'w', 'v', 's', 't', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm'];
    $terms = [];
    $operations = [];

    foreach ($variables as $variable) {
        $terms[$variable] = rand(1, 9);
    }

    for ($i = 0; $i < 9; $i++) {
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

    if ($answer < 0 || $answer > 9) {
        return generateQuestion($number);
    }

    return [
        "number" => $number,
        "equation" => $equation,
        "steps" => $steps,
        "answer" => $answer
    ];
}

function generateQuestions($count = 300)
{
    $questions = [];
    for ($i = 0; $i < $count; $i++) {
        $questions[] = generateQuestion($i + 1);
    }
    return $questions;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $count = isset($_GET['count']) ? intval($_GET['count']) : 300;

    if ($count < 1 || $count > 300) {
        $count = 300;
    }

    $questions = generateQuestions($count);
    echo json_encode([
        "level" => "Intermediate Big Friend Level",
        "stage" => "11",
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

