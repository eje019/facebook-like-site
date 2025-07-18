<?php
header('Content-Type: application/json');
require_once '../../config.php';

$fields = ['user_id','prenom','nom','email','date_naissance','genre'];
foreach($fields as $f) {
    if(empty($_POST[$f])) {
        echo json_encode(["success" => false, "error" => "Champs manquants."]); exit;
    }
}
$user_id = (int)$_POST['user_id'];
$prenom = trim($_POST['prenom']);
$nom = trim($_POST['nom']);
$email = trim($_POST['email']);
$date_naissance = $_POST['date_naissance'];
$genre = $_POST['genre'];

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Email invalide."]); exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('UPDATE users SET prenom=?, nom=?, email=?, date_naissance=?, genre=? WHERE id=?');
    $stmt->execute([$prenom, $nom, $email, $date_naissance, $genre, $user_id]);
    echo json_encode(["success" => true]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de la mise à jour du profil."]);
} 