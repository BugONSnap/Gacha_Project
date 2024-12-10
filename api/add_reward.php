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

if (!isset($data['gacha_id']) || !isset($data['title']) || !isset($data['character_name']) || 
    !isset($data['description']) || !isset($data['image_path'])) {
    echo json_encode(['error' => 'Required data is missing']);
    exit();
}

try {
    $query = "INSERT INTO gacha_rewards (gacha_id, title, character_name, description, image_path) 
              VALUES (:gacha_id, :title, :character_name, :description, :image_path)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':gacha_id', $data['gacha_id'], PDO::PARAM_INT);
    $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
    $stmt->bindParam(':character_name', $data['character_name'], PDO::PARAM_STR);
    $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
    $stmt->bindParam(':image_path', $data['image_path'], PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reward added successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add reward']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 