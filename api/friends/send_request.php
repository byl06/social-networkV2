<?php
// api/friends/send_request.php - Envoyer une invitation d'amitié

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['receiver_id'])) {
    jsonResponse(['error' => 'ID du destinataire requis'], 400);
}

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($data['receiver_id']);

// Vérifier que l'utilisateur existe
$check = $pdo->prepare("SELECT id_user FROM users WHERE id_user = ?");
$check->execute([$receiver_id]);
if (!$check->fetch()) {
    jsonResponse(['error' => 'Utilisateur introuvable'], 404);
}

// Vérifier qu'on n'est pas déjà amis
$checkFriend = $pdo->prepare("SELECT id_friend FROM friends WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
$checkFriend->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
if ($checkFriend->fetch()) {
    jsonResponse(['error' => 'Vous êtes déjà amis'], 400);
}

// Vérifier qu'une demande n'existe pas déjà
$checkRequest = $pdo->prepare("SELECT id_request FROM friend_requests WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
$checkRequest->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
if ($checkRequest->fetch()) {
    jsonResponse(['error' => 'Une demande existe déjà'], 400);
}

$sql = "INSERT INTO friend_requests (sender_id, receiver_id, statut) VALUES (?, ?, 'pending')";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$sender_id, $receiver_id]);
    jsonResponse([
        'success' => true,
        'message' => 'Invitation envoyée'
    ], 201);
} catch(PDOException $e) {
    jsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
}
?>