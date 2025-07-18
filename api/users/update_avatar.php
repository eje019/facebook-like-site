<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_POST['user_id']) || !isset($_FILES['avatar'])) {
    echo json_encode(["success" => false, "error" => "Paramètres manquants."]); exit;
}
$user_id = (int)$_POST['user_id'];
$file = $_FILES['avatar'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if(!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
    echo json_encode(["success" => false, "error" => "Format d'image non supporté."]); exit;
}
$avatarName = 'avatar_' . $user_id . '_' . uniqid() . '.' . $ext;
$dest = '../../assets/images/' . $avatarName;
if(!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(["success" => false, "error" => "Erreur upload."]); exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('UPDATE users SET avatar=? WHERE id=?');
    $stmt->execute([$avatarName, $user_id]);
    echo json_encode(["success" => true, "avatar" => $avatarName]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de la mise à jour de l'avatar."]);
} 