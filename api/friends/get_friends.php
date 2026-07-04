<?php
// api/friends/get_friends.php - Récupérer la liste des amis

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
            CASE 
                WHEN f.user1_id = ? THEN u2.id_user
                ELSE u1.id_user
            END as id_user,
            CASE 
                WHEN f.user1_id = ? THEN u2.nom
                ELSE u1.nom
            END as nom,
            CASE 
                WHEN f.user1_id = ? THEN u2.prenom
                ELSE u1.prenom
            END as prenom,
            CASE 
                WHEN f.user1_id = ? THEN u2.photo_profil
                ELSE u1.photo_profil
            END as photo_profil,
            f.date_friendship
        FROM friends f
        JOIN users u1 ON f.user1_id = u1.id_user
        JOIN users u2 ON f.user2_id = u2.id_user
        WHERE f.user1_id = ? OR f.user2_id = ?
        ORDER BY prenom ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$friends = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'friends' => $friends
]);
?>