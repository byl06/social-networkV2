<?php
// api/profile/update_profile.php - Mettre à jour le profil

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

$sql = "UPDATE users SET 
        nom = COALESCE(?, nom),
        prenom = COALESCE(?, prenom),
        email = COALESCE(?, email),
        bio = COALESCE(?, bio)
        WHERE id_user = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $data['nom'] ?? null,
    $data['prenom'] ?? null,
    $data['email'] ?? null,
    $data['bio'] ?? null,
    $user_id
]);

// Mettre à jour la session
$_SESSION['user_nom'] = $data['nom'] ?? $_SESSION['user_nom'];
$_SESSION['user_prenom'] = $data['prenom'] ?? $_SESSION['user_prenom'];
$_SESSION['user_email'] = $data['email'] ?? $_SESSION['user_email'];

jsonResponse([
    'success' => true,
    'message' => 'Profil mis à jour'
]);
?>