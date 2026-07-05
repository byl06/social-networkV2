<?php
// api/profile/get_profile.php - Récupérer le profil utilisateur

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT u.*, r.nom_role as role 
        FROM users u
        LEFT JOIN roles r ON u.id_role = r.id_role
        WHERE u.id_user = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

unset($user['mot_de_passe']);

jsonResponse([
    'success' => true,
    'user' => $user
]);
?>