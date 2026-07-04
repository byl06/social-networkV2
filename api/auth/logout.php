<?php
// api/auth/logout.php - Déconnexion

require_once '../config.php';

session_destroy();

jsonResponse([
    'success' => true,
    'message' => 'Déconnecté avec succès'
]);
?>