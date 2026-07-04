<?php
// api/friends/get_users.php - Récupérer tous les utilisateurs sauf moi et mes amis

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$user_id = $_SESSION['user_id'];

// Récupérer les utilisateurs qui ne sont pas déjà amis et qui ne sont pas l'utilisateur courant
$sql = "SELECT u.id_user, u.nom, u.prenom, u.email, u.photo_profil, u.bio,
        CASE 
            WHEN fr.sender_id = ? AND fr.statut = 'pending' THEN 'sent'
            WHEN fr.receiver_id = ? AND fr.statut = 'pending' THEN 'received'
            WHEN f.user1_id IS NOT NULL OR f.user2_id IS NOT NULL THEN 'friend'
            ELSE 'none'
        END as relation_status
        FROM users u
        LEFT JOIN friend_requests fr ON (fr.sender_id = ? AND fr.receiver_id = u.id_user) OR (fr.receiver_id = ? AND fr.sender_id = u.id_user)
        LEFT JOIN friends f ON (f.user1_id = ? AND f.user2_id = u.id_user) OR (f.user2_id = ? AND f.user1_id = u.id_user)
        WHERE u.id_user != ?
        AND (f.user1_id IS NULL AND f.user2_id IS NULL)
        GROUP BY u.id_user
        ORDER BY u.prenom ASC
        LIMIT 20";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$users = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'users' => $users
]);
?>