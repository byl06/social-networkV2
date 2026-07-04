<?php
// api/auth/reset_password.php - Réinitialiser le mot de passe

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['token']) || empty($data['mot_de_passe'])) {
    jsonResponse(['error' => 'Token et mot de passe requis'], 400);
}

$token = $data['token'];
$new_password = $data['mot_de_passe'];

if (strlen($new_password) < 6) {
    jsonResponse(['error' => 'Le mot de passe doit contenir au moins 6 caractères'], 400);
}

// Vérifier le token
$stmt = $pdo->prepare("SELECT pr.id_user, pr.date_expiration, u.email 
                       FROM password_resets pr
                       JOIN users u ON pr.id_user = u.id_user
                       WHERE pr.token = ? AND pr.utilise = 0");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    jsonResponse(['error' => 'Token invalide ou déjà utilisé'], 400);
}

// Vérifier si le token n'a pas expiré
$expiration = strtotime($reset['date_expiration']);
if (time() > $expiration) {
    jsonResponse(['error' => 'Le lien a expiré. Faites une nouvelle demande'], 400);
}

// Mettre à jour le mot de passe
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$pdo->beginTransaction();

try {
    // Mettre à jour le mot de passe
    $stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id_user = ?");
    $stmt->execute([$hashed_password, $reset['id_user']]);
    
    // Marquer le token comme utilisé
    $stmt = $pdo->prepare("UPDATE password_resets SET utilise = 1 WHERE token = ?");
    $stmt->execute([$token]);
    
    $pdo->commit();
    
    jsonResponse([
        'success' => true,
        'message' => 'Mot de passe réinitialisé avec succès'
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    jsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
}
?>