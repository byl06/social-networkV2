<?php
// api/chat/get_conversations.php - Récupérer les conversations de l'utilisateur

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
            c.id_conversation,
            c.date_creation,
            (SELECT COUNT(*) FROM messages WHERE id_conversation = c.id_conversation AND id_sender != ? AND est_lu = 0) as unread_count,
            (SELECT m.contenu FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.date_envoi DESC LIMIT 1) as last_message,
            (SELECT m.date_envoi FROM messages m WHERE m.id_conversation = c.id_conversation ORDER BY m.date_envoi DESC LIMIT 1) as last_message_time,
            CASE 
                WHEN cp1.id_user != ? THEN u1.prenom
                ELSE u2.prenom
            END as other_prenom,
            CASE 
                WHEN cp1.id_user != ? THEN u1.nom
                ELSE u2.nom
            END as other_nom,
            CASE 
                WHEN cp1.id_user != ? THEN u1.id_user
                ELSE u2.id_user
            END as other_id,
            CASE 
                WHEN cp1.id_user != ? THEN u1.photo_profil
                ELSE u2.photo_profil
            END as other_photo
        FROM conversations c
        JOIN conversation_participants cp1 ON c.id_conversation = cp1.id_conversation AND cp1.id_user = ?
        JOIN conversation_participants cp2 ON c.id_conversation = cp2.id_conversation AND cp2.id_user != ?
        JOIN users u1 ON cp1.id_user = u1.id_user
        JOIN users u2 ON cp2.id_user = u2.id_user
        ORDER BY last_message_time DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'conversations' => $conversations
]);
?>