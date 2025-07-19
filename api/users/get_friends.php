<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_GET['user_id'])) {
    echo json_encode(["success" => false, "error" => "user_id manquant."]); exit;
}
$user_id = (int)$_GET['user_id'];

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sql = "SELECT u.id, u.prenom, u.nom, u.avatar
            FROM users u
            JOIN friendships f ON (
                (f.user_id = :user_id AND f.friend_id = u.id)
                OR (f.friend_id = :user_id AND f.user_id = u.id)
            )
            WHERE f.status = 'accepted' AND u.id != :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "friends" => $friends]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de la récupération des amis."]);
} 