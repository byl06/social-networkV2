<?php
// api/notifications/get_notifications.php

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$user_id = $_SESSION['user_id'];

// Récupérer les notifications (demandes d'amis, likes, commentaires)
$notifications = [];

// 1. Demandes d'amis reçues
$sql = "SELECT fr.id_request, fr.date_request, 'friend_request' as type,
        u.id_user as sender_id, u.prenom, u.nom, u.photo_profil
        FROM friend_requests fr
        JOIN users u ON fr.sender_id = u.id_user
        WHERE fr.receiver_id = ? AND fr.statut = 'pending'
        ORDER BY fr.date_request DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$friendRequests = $stmt->fetchAll();

foreach ($friendRequests as $req) {
    $notifications[] = [
        'id' => $req['id_request'],
        'type' => 'friend_request',
        'message' => $req['prenom'] . ' ' . $req['nom'] . ' vous a envoyé une demande d\'amitié',
        'user_id' => $req['sender_id'],
        'prenom' => $req['prenom'],
        'nom' => $req['nom'],
        'photo_profil' => $req['photo_profil'],
        'date' => $req['date_request'],
        'time_ago' => timeAgo($req['date_request']),
        'read' => false
    ];
}

// 2. Likes sur mes publications
$sql = "SELECT r.id_reaction, r.date_reaction, 'like' as type,
        u.id_user as sender_id, u.prenom, u.nom, u.photo_profil,
        p.id_post, p.contenu
        FROM reactions r
        JOIN posts p ON r.id_post = p.id_post
        JOIN users u ON r.id_user = u.id_user
        WHERE p.id_user = ? AND r.type_reaction = 'like' AND r.id_user != ?
        ORDER BY r.date_reaction DESC
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id]);
$likes = $stmt->fetchAll();

foreach ($likes as $like) {
    $notifications[] = [
        'id' => $like['id_reaction'],
        'type' => 'like',
        'message' => $like['prenom'] . ' ' . $like['nom'] . ' a aimé votre publication',
        'user_id' => $like['sender_id'],
        'prenom' => $like['prenom'],
        'nom' => $like['nom'],
        'photo_profil' => $like['photo_profil'],
        'date' => $like['date_reaction'],
        'time_ago' => timeAgo($like['date_reaction']),
        'post_id' => $like['id_post'],
        'read' => false
    ];
}

// 3. Commentaires sur mes publications
$sql = "SELECT c.id_comment, c.date_commentaire, 'comment' as type,
        u.id_user as sender_id, u.prenom, u.nom, u.photo_profil,
        p.id_post, p.contenu
        FROM comments c
        JOIN posts p ON c.id_post = p.id_post
        JOIN users u ON c.id_user = u.id_user
        WHERE p.id_user = ? AND c.id_user != ?
        ORDER BY c.date_commentaire DESC
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id]);
$comments = $stmt->fetchAll();

foreach ($comments as $comment) {
    $notifications[] = [
        'id' => $comment['id_comment'],
        'type' => 'comment',
        'message' => $comment['prenom'] . ' ' . $comment['nom'] . ' a commenté votre publication',
        'user_id' => $comment['sender_id'],
        'prenom' => $comment['prenom'],
        'nom' => $comment['nom'],
        'photo_profil' => $comment['photo_profil'],
        'date' => $comment['date_commentaire'],
        'time_ago' => timeAgo($comment['date_commentaire']),
        'post_id' => $comment['id_post'],
        'read' => false
    ];
}

// Trier par date (plus récent d'abord)
usort($notifications, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Limiter à 30 notifications
$notifications = array_slice($notifications, 0, 30);

// Compter les non lues
$unreadCount = count($notifications);

jsonResponse([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => $unreadCount
]);

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'à l\'instant';
    if ($diff < 3600) return round($diff / 60) . ' min';
    if ($diff < 86400) return round($diff / 3600) . ' h';
    if ($diff < 604800) return round($diff / 86400) . ' j';
    return date('d/m/Y', $timestamp);
}
?>