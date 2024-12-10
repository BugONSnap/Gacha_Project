<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

include 'connect.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if (!$user_id) {
    echo json_encode(['error' => 'User ID is required']);
    exit();
}

try {
    $query = "SELECT 
                ugh.history_id,
                ugh.gacha_id,
                ugh.pulled_at,
                g.title as gacha_title,
                gr.title as reward_title,
                gr.image_path as reward_image,
                gr.character_name
              FROM user_gacha_history ugh
              JOIN gacha g ON ugh.gacha_id = g.post_id
              JOIN gacha_rewards gr ON g.post_id = gr.gacha_id
              WHERE ugh.user_id = :user_id
              ORDER BY ugh.pulled_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($history);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 