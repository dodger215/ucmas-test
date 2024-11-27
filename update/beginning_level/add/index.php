<?php
header("Content-Type: application/json");

// Function to read the existing data from the file
function readUserDataFromFile($fileName)
{
    if (file_exists($fileName)) {
        $jsonData = file_get_contents($fileName);
        return json_decode($jsonData, true);
    }
    return null;
}

// Function to save the updated data to the file
function saveUserDataToFile($fileName, $data)
{
    return file_put_contents($fileName, json_encode($data, JSON_PRETTY_PRINT));
}

// Function to calculate the total score for a level
function calculateTotalScore($levelData)
{
    $totalScore = 0;
    foreach ($levelData as $stage) {
        if (isset($stage['data']['score']) && is_numeric($stage['data']['score'])) {
            $totalScore += $stage['data']['score'];
        }
    }
    return $totalScore;
}

// Handle API request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw POST data
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid JSON input."
        ]);
        exit;
    }

    if (!isset($inputData['id']) || !isset($inputData['level']) || !isset($inputData['stage']) || !isset($inputData['score'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields (id, level, stage, score)."
        ]);
        exit;
    }

    // Read the existing data from the file
    $fileName = '../storage/users_beginner_data.json';
    $existingData = readUserDataFromFile($fileName);

    if (!$existingData) {
        echo json_encode([
            "status" => "error",
            "message" => "No existing data found."
        ]);
        exit;
    }

    // Find the user in the existing data
    $userFound = false;
    foreach ($existingData['users'] as &$user) {
        if ($user['id'] === $inputData['id']) {
            $userFound = true;

            // Check if the level and stage exist in the user data
            if (isset($user['levels'][$inputData['level']])) {
                $levelData = &$user['levels'][$inputData['level']];
                $stageNumber = intval(str_replace('Stage ', '', $inputData['stage'])) - 1;

                // Check if the stage exists
                if (isset($levelData[$stageNumber])) {
                    $levelData[$stageNumber]['data']['score'] = $inputData['score'];
                    $levelData[$stageNumber]['data']['time_uploaded'] = date("Y-m-d H:i:s"); // Update the time
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Stage not found."
                    ]);
                    exit;
                }

                // Calculate the total score for the level
                $totalScore = calculateTotalScore($levelData);
                $user['levels'][$inputData['level']]['total_score'] = $totalScore;
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Level not found."
                ]);
                exit;
            }

            break;
        }
    }

    // If the user is not found
    if (!$userFound) {
        echo json_encode([
            "status" => "error",
            "message" => "User not found."
        ]);
        exit;
    }

    // Save the updated data back to the file
    if (saveUserDataToFile($fileName, $existingData)) {
        echo json_encode([
            "status" => "success",
            "message" => "Score updated successfully.",
            "user" => $user
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to save updated data to the file."
        ]);
    }
    exit;
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method. Please use POST."
    ]);
    exit;
}
