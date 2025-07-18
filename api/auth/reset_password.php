<?php
header('Content-Type: application/json');
require_once '../../config.php';

$response = ["success" => false];

if(empty($_POST['token'])) {
    $response['error'] = 'Lien invalide ou expiré.';
    echo json_encode($response); exit;
}
if(empty($_POST['password']) || empty($_POST['password_confirm'])) {
    $response['error'] = 'Tous les champs sont obligatoires.';
    echo json_encode($response); exit;
}

if($_POST['password'] !== $_POST['password_confirm']) {
    $response['error'] = 'Les mots de passe ne correspondent pas.';
    echo json_encode($response); exit;
}
if(strlen($_POST['password']) < 6) {
    $response['error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
    echo json_encode($response); exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch(Exception $e) {
    $response['error'] = 'Erreur de connexion à la base de données.';
    echo json_encode($response); exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE reset_token = ?');
$stmt->execute([$_POST['token']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user) {
    $response['error'] = 'Lien invalide ou expiré.';
    echo json_encode($response); exit;
}

$hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
$stmt = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL WHERE id = ?');
$stmt->execute([$hash, $user['id']]);

$response['success'] = true;
echo json_encode($response); 