<?php
// api/friends/accept_request.php - Accepter une invitation

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

// Vérifier que la demande existe et est destinée à l'utilisateur
$check = $pdo->prepare("SELECT sender_id, receiver_id FROM friend_requests WHERE id_request = ? AND receiver_id = ? AND statut = 'pending'");
$check->execute([$request_id, $user_id]);
$request = $check->fetch();

if (!$request) {
    jsonResponse(['error' => 'Demande introuvable ou déjà traitée'], 404);
}

// Mettre à jour le statut de la demande
$update = $pdo->prepare("UPDATE friend_requests SET statut = 'accepted' WHERE id_request = ?");
$update->execute([$request_id]);

// Ajouter la relation d'amitié
$addFriend = $pdo->prepare("INSERT INTO friends (user1_id, user2_id) VALUES (?, ?)");
$addFriend->execute([$request['sender_id'], $request['receiver_id']]);

jsonResponse([
    'success' => true,
    'message' => 'Invitation acceptée'
]);
?>