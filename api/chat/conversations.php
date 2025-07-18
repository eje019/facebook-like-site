<?php
session_start();
header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté (session PHP ou sessionStorage)
$user_id = null;

// Méthode 1: Session PHP
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// Méthode 2: SessionStorage (via header)
if (!$user_id) {
    $headers = getallheaders();
    if (isset($headers['X-User-Id'])) {
        $user_id = $headers['X-User-Id'];
    }
}

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$other_user_id = $input['user_id'] ?? null;

if (!$other_user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID utilisateur requis']);
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

    // Vérifier si une conversation existe déjà
    $check_query = "
        SELECT c.id 
        FROM conversations c
        INNER JOIN conversation_participants cp1 ON c.id = cp1.conversation_id
        INNER JOIN conversation_participants cp2 ON c.id = cp2.conversation_id
        WHERE cp1.user_id = ? AND cp2.user_id = ?
        LIMIT 1
    ";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$user_id, $other_user_id]);
    $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $conversation_id = $existing['id'];
    } else {
        // Créer une nouvelle conversation
        $pdo->beginTransaction();
        
        $insert_conversation = "INSERT INTO conversations (created_at) VALUES (NOW())";
        $pdo->prepare($insert_conversation)->execute();
        $conversation_id = $pdo->lastInsertId();
        
        $insert_participant = "INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($insert_participant);
        $stmt->execute([$conversation_id, $user_id]);
        $stmt->execute([$conversation_id, $other_user_id]);
        
        $pdo->commit();
    }
    
    // Récupérer les infos de l'autre utilisateur
    $user_query = "SELECT id, prenom, nom, username, avatar, is_online FROM users WHERE id = ?";
    $user_stmt = $pdo->prepare($user_query);
    $user_stmt->execute([$other_user_id]);
    $other_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'conversation' => [
            'id' => $conversation_id,
            'user' => $other_user
        ]
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
} 