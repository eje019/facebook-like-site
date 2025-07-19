<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_GET['user_id'])) {
    echo json_encode(["success" => false, "error" => "user_id manquant."]); exit;
}
$user_id = (int)$_GET['user_id'];
$current_user_id = isset($_GET['current_user_id']) ? (int)$_GET['current_user_id'] : null;

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Récupère les infos de l'utilisateur
    $sql = "SELECT id, prenom, nom, email, avatar FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$user) {
        echo json_encode(["success" => false, "error" => "Utilisateur introuvable."]); exit;
    }
    
    // Statut d'amitié si on a un utilisateur connecté
    if($current_user_id && $current_user_id != $user_id) {
        $stmt2 = $pdo->prepare('SELECT status, user_id, friend_id FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)');
        $stmt2->execute([$current_user_id, $user_id, $user_id, $current_user_id]);
        $friendship = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if(!$friendship) {
            $user['friend_status'] = 'none';
        } else if($friendship['status'] === 'accepted') {
            $user['friend_status'] = 'friend';
        } else if($friendship['status'] === 'pending' && $friendship['user_id'] == $current_user_id) {
            $user['friend_status'] = 'pending_sent';
        } else if($friendship['status'] === 'pending' && $friendship['friend_id'] == $current_user_id) {
            $user['friend_status'] = 'pending_received';
        } else if($friendship['status'] === 'refused') {
            $user['friend_status'] = 'refused';
        } else {
            $user['friend_status'] = 'none';
        }
    } else {
        $user['friend_status'] = 'self';
    }
    
    echo json_encode(["success" => true, "user" => $user]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de la récupération de l'utilisateur."]);
} 