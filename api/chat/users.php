<?php
header('Content-Type: application/json');
require_once '../../config.php';

if (empty($_GET['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'user_id manquant']);
    exit;
}
$user_id = (int)$_GET['user_id'];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare("SELECT id, prenom, nom, avatar FROM users WHERE id != ?");
    $stmt->execute([$user_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
