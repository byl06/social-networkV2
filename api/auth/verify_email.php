<?php
// api/auth/verify_email.php

require_once '../config.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    die('Token manquant');
}

$stmt = $pdo->prepare("SELECT id_user, email FROM users WHERE token_verification = ? AND email_verifie = 0");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die('Token invalide ou déjà utilisé');
}

$stmt = $pdo->prepare("UPDATE users SET email_verifie = 1, token_verification = NULL WHERE id_user = ?");
$stmt->execute([$user['id_user']]);

// Rediriger vers la page de succès
header('Location: ../../vues/clients/success.html');
exit();
?>