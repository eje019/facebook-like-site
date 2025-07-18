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
    $headers = getallheaders();
    if (isset($headers['X-User-Id'])) {
        $user_id = $headers['X-User-Id'];
    }
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

$conversation_id = $_GET['conversation_id'] ?? null;

if (!$conversation_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID conversation requis']);
    exit;
}

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

    // Vérifier que l'utilisateur fait partie de cette conversation
    $check_query = "SELECT COUNT(*) FROM conversation_participants WHERE conversation_id = ? AND user_id = ?";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$conversation_id, $user_id]);
    if ($check_stmt->fetchColumn() == 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Récupérer les messages
        $query = "
            SELECT id, content, sender_id, created_at
            FROM messages 
            WHERE conversation_id = ?
            ORDER BY created_at ASC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$conversation_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Envoyer un message
        $input = json_decode(file_get_contents('php://input'), true);
        $content = trim($input['content'] ?? '');
        
        if (empty($content)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Message vide']);
            exit;
        }
        
        // Insérer le message
        $insert_query = "INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->execute([$conversation_id, $user_id, $content]);
        $message_id = $pdo->lastInsertId();
        
        // Mettre à jour la conversation
        $update_query = "UPDATE conversations SET last_message = ?, last_message_time = NOW() WHERE id = ?";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([$content, $conversation_id]);
        
        // Récupérer le message créé
        $get_query = "SELECT id, content, sender_id, created_at FROM messages WHERE id = ?";
        $get_stmt = $pdo->prepare($get_query);
        $get_stmt->execute([$message_id]);
        $message = $get_stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
} 