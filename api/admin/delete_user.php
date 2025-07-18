<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Vérifier si l'admin est connecté (seuls les admins peuvent supprimer)
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Accès non autorisé - Seuls les administrateurs peuvent supprimer des utilisateurs']);
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
    
    // Vérifier que l'utilisateur existe
    $check_query = "SELECT id, name, role FROM users WHERE id = ?";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$user_id]);
    $user = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Utilisateur non trouvé']);
        exit;
    }
    
    // Empêcher la suppression de l'admin connecté
    if ($user_id == $_SESSION['admin_id']) {
        http_response_code(400);
        echo json_encode(['error' => 'Vous ne pouvez pas supprimer votre propre compte']);
        exit;
    }
    
    // Empêcher la suppression d'autres admins (sécurité)
    if ($user['role'] === 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Vous ne pouvez pas supprimer un autre administrateur']);
        exit;
    }
    
    // Supprimer l'utilisateur (les contraintes CASCADE supprimeront automatiquement les données associées)
    $delete_query = "DELETE FROM users WHERE id = ?";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->execute([$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => "L'utilisateur {$user['name']} a été supprimé avec succès"
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?> 