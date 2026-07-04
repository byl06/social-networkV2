<?php
// api/profile/get_public_profile.php

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if (!$user_id) {
    jsonResponse(['error' => 'ID utilisateur requis'], 400);
}

$sql = "SELECT u.id_user, u.nom, u.prenom, u.photo_profil, u.bio, u.date_inscription,
        (SELECT COUNT(*) FROM posts WHERE id_user = u.id_user) as posts_count,
        (SELECT COUNT(*) FROM friends WHERE user1_id = u.id_user OR user2_id = u.id_user) as friends_count,
        (SELECT COUNT(*) FROM reactions r JOIN posts p ON r.id_post = p.id_post WHERE p.id_user = u.id_user AND r.type_reaction = 'like') as likes_count
        FROM users u
        WHERE u.id_user = ? AND u.statut = 'actif'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse(['error' => 'Utilisateur non trouvé'], 404);
}

jsonResponse([
    'success' => true,
    'user' => $user
]);
?>