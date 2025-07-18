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
    $sql = "SELECT id, prenom, nom, avatar FROM users WHERE id != ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($users as &$u) {
        // Statut d'amitié
        $stmt2 = $pdo->prepare('SELECT status, user_id, friend_id FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)');
        $stmt2->execute([$user_id, $u['id'], $u['id'], $user_id]);
        $f = $stmt2->fetch(PDO::FETCH_ASSOC);
        if(!$f) {
            $u['friend_status'] = 'none';
        } else if($f['status'] === 'accepted') {
            $u['friend_status'] = 'friend';
        } else if($f['status'] === 'pending' && $f['user_id'] == $user_id) {
            $u['friend_status'] = 'pending_sent';
        } else if($f['status'] === 'pending' && $f['friend_id'] == $user_id) {
            $u['friend_status'] = 'pending_received';
        } else if($f['status'] === 'refused') {
            $u['friend_status'] = 'refused';
        } else {
            $u['friend_status'] = 'none';
        }
    }
    echo json_encode(["success" => true, "users" => $users]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de la récupération des utilisateurs."]);
} 