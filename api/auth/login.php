<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../../config.php';

$response = ["success" => false];

// On utilise $_POST car le JS envoie du FormData
if(empty($_POST['email']) || empty($_POST['password'])) {
    $response['error'] = 'Veuillez remplir tous les champs.';
    echo json_encode($response); exit;
}

try {
    // CORRECTION ICI : on construit le DSN à partir des constantes de config
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch(Exception $e) {
    $response['error'] = 'Erreur de connexion à la base de données.';
    echo json_encode($response); exit;
}

$stmt = $pdo->prepare('SELECT id, prenom, nom, email, password, is_active, avatar, role FROM users WHERE email = ?');
$stmt->execute([$_POST['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user) {
    $response['error'] = 'Identifiants incorrects.';
    echo json_encode($response); exit;
}

// Si le champ is_active existe dans ta table users, garde ce bloc.
// Sinon, tu peux le commenter ou l'enlever.
if(isset($user['is_active']) && !$user['is_active']) {
    $response['error'] = 'Votre compte n\'est pas encore activé. Vérifiez votre email.';
    echo json_encode($response); exit;
}

if(!password_verify($_POST['password'], $user['password'])) {
    $response['error'] = 'Identifiants incorrects.';
    echo json_encode($response); exit;
}

// On ne retourne pas le mot de passe !
unset($user['password']);
$response['success'] = true;
$response['user'] = $user;
echo json_encode($response); 