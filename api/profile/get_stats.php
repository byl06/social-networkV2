<?php
// api/profile/get_stats.php

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : ($_SESSION['user_id'] ?? null);

if (!$user_id) {
    jsonResponse(['error' => 'Utilisateur non spécifié'], 400);
}

// Nombre de publications
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM posts WHERE id_user = ?");
$stmt->execute([$user_id]);
$posts_count = $stmt->fetch()['count'];

// Nombre d'amis
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM friends WHERE user1_id = ? OR user2_id = ?");
$stmt->execute([$user_id, $user_id]);
$friends_count = $stmt->fetch()['count'];

// Nombre de likes reçus sur les publications
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reactions r JOIN posts p ON r.id_post = p.id_post WHERE p.id_user = ? AND r.type_reaction = 'like'");
$stmt->execute([$user_id]);
$likes_count = $stmt->fetch()['count'];

jsonResponse([
    'success' => true,
    'stats' => [
        'posts_count' => (int)$posts_count,
        'friends_count' => (int)$friends_count,
        'followers_count' => (int)$likes_count
    ]
]);
?>