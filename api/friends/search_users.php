<?php
// api/friends/search_users.php

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$user_id = $_SESSION['user_id'];

if (strlen($query) < 2) {
    jsonResponse(['success' => true, 'users' => []]);
}

try {
    $searchTerm = "%$query%";
    $sql = "SELECT u.id_user, u.nom, u.prenom, u.photo_profil, u.bio
            FROM users u
            WHERE u.id_user != ?
            AND (u.prenom LIKE ? OR u.nom LIKE ? OR CONCAT(u.prenom, ' ', u.nom) LIKE ?)
            AND u.statut = 'actif'
            LIMIT 15";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $searchTerm, $searchTerm, $searchTerm]);
    $users = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'users' => $users
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Erreur de base de données: ' . $e->getMessage()], 500);
}
?>