<?php
// api/notifications/mark_read.php

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

// Pour l'instant, on marque juste comme lu en retournant un succès
// Plus tard on pourra ajouter une table de notifications

jsonResponse([
    'success' => true,
    'message' => 'Notifications marquées comme lues'
]);
?>