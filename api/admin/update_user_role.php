<?php
// api/admin/update_user_role.php

require_once '../config.php';

// Activer les logs
error_log("=== update_user_role.php appelé ===");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

// Vérifier que l'utilisateur est admin
$checkRole = $pdo->prepare("SELECT r.nom_role FROM users u JOIN roles r ON u.id_role = r.id_role WHERE u.id_user = ?");
$checkRole->execute([$_SESSION['user_id']]);
$currentUser = $checkRole->fetch();

if (!$currentUser || $currentUser['nom_role'] !== 'Administrateur') {
    jsonResponse(['error' => 'Accès non autorisé - Administrateur requis'], 403);
}

$data = json_decode(file_get_contents('php://input'), true);
error_log("Données reçues: " . json_encode($data));

if (empty($data['user_id']) || empty($data['role'])) {
    jsonResponse(['error' => 'user_id et role requis'], 400);
}

// Récupérer l'id du rôle
$stmt = $pdo->prepare("SELECT id_role FROM roles WHERE nom_role = ?");
$stmt->execute([$data['role']]);
$role = $stmt->fetch();

if (!$role) {
    jsonResponse(['error' => 'Rôle invalide'], 400);
}

// Vérifier que l'utilisateur existe
$stmt = $pdo->prepare("SELECT id_user FROM users WHERE id_user = ?");
$stmt->execute([$data['user_id']]);
if (!$stmt->fetch()) {
    jsonResponse(['error' => 'Utilisateur introuvable'], 404);
}

// Ne pas modifier son propre rôle
if ($data['user_id'] == $_SESSION['user_id']) {
    jsonResponse(['error' => 'Vous ne pouvez pas modifier votre propre rôle'], 400);
}

$sql = "UPDATE users SET id_role = ? WHERE id_user = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$role['id_role'], $data['user_id']]);

error_log("Rôle mis à jour pour l'utilisateur " . $data['user_id'] . " vers " . $data['role']);

jsonResponse([
    'success' => true,
    'message' => 'Rôle mis à jour avec succès',
    'new_role' => $data['role']
]);
?>