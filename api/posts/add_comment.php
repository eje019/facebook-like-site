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
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
    $stmt->execute([$post_id, $user_id, $content]);
    $comment_id = $pdo->lastInsertId();
    
    // Récupère le commentaire créé avec les infos utilisateur
    $stmt = $pdo->prepare('SELECT c.id, c.user_id, u.prenom, u.nom, u.avatar, c.content, c.created_at
                          FROM comments c
                          JOIN users u ON c.user_id = u.id
                          WHERE c.id = ?');
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => true, "comment" => $comment]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de l'ajout du commentaire."]);
} 