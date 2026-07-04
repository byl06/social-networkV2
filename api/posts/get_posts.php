<?php
// api/posts/get_posts.php - Récupérer toutes les publications

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'] ?? null;

// Récupérer les publications avec les infos utilisateur et les likes
$sql = "SELECT 
            p.*,
            u.nom,
            u.prenom,
            u.photo_profil,
            COUNT(DISTINCT CASE WHEN r.type_reaction = 'like' THEN r.id_user END) as likes_count,
            COUNT(DISTINCT c.id_comment) as comments_count,
            EXISTS(SELECT 1 FROM reactions WHERE id_post = p.id_post AND id_user = ? AND type_reaction = 'like') as user_liked
        FROM posts p
        JOIN users u ON p.id_user = u.id_user
        LEFT JOIN reactions r ON p.id_post = r.id_post
        LEFT JOIN comments c ON p.id_post = c.id_post
        GROUP BY p.id_post
        ORDER BY p.date_publication DESC
        LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();

// Formater les dates
foreach ($posts as &$post) {
    $post['date_publication'] = timeAgo($post['date_publication']);
    $post['user_liked'] = (bool)$post['user_liked'];
}

jsonResponse([
    'success' => true,
    'posts' => $posts
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