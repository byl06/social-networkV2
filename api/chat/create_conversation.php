<?php
// api/chat/create_conversation.php - Créer une nouvelle conversation

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['receiver_id'])) {
    jsonResponse(['error' => 'receiver_id requis'], 400);
}

$user_id = $_SESSION['user_id'];
$receiver_id = intval($data['receiver_id']);

// Vérifier qu'ils sont amis
$checkFriend = $pdo->prepare("SELECT id_friend FROM friends WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
$checkFriend->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
if (!$checkFriend->fetch()) {
    jsonResponse(['error' => 'Vous devez être amis pour discuter'], 403);
}

// Vérifier si une conversation existe déjà
$checkConv = $pdo->prepare("
    SELECT c.id_conversation 
    FROM conversations c
    JOIN conversation_participants cp1 ON c.id_conversation = cp1.id_conversation AND cp1.id_user = ?
    JOIN conversation_participants cp2 ON c.id_conversation = cp2.id_conversation AND cp2.id_user = ?
    WHERE (SELECT COUNT(*) FROM conversation_participants WHERE id_conversation = c.id_conversation) = 2
");
$checkConv->execute([$user_id, $receiver_id]);
$existing = $checkConv->fetch();

if ($existing) {
    jsonResponse([
        'success' => true,
        'conversation_id' => $existing['id_conversation'],
        'existing' => true
    ]);
}

// Créer une nouvelle conversation
$pdo->beginTransaction();

$createConv = $pdo->prepare("INSERT INTO conversations () VALUES ()");
$createConv->execute();
$conversation_id = $pdo->lastInsertId();

$addParticipant = $pdo->prepare("INSERT INTO conversation_participants (id_conversation, id_user) VALUES (?, ?)");
$addParticipant->execute([$conversation_id, $user_id]);
$addParticipant->execute([$conversation_id, $receiver_id]);

$pdo->commit();

jsonResponse([
    'success' => true,
    'conversation_id' => $conversation_id
]);
?>