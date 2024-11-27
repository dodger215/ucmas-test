<?php
header("Content-Type: application/json");

// Function to generate random upload time (in seconds) within the last 30 days
function generateRandomTime()
{
    return date("Y-m-d H:i:s", rand(strtotime('-30 days'), time()));
}

// Function to generate stages for a level with a score and upload time
function generateStages($scores)
{
    $stages = [];
    for ($i = 1; $i <= 12; $i++) {
        // Only update Stage 1 with the score, leave others as null
        $score = ($i === 1 && isset($scores[0])) ? $scores[0] : 0;
        
        $stages[] = [
            "stage" => "Stage $i",
            "data" => [
                "score" => $score,  // Use the score for Stage 1 or null for other stages
                "time_uploaded" => generateRandomTime()
            ]
        ];
    }
    return $stages;
}

// Function to create the structure of user data with levels and stages
function createUserData($userData)
{
    // Levels for different classes
    $classLevels = [
        "advanced" => ["level 1"],
    ];

    // Determine class from the user data (if not provided, set default as advanced)
    $userClass = isset($userData['class']) ? $userData['class'] : 'advanced';

    // Create levels based on user class
    $userLevels = [];
    foreach ($classLevels[$userClass] as $level) {
        // Check if scores are provided for the level, otherwise, pass an empty array
        $scores = isset($userData['scores'][$level]) ? $userData['scores'][$level] : [];
        $userLevels[$level] = generateStages($scores);
    }

    // Add the user data along with their levels and stages
    return [
        "id" => $userData['id'],
        "name" => $userData['name'],
        "class" => $userClass,
        "levels" => $userLevels
    ];
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

    // Ensure required fields are present
    if (!isset($inputData['id']) || !isset($inputData['name']) || !isset($inputData['scores'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields (id, name, scores)."
        ]);
        exit;
    }

    // Create the user data structure with levels and stages
    $userData = createUserData($inputData);

    // Read the existing data from the file (if any)
    $fileName = '../storage/users_advanced_data.json';
    if (file_exists($fileName)) {
        $jsonData = file_get_contents($fileName);
        $existingData = json_decode($jsonData, true);
    } else {
        $existingData = [
            "status" => "success",
            "users" => []
        ];
    }

    // Add the new user to the existing data
    $existingData['users'][] = $userData;

    // Save the updated data back to the JSON file
    if (file_put_contents($fileName, json_encode($existingData, JSON_PRETTY_PRINT))) {
        echo json_encode([
            "status" => "success",
            "message" => "User data saved successfully.",
            "user" => $userData
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to save user data to the file."
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
