<?php
// api/profile/update_password.php - Changer le mot de passe

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

if (empty($data['current_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
    jsonResponse(['error' => 'Tous les champs sont requis'], 400);
}

if ($data['new_password'] !== $data['confirm_password']) {
    jsonResponse(['error' => 'Les nouveaux mots de passe ne correspondent pas'], 400);
}

if (strlen($data['new_password']) < 6) {
    jsonResponse(['error' => 'Le mot de passe doit contenir au moins 6 caractères'], 400);
}

// Vérifier l'ancien mot de passe
$stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!password_verify($data['current_password'], $user['mot_de_passe'])) {
    jsonResponse(['error' => 'Mot de passe actuel incorrect'], 401);
}

// Mettre à jour le mot de passe
$new_password = password_hash($data['new_password'], PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id_user = ?");
$stmt->execute([$new_password, $user_id]);

jsonResponse([
    'success' => true,
    'message' => 'Mot de passe mis à jour'
]);
?>