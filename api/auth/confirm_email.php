<?php
require_once '../../config.php';

if(empty($_GET['token'])) {
    die('Lien invalide.');
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch(Exception $e) {
    die('Erreur de connexion à la base de données.');
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE confirmation_token = ? AND is_active = 0');
$stmt->execute([$_GET['token']]);
$user = $stmt->fetch();

if($user) {
    $stmt = $pdo->prepare('UPDATE users SET is_active = 1, confirmation_token = NULL WHERE id = ?');
    $stmt->execute([$user['id']]);
    echo '<h2>Votre compte a été activé ! Vous pouvez maintenant vous connecter.</h2>';
} else {
    echo '<h2>Lien invalide ou compte déjà activé.</h2>';
} 