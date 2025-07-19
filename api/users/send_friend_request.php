<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_POST['user_id']) || empty($_POST['friend_id'])) {
    echo json_encode(["success" => false, "error" => "Paramètres manquants."]); exit;
}
$user_id = (int)$_POST['user_id'];
$friend_id = (int)$_POST['friend_id'];

if($user_id == $friend_id) {
    echo json_encode(["success" => false, "error" => "Impossible de s'ajouter soi-même."]); exit;
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    // Vérifie si une relation existe déjà
    $stmt = $pdo->prepare('SELECT id, status FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)');
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);
    if($f) {
        if($f['status'] === 'pending') {
            echo json_encode(["success" => false, "error" => "Invitation déjà en attente."]); exit;
        } else if($f['status'] === 'accepted') {
            echo json_encode(["success" => false, "error" => "Vous êtes déjà amis."]); exit;
        }
    }
    // Nouvelle invitation
    $stmt = $pdo->prepare('INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, "pending")');
    $stmt->execute([$user_id, $friend_id]);
    echo json_encode(["success" => true]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de l'envoi de l'invitation."]);
} 