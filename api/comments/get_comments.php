<?php
// api/comments/get_comments.php - Récupérer les commentaires d'un post

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$post_id = $_GET['post_id'] ?? null;

if (!$post_id) {
    jsonResponse(['error' => 'ID du post requis'], 400);
}

$sql = "SELECT c.*, u.nom, u.prenom, u.photo_profil
        FROM comments c
        JOIN users u ON c.id_user = u.id_user
        WHERE c.id_post = ?
        ORDER BY c.date_commentaire ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();

foreach ($comments as &$comment) {
    $comment['date_commentaire'] = timeAgo($comment['date_commentaire']);
}

jsonResponse([
    'success' => true,
    'comments' => $comments
]);

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'à l\'instant';
    if ($diff < 3600) return round($diff / 60) . ' min';
    if ($diff < 86400) return round($diff / 3600) . ' h';
    return date('d/m/Y', $timestamp);
}
?>