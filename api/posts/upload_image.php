<?php
// api/posts/upload_image.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

// Vérifier qu'un fichier a été uploadé
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Aucun fichier ou erreur d\'upload']);
    exit();
}

// Vérifier le type
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES['image']['type'], $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format non autorisé. Utilisez JPG, PNG, GIF ou WEBP']);
    exit();
}

// Vérifier la taille (max 5MB)
if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'Fichier trop volumineux. Maximum 5MB']);
    exit();
}

// Créer le dossier si besoin
$upload_dir = dirname(__DIR__, 2) . '/uploads/posts/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = 'post_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
$target_path = $upload_dir . $filename;

if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
    $db_path = 'uploads/posts/' . $filename;
    echo json_encode([
        'success' => true,
        'image_url' => $db_path,
        'message' => 'Image uploadée'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de l\'upload']);
}
?>