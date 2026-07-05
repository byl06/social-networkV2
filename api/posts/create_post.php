<?php
// api/posts/create_post.php

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);

$contenu = isset($data['contenu']) ? trim(htmlspecialchars($data['contenu'])) : '';
$image = isset($data['image']) ? $data['image'] : null;

if (empty($contenu) && empty($image)) {
    jsonResponse(['error' => 'Le contenu ou une image est requis'], 400);
}

$user_id = $_SESSION['user_id'];

$sql = "INSERT INTO posts (id_user, contenu, image) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$user_id, $contenu, $image]);
    $post_id = $pdo->lastInsertId();
    
    // Récupérer le post créé
    $sql2 = "SELECT p.*, u.nom, u.prenom, u.photo_profil
             FROM posts p
             JOIN users u ON p.id_user = u.id_user
             WHERE p.id_post = ?";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([$post_id]);
    $post = $stmt2->fetch();
    
    $post['date_publication'] = 'à l\'instant';
    $post['likes_count'] = 0;
    $post['comments_count'] = 0;
    $post['user_liked'] = false;
    
    jsonResponse([
        'success' => true,
        'message' => 'Publication créée',
        'post' => $post
    ], 201);
    
} catch(PDOException $e) {
    jsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
}
?>