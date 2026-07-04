<?php
// api/friends/get_requests.php - Récupérer les invitations reçues

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT fr.*, u.nom, u.prenom, u.email, u.photo_profil
        FROM friend_requests fr
        JOIN users u ON fr.sender_id = u.id_user
        WHERE fr.receiver_id = ? AND fr.statut = 'pending'
        ORDER BY fr.date_request DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'requests' => $requests
]);
?>