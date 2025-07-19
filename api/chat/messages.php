<?php
header('Content-Type: application/json');
require_once '../../config.php';

$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : null;
    if (!$conversation_id) {
        echo json_encode(['success' => false, 'error' => 'conversation_id manquant']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT m.id, m.sender_id, u.prenom, u.nom, u.avatar, m.content, m.created_at
                           FROM messages m
                           JOIN users u ON m.sender_id = u.id
                           WHERE m.conversation_id = ?
                           ORDER BY m.created_at ASC");
    $stmt->execute([$conversation_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $conversation_id = isset($input['conversation_id']) ? (int)$input['conversation_id'] : null;
    $sender_id = isset($input['sender_id']) ? (int)$input['sender_id'] : null;
    $content = trim($input['content'] ?? '');

    if (!$conversation_id || !$sender_id || !$content) {
        echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$conversation_id, $sender_id, $content]);
    echo json_encode(['success' => true]);
    exit;
}
