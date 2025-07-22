<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../../config.php';

$response = ["success" => false];

// Vérification des champs requis
if (empty($_POST['email']) || empty($_POST['password'])) {
    $response['error'] = 'Veuillez remplir tous les champs.';
    echo json_encode($response); exit;
}

// Connexion avec mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Vérifier la connexion
if ($conn->connect_error) {
    $response['error'] = 'Erreur de connexion à la base de données.';
    echo json_encode($response); exit;
}

// Préparer la requête
$stmt = $conn->prepare("SELECT id, prenom, nom, email, password, is_active, avatar, role FROM users WHERE email = ?");
$stmt->bind_param("s", $_POST['email']);
$stmt->execute();

// Récupérer le résultat
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Vérification des identifiants
if (!$user || !password_verify($_POST['password'], $user['password'])) {
    $response['error'] = 'Identifiants incorrects.';
    echo json_encode($response); exit;
}

// Vérifier si le compte est actif
if (isset($user['is_active']) && !$user['is_active']) {
    $response['error'] = 'Votre compte n\'est pas encore activé. Vérifiez votre email.';
    echo json_encode($response); exit;
}

// Ne pas renvoyer le mot de passe
unset($user['password']);

$response['success'] = true;
$response['user'] = $user;

echo json_encode($response);
