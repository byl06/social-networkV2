<?php
// api/profile/upload_avatar.php

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

// Debug - log la requête
error_log("Upload avatar - User ID: " . $_SESSION['user_id']);

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $error_msg = isset($_FILES['avatar']) ? "Erreur code: " . $_FILES['avatar']['error'] : "Aucun fichier";
    error_log($error_msg);
    jsonResponse(['error' => 'Aucun fichier ou erreur d\'upload: ' . $error_msg], 400);
}

$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES['avatar']['type'], $allowed)) {
    error_log("Type non autorisé: " . $_FILES['avatar']['type']);
    jsonResponse(['error' => 'Format non autorisé. Utilisez JPG, PNG, GIF ou WEBP'], 400);
}

if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
    error_log("Fichier trop gros: " . $_FILES['avatar']['size']);
    jsonResponse(['error' => 'Fichier trop volumineux (max 2MB)'], 400);
}

// Chemin absolu et relatif
$upload_dir_abs = dirname(__DIR__, 2) . '/uploads/profiles/';
$upload_dir_rel = 'uploads/profiles/';

error_log("Dossier upload: " . $upload_dir_abs);

if (!file_exists($upload_dir_abs)) {
    mkdir($upload_dir_abs, 0777, true);
    error_log("Dossier créé");
}

$extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
$target_path_abs = $upload_dir_abs . $filename;
$target_path_rel = $upload_dir_rel . $filename;

error_log("Fichier cible: " . $target_path_abs);

if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path_abs)) {
    error_log("Fichier déplacé avec succès");
    
    $stmt = $pdo->prepare("UPDATE users SET photo_profil = ? WHERE id_user = ?");
    $stmt->execute([$target_path_rel, $_SESSION['user_id']]);
    
    // Mettre à jour la session
    $_SESSION['user_photo'] = $target_path_rel;
    
    jsonResponse([
        'success' => true,
        'photo_url' => $target_path_rel,
        'message' => 'Photo mise à jour'
    ]);
} else {
    error_log("Erreur move_uploaded_file");
    jsonResponse(['error' => 'Erreur lors de la sauvegarde du fichier'], 500);
}
?>