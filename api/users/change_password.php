<?php
header('Content-Type: application/json');
require_once '../../config.php';

$fields = ['user_id','current_password','new_password','confirm_password'];
foreach($fields as $f) {
    if(empty($_POST[$f])) {
        echo json_encode(["success" => false, "error" => "Champs manquants."]); exit;
    }
}
$user_id = (int)$_POST['user_id'];
$current = $_POST['current_password'];
$new = $_POST['new_password'];
$confirm = $_POST['confirm_password'];

if($new !== $confirm) {
    echo json_encode(["success" => false, "error" => "Les mots de passe ne correspondent pas."]); exit;
}
if(strlen($new) < 6) {
    echo json_encode(["success" => false, "error" => "Le mot de passe doit contenir au moins 6 caractères."]); exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id=?');
    $stmt->execute([$user_id]);
    $hash = $stmt->fetchColumn();
    if(!$hash || !password_verify($current, $hash)) {
        echo json_encode(["success" => false, "error" => "Mot de passe actuel incorrect."]); exit;
    }
    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
    $stmt->execute([$newHash, $user_id]);
    echo json_encode(["success" => true]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors du changement de mot de passe."]);
} 