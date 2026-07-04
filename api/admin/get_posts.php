<?php
// api/admin/get_posts.php - Liste des publications (admin)

require_once '../config.php';

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

if ($user['nom_role'] !== 'Administrateur' && $user['nom_role'] !== 'Modérateur') {
    jsonResponse(['error' => 'Accès non autorisé'], 403);
}

$sql = "SELECT p.*, u.prenom, u.nom, u.email,
        (SELECT COUNT(*) FROM comments WHERE id_post = p.id_post) as comments_count,
        (SELECT COUNT(*) FROM reactions WHERE id_post = p.id_post) as reactions_count
        FROM posts p
        JOIN users u ON p.id_user = u.id_user
        ORDER BY p.date_publication DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'posts' => $posts
]);
?>