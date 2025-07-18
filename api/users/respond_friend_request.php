<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_POST['user_id']) || empty($_POST['friend_id']) || empty($_POST['action'])) {
    echo json_encode(["success" => false, "error" => "Paramètres manquants."]); exit;
}
$user_id = (int)$_POST['user_id'];
$friend_id = (int)$_POST['friend_id'];
$action = $_POST['action'];

if(!in_array($action, ['accept','refuse'])) {
    echo json_encode(["success" => false, "error" => "Action invalide."]); exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('UPDATE friendships SET status = ? WHERE user_id = ? AND friend_id = ? AND status = "pending"');
    $stmt->execute([$action === 'accept' ? 'accepted' : 'refused', $friend_id, $user_id]);
    if($stmt->rowCount() > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Aucune invitation trouvée."]);
    }
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors du traitement."]);
} 