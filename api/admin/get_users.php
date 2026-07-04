<?php
// api/admin/get_users.php

require_once '../config.php';

// Désactiver l'affichage des erreurs
error_reporting(0);
ini_set('display_errors', 0);
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

// Vérifier les droits
$checkRole = $pdo->prepare("SELECT r.nom_role FROM users u JOIN roles r ON u.id_role = r.id_role WHERE u.id_user = ?");
$checkRole->execute([$_SESSION['user_id']]);
$user = $checkRole->fetch();

if (!$user || ($user['nom_role'] !== 'Administrateur' && $user['nom_role'] !== 'Modérateur')) {
    jsonResponse(['error' => 'Accès non autorisé'], 403);
}

$sql = "SELECT u.*, r.nom_role as role 
        FROM users u
        JOIN roles r ON u.id_role = r.id_role
        ORDER BY u.date_inscription DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();

// Enlever les mots de passe
foreach ($users as &$u) {
    unset($u['mot_de_passe']);
}

jsonResponse([
    'success' => true,
    'users' => $users,
    'is_admin' => ($user['nom_role'] === 'Administrateur')
]);
?>