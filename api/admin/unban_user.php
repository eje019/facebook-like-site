<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Vérifier si l'admin/moderateur est connecté
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'moderator'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

require_once '../../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? null;
    
    if (!$user_id || !is_numeric($user_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID utilisateur invalide']);
        exit;
    }
    
    // Vérifier que l'utilisateur existe et est banni
    $check_query = "SELECT id, name, is_banned, role FROM users WHERE id = ?";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$user_id]);
    $user = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Utilisateur non trouvé']);
        exit;
    }
    
    if (!$user['is_banned']) {
        http_response_code(400);
        echo json_encode(['error' => "Cet utilisateur n'est pas banni"]);
        exit;
    }
    
    // Les modérateurs ne peuvent pas débannir les admins
    if ($_SESSION['role'] === 'moderator' && $user['role'] === 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Les modérateurs ne peuvent pas débannir les administrateurs']);
        exit;
    }
    
    // Débannir l'utilisateur
    $unban_query = "UPDATE users SET is_banned = 0 WHERE id = ?";
    $unban_stmt = $pdo->prepare($unban_query);
    $unban_stmt->execute([$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => "L'utilisateur {$user['name']} a été débanni avec succès"
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?> 