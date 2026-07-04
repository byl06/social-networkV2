<?php
// api/admin/check_admin.php

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT u.*, r.nom_role as role 
        FROM users u
        LEFT JOIN roles r ON u.id_role = r.id_role
        WHERE u.id_user = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse(['error' => 'Utilisateur non trouvé'], 404);
}

$isAdmin = ($user['role'] === 'Administrateur');
$isModo = ($user['role'] === 'Modérateur');

if (!$isAdmin && !$isModo) {
    jsonResponse(['error' => 'Accès non autorisé - Vous devez être administrateur ou modérateur'], 403);
}

jsonResponse([
    'success' => true,
    'role' => $user['role'],
    'is_admin' => $isAdmin,
    'is_modo' => $isModo,
    'user' => $user
]);
?>