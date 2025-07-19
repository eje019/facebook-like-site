<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], ['admin', 'moderator'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
    exit;
}

require_once '../../config.php';
$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$input = json_decode(file_get_contents('php://input'), true);
$comment_id = $input['comment_id'] ?? null;
if (!$comment_id || !is_numeric($comment_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID commentaire invalide']);
    exit;
}

try {
    // Vérifier que le commentaire existe
    $stmt = $pdo->prepare('SELECT id FROM comments WHERE id = ?');
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$comment) {
        echo json_encode(['success' => false, 'error' => 'Commentaire introuvable']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
    $stmt->execute([$comment_id]);
    echo json_encode(['success' => true, 'message' => 'Commentaire supprimé avec succès']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
} 