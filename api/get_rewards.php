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

$gacha_id = isset($_GET['gacha_id']) ? (int)$_GET['gacha_id'] : null;

if (!$gacha_id) {
    echo json_encode(['error' => 'Gacha ID is required']);
    exit();
}

try {
    $query = "SELECT * FROM gacha_rewards WHERE gacha_id = :gacha_id ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':gacha_id', $gacha_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rewards);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 