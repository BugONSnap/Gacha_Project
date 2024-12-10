<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if (!isset($_FILES['image'])) {
    echo json_encode(['error' => 'No image file uploaded']);
    exit();
}

$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['image'];
$fileName = uniqid() . '_' . basename($file['name']);
$targetPath = $uploadDir . $fileName;

// Check if it's actually an image
$check = getimagesize($file['tmp_name']);
if ($check === false) {
    echo json_encode(['error' => 'File is not an image']);
    exit();
}

// Check file size (5MB max)
if ($file['size'] > 5000000) {
    echo json_encode(['error' => 'File is too large']);
    exit();
}

// Allow certain file formats
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
$fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['error' => 'Only JPG, JPEG, PNG & GIF files are allowed']);
    exit();
}

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode([
        'success' => true,
        'image_path' => 'uploads/' . $fileName
    ]);
} else {
    echo json_encode(['error' => 'Failed to upload file']);
}
?> 