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
    $query = "SELECT username FROM user_profile WHERE user_id = :user_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $username = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'username' => $username
    ]);

} catch (Exception $e) {
    error_log("Get profile error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?> 