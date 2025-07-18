<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Vérifier si l'admin/moderateur est connecté
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'moderator'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

require_once '../../config/database.php';

try {
    $stats = [];

    // Nombre d'utilisateurs
    $users_query = "SELECT COUNT(*) as count FROM users";
    $users_stmt = $pdo->query($users_query);
    $stats['users'] = (int)($users_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    // Nombre de posts
    $posts_query = "SELECT COUNT(*) as count FROM posts";
    $posts_stmt = $pdo->query($posts_query);
    $stats['posts'] = (int)($posts_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    // Nombre de commentaires
    $comments_query = "SELECT COUNT(*) as count FROM comments";
    $comments_stmt = $pdo->query($comments_query);
    $stats['comments'] = (int)($comments_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    // Nombre de likes
    $likes_query = "SELECT COUNT(*) as count FROM likes WHERE type = 'like'";
    $likes_stmt = $pdo->query($likes_query);
    $stats['likes'] = (int)($likes_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

    // Statistiques détaillées pour admin, basiques pour modérateur
    $response_stats = $stats;
    
    if ($_SESSION['role'] === 'admin') {
        // Statistiques détaillées pour admin
        $response_stats['detailed'] = true;
        
        // Ajouter des stats supplémentaires pour admin
        $recent_users_query = "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $recent_users_stmt = $pdo->query($recent_users_query);
        $response_stats['recent_users'] = (int)($recent_users_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
        
        $banned_users_query = "SELECT COUNT(*) as count FROM users WHERE is_banned = 1";
        $banned_users_stmt = $pdo->query($banned_users_query);
        $response_stats['banned_users'] = (int)($banned_users_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
        
        $admin_users_query = "SELECT COUNT(*) as count FROM users WHERE role = 'admin'";
        $admin_users_stmt = $pdo->query($admin_users_query);
        $response_stats['admin_users'] = (int)($admin_users_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
        
        $moderator_users_query = "SELECT COUNT(*) as count FROM users WHERE role = 'moderator'";
        $moderator_users_stmt = $pdo->query($moderator_users_query);
        $response_stats['moderator_users'] = (int)($moderator_users_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    } else {
        // Statistiques basiques pour modérateur
        $response_stats['detailed'] = false;
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $response_stats,
        'user_role' => $_SESSION['role']
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?> 