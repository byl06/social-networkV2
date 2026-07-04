<?php
// api/comments/add_comment.php - Ajouter un commentaire

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    jsonResponse(['error' => 'Non authentifié'], 401);
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

// Valider les données
if (empty($data['post_id']) || empty($data['contenu'])) {
    jsonResponse(['error' => 'Données incomplètes. post_id et contenu sont requis'], 400);
}

$user_id = $_SESSION['user_id'];
$post_id = intval($data['post_id']);
$contenu = trim(htmlspecialchars($data['contenu']));

// Vérifier que le contenu n'est pas vide après nettoyage
if (empty($contenu)) {
    jsonResponse(['error' => 'Le commentaire ne peut pas être vide'], 400);
}

// Vérifier que le post existe
$checkPost = $pdo->prepare("SELECT id_post FROM posts WHERE id_post = ?");
$checkPost->execute([$post_id]);
if (!$checkPost->fetch()) {
    jsonResponse(['error' => 'Publication introuvable'], 404);
}

// Insérer le commentaire
$sql = "INSERT INTO comments (id_post, id_user, contenu) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$post_id, $user_id, $contenu]);
    $comment_id = $pdo->lastInsertId();
    
    // Récupérer le commentaire avec les infos utilisateur
    $sql2 = "SELECT 
                c.*, 
                u.nom, 
                u.prenom, 
                u.photo_profil,
                u.id_user as user_id
             FROM comments c
             JOIN users u ON c.id_user = u.id_user
             WHERE c.id_comment = ?";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([$comment_id]);
    $comment = $stmt2->fetch();
    
    // Formater la date
    $comment['date_commentaire'] = 'à l\'instant';
    
    // Récupérer le nouveau nombre total de commentaires pour ce post
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM comments WHERE id_post = ?");
    $countStmt->execute([$post_id]);
    $totalComments = $countStmt->fetch()['total'];
    
    // Retourner la réponse complète
    jsonResponse([
        'success' => true,
        'message' => 'Commentaire ajouté avec succès',
        'comment' => $comment,
        'total_comments' => $totalComments
    ], 201);
    
} catch(PDOException $e) {
    // Journaliser l'erreur pour le débogage
    error_log('Erreur add_comment.php: ' . $e->getMessage());
    jsonResponse(['error' => 'Erreur lors de l\'ajout du commentaire: ' . $e->getMessage()], 500);
}

// Fonction de formatage de date (si besoin)
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'à l\'instant';
    if ($diff < 3600) return round($diff / 60) . ' min';
    if ($diff < 86400) return round($diff / 3600) . ' h';
    return date('d/m/Y', $timestamp);
}
?>