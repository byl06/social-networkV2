<?php
// api/chat/get_messages.php - Récupérer les messages d'une conversation

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$user_id = $_SESSION['user_id'];
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

if (!$conversation_id) {
    jsonResponse(['error' => 'ID de conversation requis'], 400);
}

// Vérifier que l'utilisateur participe à la conversation
$check = $pdo->prepare("SELECT id_participant FROM conversation_participants WHERE id_conversation = ? AND id_user = ?");
$check->execute([$conversation_id, $user_id]);
if (!$check->fetch()) {
    jsonResponse(['error' => 'Non autorisé'], 403);
}

// Marquer les messages comme lus
$update = $pdo->prepare("UPDATE messages SET est_lu = 1 WHERE id_conversation = ? AND id_sender != ?");
$update->execute([$conversation_id, $user_id]);

// Récupérer les messages
$sql = "SELECT m.*, u.prenom, u.nom, u.photo_profil
        FROM messages m
        JOIN users u ON m.id_sender = u.id_user
        WHERE m.id_conversation = ?
        ORDER BY m.date_envoi ASC
        LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute([$conversation_id]);
$messages = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'messages' => $messages
]);
?>