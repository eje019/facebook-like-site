<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_POST['user_id']) || empty($_POST['post_id'])) {
    echo json_encode(["success" => false, "error" => "Paramètres manquants."]); exit;
}

$user_id = (int)$_POST['user_id'];
$post_id = (int)$_POST['post_id'];

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Récupère le post original
    $stmt = $pdo->prepare('SELECT p.*, u.prenom, u.nom FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?');
    $stmt->execute([$post_id]);
    $originalPost = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$originalPost) {
        echo json_encode(["success" => false, "error" => "Post introuvable."]); exit;
    }
    
    // Crée le contenu du partage
    $shareContent = "📤 Partagé depuis ${originalPost['prenom']} ${originalPost['nom']}\n\n" . $originalPost['content'];
    
    // Insère le nouveau post (partage)
    $stmt = $pdo->prepare('INSERT INTO posts (user_id, content, image, shared_from_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user_id, $shareContent, $originalPost['image'], $post_id]);
    
    echo json_encode(["success" => true, "message" => "Post partagé avec succès."]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors du partage."]);
} 