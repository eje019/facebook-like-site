<?php
session_start();
header('Content-Type: application/json');

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

if (!isset($_POST['conversation_id'], $_POST['sender_id']) || !isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
    exit;
}

$conversation_id = intval($_POST['conversation_id']);
$sender_id = intval($_POST['sender_id']);

// Vérifier le fichier image
$img = $_FILES['image'];
if ($img['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Erreur upload image']);
    exit;
}
$ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Format non autorisé']);
    exit;
}
if ($img['size'] > 2 * 1024 * 1024) { // 2 Mo max
    echo json_encode(['success' => false, 'error' => 'Image trop lourde']);
    exit;
}
$filename = 'chatimg_' . uniqid() . '.' . $ext;
$dest = '../../assets/images/' . $filename;
if (!move_uploaded_file($img['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'error' => "Erreur lors de l'enregistrement"]);
    exit;
}

// Enregistrer le message dans la BDD
require_once '../../config.php';
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
$pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$sql = "INSERT INTO messages (conversation_id, sender_id, content, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $pdo->prepare($sql);
$imgPath = 'assets/images/' . $filename;
$stmt->execute([$conversation_id, $sender_id, '<img src="' . $imgPath . '" alt="Image" />']);

echo json_encode(['success' => true, 'image' => $imgPath]); 