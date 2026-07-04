<?php
// api/auth/register.php

error_reporting(0);
ini_set('display_errors', 0);
ob_clean();

require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Données invalides']);
    exit();
}

if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['mot_de_passe']) || empty($data['confirm_mot_de_passe'])) {
    echo json_encode(['error' => 'Tous les champs sont requis']);
    exit();
}

$nom = trim($data['nom']);
$prenom = trim($data['prenom']);
$email = trim($data['email']);
$password = $data['mot_de_passe'];
$confirm = $data['confirm_mot_de_passe'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Email invalide']);
    exit();
}

if ($password !== $confirm) {
    echo json_encode(['error' => 'Les mots de passe ne correspondent pas']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['error' => 'Le mot de passe doit contenir au moins 6 caractères']);
    exit();
}

$stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Cet email est déjà utilisé']);
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));
$bio = "Nouveau membre de SocialWave !";

$sql = "INSERT INTO users (nom, prenom, email, mot_de_passe, token_verification, bio, email_verifie, date_inscription) 
        VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$nom, $prenom, $email, $hashed_password, $token, $bio]);
    $user_id = $pdo->lastInsertId();
    
    $confirm_link = "http://localhost/social-network/api/auth/verify_email.php?token=" . $token;
    
    // Envoi de l'email HTML
    $to = $email;
    $subject = "Confirmation de votre compte SocialWave";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: SocialWave <no-reply@socialwave.com>\r\n";
    
    $message = '<!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family:Arial;background:#0a0a0f;color:#f0f0f8;padding:20px;text-align:center;">
        <div style="max-width:500px;margin:0 auto;background:#12121a;padding:40px;border-radius:16px;border:1px solid rgba(255,255,255,0.08);">
            <h1 style="color:#7c6fff;font-size:28px;">🌊 SocialWave</h1>
            <h2 style="color:#f0f0f8;">Bienvenue ' . htmlspecialchars($prenom) . ' !</h2>
            <p style="color:#8888aa;margin:20px 0;line-height:1.6;">
                Merci de vous être inscrit sur SocialWave.<br>
                Cliquez sur le bouton ci-dessous pour activer votre compte.
            </p>
            <a href="' . $confirm_link . '" style="display:inline-block;background:#7c6fff;color:white;padding:14px 35px;border-radius:50px;text-decoration:none;font-weight:600;font-size:16px;">Activer mon compte</a>
            <p style="margin-top:25px;font-size:12px;color:#555577;">Si vous n\'êtes pas à l\'origine de cette inscription, ignorez cet email.</p>
            <p style="font-size:11px;color:#444466;">© 2026 SocialWave</p>
        </div>
    </body>
    </html>';
    
    mail($to, $subject, $message, $headers);
    
    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie !',
        'user_id' => $user_id,
        'email' => $email,
        'token' => $token,
        'debug_link' => $confirm_link
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
}
?>