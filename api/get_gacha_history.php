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

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'Missing user_id']);
    exit();
}

try {
    $query = "SELECT ugh.*, g.title as reward, g.character_name, g.image_path 
              FROM user_gacha_history ugh
              JOIN gacha g ON ugh.gacha_id = g.post_id
              WHERE ugh.user_id = :user_id 
              ORDER BY ugh.pulled_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);

} catch (Exception $e) {
    error_log("Get history error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?> 