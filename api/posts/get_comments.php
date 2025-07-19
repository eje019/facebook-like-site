<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_GET['post_id'])) {
    echo json_encode(["success" => false, "error" => "post_id manquant."]); exit;
}
$post_id = (int)$_GET['post_id'];

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sql = "SELECT c.id, c.user_id, u.prenom, u.nom, u.avatar, c.content, c.created_at
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "comments" => $comments]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de la récupération des commentaires."]);
} 