<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

include 'connect.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['user_id']) || !isset($data['gacha_id'])) {
    echo json_encode(['error' => 'Missing required data']);
    exit();
}

try {
    // Get character info from gacha table
    $getCharacterQuery = "SELECT character_name, image_path FROM gacha WHERE post_id = :gacha_id";
    $charStmt = $conn->prepare($getCharacterQuery);
    $charStmt->bindParam(':gacha_id', $data['gacha_id'], PDO::PARAM_INT);
    $charStmt->execute();
    $character = $charStmt->fetch(PDO::FETCH_ASSOC);

    if (!$character) {
        throw new Exception('Character not found');
    }

    // Check if user_profile exists
    $checkQuery = "SELECT * FROM user_profile WHERE user_id = :user_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        // Update existing profile
        $updateQuery = "UPDATE user_profile 
                       SET character_name = :character_name,
                           image_path = :image_path
                       WHERE user_id = :user_id";
    } else {
        // Insert new profile
        $updateQuery = "INSERT INTO user_profile (user_id, character_name, image_path) 
                       VALUES (:user_id, :character_name, :image_path)";
    }
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
    $updateStmt->bindParam(':character_name', $character['character_name'], PDO::PARAM_STR);
    $updateStmt->bindParam(':image_path', $character['image_path'], PDO::PARAM_STR);
    $updateStmt->execute();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Update profile error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?> 