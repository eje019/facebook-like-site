<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_POST['user_id']) || empty($_POST['other_user_id'])) {
    echo json_encode(["success" => false, "error" => "Paramètres manquants."]); exit;
}
$user_id = (int)$_POST['user_id'];
$other_id = (int)$_POST['other_user_id'];

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('DELETE FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)');
    $stmt->execute([$user_id, $other_id, $other_id, $user_id]);
    echo json_encode(["success" => true]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de la suppression."]);
} 