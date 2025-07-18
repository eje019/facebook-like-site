<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';
require_once 'admin_auth.php';

// Vérifier l'authentification admin
$adminAuth = new AdminAuth();
$authResult = $adminAuth->checkAuth();

if (!$authResult['success']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Vérifier que l'utilisateur est admin ou moderator
if (!in_array($authResult['user']['role'], ['admin', 'moderator'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer l'ID de l'utilisateur
$userId = $_GET['id'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID utilisateur requis']);
    exit;
}

try {
    // Récupérer les données de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT 
            id,
            first_name,
            last_name,
            email,
            profile_photo,
            role,
            is_banned,
            created_at,
            last_login
        FROM users 
        WHERE id = ?
    ");
    
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        exit;
    }
    
    // Compter les posts de l'utilisateur
    $stmt = $pdo->prepare("SELECT COUNT(*) as posts_count FROM posts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $postsCount = $stmt->fetch(PDO::FETCH_ASSOC)['posts_count'];
    
    // Compter les likes totaux
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as likes_count 
        FROM post_likes pl 
        JOIN posts p ON pl.post_id = p.id 
        WHERE p.user_id = ?
    ");
    $stmt->execute([$userId]);
    $likesCount = $stmt->fetch(PDO::FETCH_ASSOC)['likes_count'];
    
    // Compter les commentaires totaux
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as comments_count 
        FROM comments c 
        JOIN posts p ON c.post_id = p.id 
        WHERE p.user_id = ?
    ");
    $stmt->execute([$userId]);
    $commentsCount = $stmt->fetch(PDO::FETCH_ASSOC)['comments_count'];
    
    // Ajouter les statistiques à l'utilisateur
    $user['posts_count'] = (int)$postsCount;
    $user['likes_count'] = (int)$likesCount;
    $user['comments_count'] = (int)$commentsCount;
    
    // Formater les dates
    $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created_at']));
    $user['last_login'] = $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : null;
    
    // Masquer l'email si l'utilisateur n'est pas admin (pour les modérateurs)
    if ($authResult['user']['role'] !== 'admin') {
        $user['email'] = maskEmail($user['email']);
    }
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur base de données: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}

/**
 * Masquer partiellement l'email pour les modérateurs
 */
function maskEmail($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return $email;
    }
    
    $username = $parts[0];
    $domain = $parts[1];
    
    if (strlen($username) <= 2) {
        $maskedUsername = $username;
    } else {
        $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
    }
    
    return $maskedUsername . '@' . $domain;
}
?> 