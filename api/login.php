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
error_log("Login input received: " . $input);

$data = json_decode($input, true);

if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['error' => 'Username and password are required']);
    exit();
}

$username = $data['username'];
$password = $data['password'];

try {
    // First get the user by username only
    $query = "SELECT user_id, username, password, role FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Remove password from user array before sending
            unset($user['password']);
            
            // Get profile info if exists
            $profileQuery = "SELECT character_name, image_path 
                           FROM user_profile 
                           WHERE user_id = :user_id 
                           ORDER BY post_id DESC LIMIT 1";
            $profileStmt = $conn->prepare($profileQuery);
            $profileStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
            $profileStmt->execute();
            $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

            if ($profile) {
                $user['character_name'] = $profile['character_name'];
                $user['image_path'] = $profile['image_path'];
            }

            error_log("User found: " . json_encode($user));
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            error_log("Invalid password for username: " . $username);
            echo json_encode(['error' => 'Invalid username or password']);
        }
    } else {
        error_log("Username not found: " . $username);
        echo json_encode(['error' => 'Invalid username or password']);
    }
} catch (PDOException $e) {
    error_log("Login database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 