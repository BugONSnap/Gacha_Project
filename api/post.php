<?php

// CORS headers to allow requests from your frontend (ensure frontend matches this domain)
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

// Handle OPTIONS requests to enable pre-flight CORS checksy
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Include database connection file
include 'connect.php';

// Get the raw input data
$input = file_get_contents('php://input');

// Log raw input for debugging
file_put_contents('php://stderr', "Raw input: " . $input . "\n");

// Decode the JSON input
$data = json_decode($input, true);

// Check if required data is present
if (!isset($data['user_id']) || !isset($data['title']) || !isset($data['description']) || 
    !isset($data['character_name']) || !isset($data['image_path'])) {
    echo json_encode(['error' => 'Required data is missing']);
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Insert into gacha table
    $gachaQuery = "INSERT INTO gacha (user_id, title, description, character_name, image_path) 
                   VALUES (:user_id, :title, :description, :character_name, :image_path)";
    $gachaStmt = $conn->prepare($gachaQuery);
    $gachaStmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
    $gachaStmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
    $gachaStmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
    $gachaStmt->bindParam(':character_name', $data['character_name'], PDO::PARAM_STR);
    $gachaStmt->bindParam(':image_path', $data['image_path'], PDO::PARAM_STR);
    $gachaStmt->execute();

    // Get the inserted gacha ID
    $gachaId = $conn->lastInsertId();

    // Create initial gacha reward
    $rewardQuery = "INSERT INTO gacha_rewards (gacha_id, title, character_name, description, image_path) 
                    VALUES (:gacha_id, :title, :character_name, :description, :image_path)";
    $rewardStmt = $conn->prepare($rewardQuery);
    $rewardStmt->bindParam(':gacha_id', $gachaId, PDO::PARAM_INT);
    $rewardStmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
    $rewardStmt->bindParam(':character_name', $data['character_name'], PDO::PARAM_STR);
    $rewardStmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
    $rewardStmt->bindParam(':image_path', $data['image_path'], PDO::PARAM_STR);
    $rewardStmt->execute();

    // Create user profile entry if it doesn't exist
    $profileQuery = "INSERT INTO user_profile (user_id, post_id, character_name, image_path) 
                     VALUES (:user_id, :post_id, :character_name, :image_path)
                     ON DUPLICATE KEY UPDATE 
                     character_name = :character_name_update,
                     image_path = :image_path_update";
    $profileStmt = $conn->prepare($profileQuery);
    $profileStmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
    $profileStmt->bindParam(':post_id', $gachaId, PDO::PARAM_INT);
    $profileStmt->bindParam(':character_name', $data['character_name'], PDO::PARAM_STR);
    $profileStmt->bindParam(':image_path', $data['image_path'], PDO::PARAM_STR);
    $profileStmt->bindParam(':character_name_update', $data['character_name'], PDO::PARAM_STR);
    $profileStmt->bindParam(':image_path_update', $data['image_path'], PDO::PARAM_STR);
    $profileStmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Gacha post created successfully!',
        'gacha_id' => $gachaId
    ]);

} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
