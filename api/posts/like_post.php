<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_POST['user_id']) || empty($_POST['post_id']) || empty($_POST['type'])) {
    echo json_encode(["success" => false, "error" => "Paramètres manquants."]); exit;
}

$user_id = (int)$_POST['user_id'];
$post_id = (int)$_POST['post_id'];
$type = $_POST['type'] === 'dislike' ? 'dislike' : 'like';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    // Vérifie si l'utilisateur a déjà liké/disliké ce post
    $stmt = $pdo->prepare('SELECT id, type FROM likes WHERE user_id = ? AND post_id = ?');
    $stmt->execute([$user_id, $post_id]);
    $like = $stmt->fetch(PDO::FETCH_ASSOC);
    if($like) {
        if($like['type'] === $type) {
            // Si déjà ce type, on supprime (toggle off)
            $pdo->prepare('DELETE FROM likes WHERE id = ?')->execute([$like['id']]);
            $action = 'removed';
        } else {
            // Sinon, on met à jour le type
            $pdo->prepare('UPDATE likes SET type = ? WHERE id = ?')->execute([$type, $like['id']]);
            $action = 'updated';
        }
    } else {
        // Nouveau like/dislike
        $pdo->prepare('INSERT INTO likes (user_id, post_id, type) VALUES (?, ?, ?)')->execute([$user_id, $post_id, $type]);
        $action = 'added';
    }
    // Retourne les nouveaux compteurs
    $likes = $pdo->query("SELECT COUNT(*) FROM likes WHERE post_id = $post_id AND type = 'like'")->fetchColumn();
    $dislikes = $pdo->query("SELECT COUNT(*) FROM likes WHERE post_id = $post_id AND type = 'dislike'")->fetchColumn();
    echo json_encode(["success" => true, "action" => $action, "likes" => (int)$likes, "dislikes" => (int)$dislikes]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors du traitement."]);
} 