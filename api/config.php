<?php
// api/config.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Paramètres de connexion - VÉRIFIE CES INFOS
define('DB_HOST', 'localhost');
define('DB_NAME', 'socialwave_db');  // Vérifie le nom de ta base
define('DB_USER', 'root');
define('DB_PASS', '');  // Laisse vide si pas de mot de passe

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    // Afficher l'erreur réelle pour le débogage
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion: ' . $e->getMessage()]);
    exit();
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

session_start();
?>