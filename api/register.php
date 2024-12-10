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

$input = file_get_contents('php://input');
// Add debug logging
error_log("Received input: " . $input);

$data = json_decode($input, true);

if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['error' => 'Username and password are required']);
    exit();
}

$username = $data['username'];
$password = $data['password'];
// Hash the password before storing
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = isset($data['role']) ? (int)$data['role'] : 0;
if ($role !== 0 && $role !== 1) {
    $role = 0; // Default to regular user if invalid role
}

try {
    // Check if username already exists
    $checkQuery = "SELECT user_id FROM users WHERE username = :username";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':username', $username, PDO::PARAM_STR);
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        echo json_encode(['error' => 'Username already exists']);
        exit();
    }

    // Insert new user
    $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':role', $role, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        echo json_encode(['error' => 'Registration failed']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 