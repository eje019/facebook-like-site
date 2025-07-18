<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_POST['post_id']) || empty($_POST['user_id']) || empty($_POST['content'])) {
    echo json_encode(["success" => false, "error" => "Champs obligatoires manquants."]); exit;
}
$post_id = (int)$_POST['post_id'];
$user_id = (int)$_POST['user_id'];
$content = trim($_POST['content']);
if(strlen($content) < 1) {
    echo json_encode(["success" => false, "error" => "Commentaire trop court."]); exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
    $stmt->execute([$post_id, $user_id, $content]);
    echo json_encode(["success" => true]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de l'ajout du commentaire."]);
} 