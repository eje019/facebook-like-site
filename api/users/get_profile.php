<?php
header('Content-Type: application/json');
require_once '../../config.php';

if(empty($_GET['user_id'])) {
    echo json_encode(["success" => false, "error" => "user_id manquant."]); exit;
}
$user_id = (int)$_GET['user_id'];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('SELECT id, prenom, nom, email, date_naissance, genre, avatar FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    if($profile) {
        echo json_encode(["success" => true, "profile" => $profile]);
    } else {
        echo json_encode(["success" => false, "error" => "Utilisateur introuvable."]);
    }
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de la récupération du profil."]);
} 