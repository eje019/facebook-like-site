<?php
session_start();
header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté (session PHP ou sessionStorage)
$user_id = null;

// Méthode 1: Session PHP
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// Méthode 2: SessionStorage (via header ou paramètre)
if (!$user_id) {
    // Vérifier si l'utilisateur est passé en header
    $headers = getallheaders();
    if (isset($headers['X-User-Id'])) {
        $user_id = $headers['X-User-Id'];
    }
    // Ou vérifier en paramètre GET
    elseif (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
    }
}

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

require_once '../../config/database.php';

try {
    // Vérifier que l'utilisateur existe
    $check_user = "SELECT id FROM users WHERE id = ?";
    $check_stmt = $pdo->prepare($check_user);
    $check_stmt->execute([$user_id]);
    if (!$check_stmt->fetch()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Utilisateur invalide']);
        exit;
    }
    
    // Récupérer tous les utilisateurs sauf l'utilisateur connecté
    $query = "
        SELECT id, prenom, nom, username, avatar, is_online
        FROM users 
        WHERE id != ?
        ORDER BY prenom, nom
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
} 