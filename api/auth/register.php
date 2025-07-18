<?php
header('Content-Type: application/json');
require_once '../../config.php';
require '../../vendor/autoload.php'; // PHPMailer

$response = ["success" => false];

// 1. Vérification des champs
$fields = ['prenom', 'nom', 'email', 'password', 'password_confirm', 'jour', 'mois', 'annee', 'genre'];
foreach($fields as $field) {
    if(empty($_POST[$field])) {
        $response['error'] = 'Tous les champs sont obligatoires.';
        echo json_encode($response); exit;
    }
}

// 2. Validation email
if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $response['error'] = 'Email invalide.';
    echo json_encode($response); exit;
}

// 3. Vérification mot de passe
if($_POST['password'] !== $_POST['password_confirm']) {
    $response['error'] = 'Les mots de passe ne correspondent pas.';
    echo json_encode($response); exit;
}
if(strlen($_POST['password']) < 6) {
    $response['error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
    echo json_encode($response); exit;
}

// 4. Validation date de naissance
$jour = (int)$_POST['jour'];
$mois = (int)$_POST['mois'];
$annee = (int)$_POST['annee'];
if(!checkdate($mois, $jour, $annee)) {
    $response['error'] = 'Date de naissance invalide.';
    echo json_encode($response); exit;
}
$date_naissance = sprintf('%04d-%02d-%02d', $annee, $mois, $jour);

// 5. Validation genre
$genres_valides = ['Femme', 'Homme', 'Personnalisé'];
if(!in_array($_POST['genre'], $genres_valides)) {
    $response['error'] = 'Genre invalide.';
    echo json_encode($response); exit;
}

// 6. Connexion à la BDD
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch(Exception $e) {
    $response['error'] = 'Erreur de connexion à la base de données.';
    echo json_encode($response); exit;
}

// 7. Vérifier si l'email existe déjà
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$_POST['email']]);
if($stmt->fetch()) {
    $response['error'] = 'Cet email est déjà utilisé.';
    echo json_encode($response); exit;
}

// 8. Générer un token de confirmation
$token = bin2hex(random_bytes(32));

// 9. Enregistrer l'utilisateur (statut: inactif)
$hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (prenom, nom, email, password, confirmation_token, is_active, date_naissance, genre) VALUES (?, ?, ?, ?, ?, 0, ?, ?)');
$stmt->execute([
    htmlspecialchars($_POST['prenom']),
    htmlspecialchars($_POST['nom']),
    $_POST['email'],
    $hash,
    $token,
    $date_naissance,
    $_POST['genre']
]);

// 10. Envoyer l'email de confirmation avec PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // Paramètres SMTP Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'j20426048@gmail.com';
    $mail->Password = 'faaz gmof ripm knes';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('j20426048@gmail.com', 'Facebook-like');
    $mail->addAddress($_POST['email']);
    $mail->isHTML(true);
    $mail->Subject = 'Confirmez votre inscription';

    $confirm_link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/confirm_email.php?token=$token";
    $prenom = htmlspecialchars($_POST['prenom']);
    $logo = 'https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg';
    $mail->Body = "<div style='background:#f0f2f5;padding:40px 0;'>
        <div style='max-width:480px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);padding:32px 24px;text-align:center;'>
            <img src='$logo' alt='Facebook-like' style='width:60px;margin-bottom:18px;'>
            <h2 style='color:#1877f2;margin-bottom:8px;'>Bienvenue sur Facebook-like, $prenom !</h2>
            <p style='color:#444;font-size:1.08em;margin-bottom:24px;'>Merci de vous être inscrit sur notre réseau social.<br>Pour activer votre compte, cliquez sur le bouton ci-dessous :</p>
            <a href='$confirm_link' style='display:inline-block;background:#1877f2;color:#fff;padding:14px 32px;border-radius:6px;font-size:1.1em;text-decoration:none;font-weight:bold;margin-bottom:18px;'>Confirmer mon compte</a>
            <p style='color:#888;font-size:0.98em;margin-top:30px;'>Si vous n'êtes pas à l'origine de cette inscription, ignorez simplement cet email.</p>
            <hr style='margin:32px 0 18px 0;border:none;border-top:1px solid #eee;'>
            <div style='color:#aaa;font-size:0.95em;'>© " . date('Y') . " Facebook-like. Tous droits réservés.</div>
        </div>
    </div>";

    $mail->send();
    $response['success'] = true;
} catch (Exception $e) {
    $response['error'] = "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
}

echo json_encode($response); 