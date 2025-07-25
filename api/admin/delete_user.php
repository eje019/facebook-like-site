<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
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
$user_id = $input['user_id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID utilisateur invalide']);
    exit;
}

try {
    // Vérifier que l'utilisateur existe
    $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Utilisateur introuvable']);
        exit;
    }
    // Empêcher la suppression de l'admin connecté
    if ($user_id == $_SESSION['admin_id']) {
        echo json_encode(['success' => false, 'error' => 'Impossible de supprimer votre propre compte']);
        exit;
    }
    // Empêcher la suppression d'autres admins
    if ($user['role'] === 'admin') {
        echo json_encode(['success' => false, 'error' => 'Impossible de supprimer un autre administrateur']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
} 