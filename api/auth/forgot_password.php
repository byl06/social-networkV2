<?php
// api/auth/forgot_password.php - Envoyer email de réinitialisation

require_once '../config.php';

// Désactiver l'affichage des erreurs
error_reporting(0);
ini_set('display_errors', 0);

// Vider le buffer de sortie
ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email'])) {
    jsonResponse(['error' => 'Email requis'], 400);
}

$email = trim(filter_var($data['email'], FILTER_SANITIZE_EMAIL));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['error' => 'Email invalide'], 400);
}

// Vérifier si l'email existe
$stmt = $pdo->prepare("SELECT id_user, prenom, nom FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse([
        'success' => true, 
        'message' => 'Si cet email existe, un lien de réinitialisation vous a été envoyé'
    ]);
}

// Générer un token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Supprimer les anciens tokens
$stmt = $pdo->prepare("DELETE FROM password_resets WHERE id_user = ?");
$stmt->execute([$user['id_user']]);

// Insérer le nouveau token
$stmt = $pdo->prepare("INSERT INTO password_resets (id_user, token, date_expiration) VALUES (?, ?, ?)");
$stmt->execute([$user['id_user'], $token, $expires]);

// Construire le lien de réinitialisation
$reset_link = "http://localhost/social-network/vues/clients/reset_password.html?token=" . $token;

// Pour le développement, on affiche le lien dans la réponse
jsonResponse([
    'success' => true,
    'message' => 'Un email de réinitialisation a été envoyé',
    'debug_link' => $reset_link
]);
?>