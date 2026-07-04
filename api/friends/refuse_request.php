<?php
// api/friends/refuse_request.php - Refuser une invitation

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['request_id'])) {
    jsonResponse(['error' => 'ID de la demande requis'], 400);
}

$user_id = $_SESSION['user_id'];
$request_id = intval($data['request_id']);

$sql = "UPDATE friend_requests SET statut = 'refused' WHERE id_request = ? AND receiver_id = ? AND statut = 'pending'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$request_id, $user_id]);

jsonResponse([
    'success' => true,
    'message' => 'Invitation refusée'
]);
?>