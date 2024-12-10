<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

include 'connect.php';

try {
    // Get all gacha posts with user information
    $query = "SELECT g.*, u.username 
             FROM gacha g 
             JOIN users u ON g.user_id = u.user_id 
             ORDER BY g.post_id DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($posts) {
        error_log("Found " . count($posts) . " gacha posts");
        echo json_encode($posts);
    } else {
        error_log("No gacha posts found");
        echo json_encode([]);
    }
} catch (PDOException $e) {
    error_log("Database error in get.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
