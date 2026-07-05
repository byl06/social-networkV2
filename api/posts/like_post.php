<?php
// api/posts/like_post.php - Ajouter/retirer un like

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['post_id'])) {
    jsonResponse(['error' => 'ID du post requis'], 400);
}

$user_id = $_SESSION['user_id'];
$post_id = $data['post_id'];
$action = $data['action'] ?? 'like'; // like ou unlike

if ($action === 'like') {
    // Vérifier si l'utilisateur a déjà liké
    $check = $pdo->prepare("SELECT id_reaction FROM reactions WHERE id_post = ? AND id_user = ?");
    $check->execute([$post_id, $user_id]);
    
    if ($check->fetch()) {
        jsonResponse(['error' => 'Vous avez déjà liké ce post'], 400);
    }
    
    $sql = "INSERT INTO reactions (id_post, id_user, type_reaction) VALUES (?, ?, 'like')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id, $user_id]);
    
} else {
    $sql = "DELETE FROM reactions WHERE id_post = ? AND id_user = ? AND type_reaction = 'like'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id, $user_id]);
}

// Récupérer le nouveau nombre de likes
$count = $pdo->prepare("SELECT COUNT(*) as count FROM reactions WHERE id_post = ? AND type_reaction = 'like'");
$count->execute([$post_id]);
$likes_count = $count->fetch()['count'];

jsonResponse([
    'success' => true,
    'liked' => ($action === 'like'),
    'likes_count' => $likes_count
]);
?>