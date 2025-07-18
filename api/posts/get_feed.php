<?php
header('Content-Type: application/json');
require_once '../../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur de connexion à la base de données."]); exit;
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$only_user = isset($_GET['only_user']) && $_GET['only_user'] == '1';

if ($only_user && $user_id) {
    $sql = "SELECT p.id, p.content, p.image, p.created_at, p.shared_from_id, u.id AS user_id, u.prenom, u.nom, u.avatar
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC
            LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
} else {
    $sql = "SELECT p.id, p.content, p.image, p.created_at, p.shared_from_id, u.id AS user_id, u.prenom, u.nom, u.avatar
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 50";
    $stmt = $pdo->query($sql);
}
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($posts as &$post) {
    // Nombre de likes
    $stmt2 = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ? AND type = "like"');
    $stmt2->execute([$post['id']]);
    $post['likes'] = (int)$stmt2->fetchColumn();
    
    // Nombre de dislikes
    $stmt2 = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ? AND type = "dislike"');
    $stmt2->execute([$post['id']]);
    $post['dislikes'] = (int)$stmt2->fetchColumn();
    
    // Nombre de commentaires
    $stmt2 = $pdo->prepare('SELECT COUNT(*) FROM comments WHERE post_id = ?');
    $stmt2->execute([$post['id']]);
    $post['comments'] = (int)$stmt2->fetchColumn();
    
    // Like/dislike de l'utilisateur courant
    if($user_id) {
        $stmt2 = $pdo->prepare('SELECT type FROM likes WHERE post_id = ? AND user_id = ?');
        $stmt2->execute([$post['id'], $user_id]);
        $post['user_like'] = $stmt2->fetchColumn() ?: null;
    } else {
        $post['user_like'] = null;
    }
    
    // Informations sur le post partagé si applicable
    if($post['shared_from_id']) {
        $stmt2 = $pdo->prepare('SELECT p.content, p.image, u.prenom, u.nom FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?');
        $stmt2->execute([$post['shared_from_id']]);
        $sharedPost = $stmt2->fetch(PDO::FETCH_ASSOC);
        if($sharedPost) {
            $post['shared_post'] = $sharedPost;
        }
    }
}

echo json_encode(["success" => true, "posts" => $posts]); 