<?php
header('Content-Type: application/json');
require_once '../../config.php';

$response = ["success" => false];

if(empty($_POST['user_id']) || empty($_POST['content'])) {
    $response['error'] = 'Texte obligatoire.';
    echo json_encode($response); exit;
}

$content = trim($_POST['content']);
if(strlen($content) < 1) {
    $response['error'] = 'Le texte est trop court.';
    echo json_encode($response); exit;
}

$imageName = null;
if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if(!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        $response['error'] = 'Format d\'image non supporté.';
        echo json_encode($response); exit;
    }
    $imageName = uniqid('img_') . '.' . $ext;
    move_uploaded_file($_FILES['image']['tmp_name'], '../../assets/images/' . $imageName);
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)');
    $stmt->execute([$_POST['user_id'], $content, $imageName]);
    $response['success'] = true;
} catch(Exception $e) {
    $response['error'] = 'Erreur lors de la publication.';
}

echo json_encode($response); 