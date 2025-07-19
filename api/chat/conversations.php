<?php
header('Content-Type: application/json');
require_once '../../config.php';

$input = json_decode(file_get_contents('php://input'), true);
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$other_user_id = isset($input['other_user_id']) ? (int)$input['other_user_id'] : null;

if (!$user_id || !$other_user_id) {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    // Cherche une conversation existante
    $sql = "SELECT c.id FROM conversations c
            JOIN conversation_participants cp1 ON c.id = cp1.conversation_id
            JOIN conversation_participants cp2 ON c.id = cp2.conversation_id
            WHERE cp1.user_id = ? AND cp2.user_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $other_user_id]);
    $conv = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($conv) {
        $conversation_id = $conv['id'];
    } else {
        // Crée une nouvelle conversation
        $pdo->beginTransaction();
        $pdo->exec("INSERT INTO conversations () VALUES ()");
        $conversation_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?), (?, ?)");
        $stmt->execute([$conversation_id, $user_id, $conversation_id, $other_user_id]);
        $pdo->commit();
    }

    echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
