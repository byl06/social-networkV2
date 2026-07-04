<?php
// api/admin/get_stats.php - Statistiques pour le dashboard

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

// Vérifier les droits
$checkRole = $pdo->prepare("SELECT u.id_role, r.nom_role FROM users u JOIN roles r ON u.id_role = r.id_role WHERE u.id_user = ?");
$checkRole->execute([$_SESSION['user_id']]);
$user = $checkRole->fetch();

if ($user['nom_role'] !== 'Administrateur' && $user['nom_role'] !== 'Modérateur') {
    jsonResponse(['error' => 'Accès non autorisé'], 403);
}

// Récupérer les stats
$stats = [];

// Nombre d'utilisateurs
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $stmt->fetch()['count'];

// Nombre de publications
$stmt = $pdo->query("SELECT COUNT(*) as count FROM posts");
$stats['total_posts'] = $stmt->fetch()['count'];

// Nombre de commentaires
$stmt = $pdo->query("SELECT COUNT(*) as count FROM comments");
$stats['total_comments'] = $stmt->fetch()['count'];

// Nombre de likes
$stmt = $pdo->query("SELECT COUNT(*) as count FROM reactions WHERE type_reaction = 'like'");
$stats['total_likes'] = $stmt->fetch()['count'];

// Nombre d'amitiés
$stmt = $pdo->query("SELECT COUNT(*) as count FROM friends");
$stats['total_friendships'] = $stmt->fetch()['count'];

// Nombre de messages
$stmt = $pdo->query("SELECT COUNT(*) as count FROM messages");
$stats['total_messages'] = $stmt->fetch()['count'];

// Derniers inscrits
$stmt = $pdo->query("SELECT id_user, prenom, nom, email, date_inscription FROM users ORDER BY date_inscription DESC LIMIT 5");
$stats['recent_users'] = $stmt->fetchAll();

// Pour déboguer - afficher dans les logs
error_log("Stats chargées: " . json_encode($stats));

jsonResponse([
    'success' => true,
    'stats' => $stats
]);
?>