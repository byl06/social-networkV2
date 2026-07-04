<?php
// api/admin/delete_post.php - Supprimer une publication

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

// Vérifier les droits (admin ou modo)
$checkRole = $pdo->prepare("SELECT r.nom_role FROM users u JOIN roles r ON u.id_role = r.id_role WHERE u.id_user = ?");
$checkRole->execute([$_SESSION['user_id']]);
$currentUser = $checkRole->fetch();

if ($currentUser['nom_role'] !== 'Administrateur' && $currentUser['nom_role'] !== 'Modérateur') {
    jsonResponse(['error' => 'Accès non autorisé'], 403);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['post_id'])) {
    jsonResponse(['error' => 'post_id requis'], 400);
}

$sql = "DELETE FROM posts WHERE id_post = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$data['post_id']]);

jsonResponse([
    'success' => true,
    'message' => 'Publication supprimée'
]);
?>