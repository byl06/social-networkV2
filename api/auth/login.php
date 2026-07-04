<?php
// api/auth/login.php - Connexion utilisateur

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email']) || empty($data['mot_de_passe'])) {
    jsonResponse(['error' => 'Email et mot de passe requis'], 400);
}

$email = trim(filter_var($data['email'], FILTER_SANITIZE_EMAIL));
$mot_de_passe = $data['mot_de_passe'];

// Récupérer l'utilisateur
$sql = "SELECT u.*, r.nom_role as role 
        FROM users u 
        LEFT JOIN roles r ON u.id_role = r.id_role 
        WHERE u.email = ? AND u.statut = 'actif'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse(['error' => 'Email ou mot de passe incorrect'], 401);
}

// Vérifier le mot de passe
if (!password_verify($mot_de_passe, $user['mot_de_passe'])) {
    jsonResponse(['error' => 'Email ou mot de passe incorrect'], 401);
}

// 🔥 Vérifier si l'email est confirmé
if (!$user['email_verifie']) {
    jsonResponse(['error' => 'Veuillez vérifier votre email avant de vous connecter'], 403);
}

// Stocker l'utilisateur en session PHP
$_SESSION['user_id'] = $user['id_user'];
$_SESSION['user_nom'] = $user['nom'];
$_SESSION['user_prenom'] = $user['prenom'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_photo'] = $user['photo_profil'];

unset($user['mot_de_passe']);

jsonResponse([
    'success' => true,
    'message' => 'Connexion réussie',
    'user' => $user,
    'session_id' => session_id()
]);
?>