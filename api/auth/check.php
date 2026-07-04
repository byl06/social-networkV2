<?php
// api/auth/check.php - Vérifier l'état de connexion

require_once '../config.php';

if (isset($_SESSION['user_id'])) {
    jsonResponse([
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'nom' => $_SESSION['user_nom'],
            'prenom' => $_SESSION['user_prenom'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'photo' => $_SESSION['user_photo'] ?? 'default-avatar.png'
        ]
    ]);
} else {
    jsonResponse(['logged_in' => false]);
}
?>