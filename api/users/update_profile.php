<?php
header('Content-Type: application/json');
require_once '../../config.php';

$fields = ['user_id','prenom','nom','email'];
foreach($fields as $f) {
    if(empty($_POST[$f])) {
        echo json_encode(["success" => false, "error" => "Champs manquants."]); exit;
    }
}
$user_id = (int)$_POST['user_id'];
$prenom = trim($_POST['prenom']);
$nom = trim($_POST['nom']);
$email = trim($_POST['email']);

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Email invalide."]); exit;
}

// Mot de passe (optionnel)
$password_sql = '';
$password_params = [];
if (!empty($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $password_sql = ', password=?';
    $password_params[] = $hash;
}

// Avatar (optionnel)
$avatar_sql = '';
$avatar_param = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $allowed)) {
        echo json_encode(["success" => false, "error" => "Format d'image non autorisé."]); exit;
    }
    if ($_FILES['avatar']['size'] > 2*1024*1024) {
        echo json_encode(["success" => false, "error" => "Image trop lourde (2Mo max)."]); exit;
    }
    $filename = 'avatar_' . uniqid() . '.' . $ext;
    $dest = '../../assets/images/' . $filename;
    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
        echo json_encode(["success" => false, "error" => "Erreur upload image."]); exit;
    }
    $avatar_sql = ', avatar=?';
    $avatar_param = $filename;
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sql = 'UPDATE users SET prenom=?, nom=?, email=?' . $password_sql . $avatar_sql . ' WHERE id=?';
    $params = [$prenom, $nom, $email];
    if ($password_sql) $params[] = $hash;
    if ($avatar_sql) $params[] = $avatar_param;
    $params[] = $user_id;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    // Récupérer les nouvelles infos utilisateur
    $stmt2 = $pdo->prepare('SELECT id, prenom, nom, email, avatar FROM users WHERE id=?');
    $stmt2->execute([$user_id]);
    $user = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "user" => $user]);
} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => "Erreur lors de la mise à jour du profil."]);
} 