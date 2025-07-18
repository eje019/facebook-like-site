<?php
header('Content-Type: application/json');
require_once '../../config.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = ["success" => false];

if(empty($_POST['email'])) {
    $response['error'] = 'Veuillez saisir votre adresse email.';
    echo json_encode($response); exit;
}

$email = $_POST['email'];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch(Exception $e) {
    $response['error'] = 'Erreur de connexion à la base de données.';
    echo json_encode($response); exit;
}

$stmt = $pdo->prepare('SELECT id, prenom FROM users WHERE email = ? AND is_active = 1');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user) {
    $response['error'] = 'Aucun compte actif trouvé avec cet email.';
    echo json_encode($response); exit;
}

// Générer un token de réinitialisation
$token = bin2hex(random_bytes(32));
$stmt = $pdo->prepare('UPDATE users SET reset_token = ? WHERE id = ?');
$stmt->execute([$token, $user['id']]);

// Envoi de l'email HTML avec PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'j20426048@gmail.com';
    $mail->Password = 'faaz gmof ripm knes';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('j20426048@gmail.com', 'Facebook-like');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Réinitialisation de votre mot de passe';

    // Correction du lien pour pointer vers la page HTML côté client
    $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . '/facebook-like-site/vues/clients/reset.html?token=' . $token;
    $prenom = htmlspecialchars($user['prenom']);
    $logo = 'https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg';
    $mail->Body = "<div style='background:#f0f2f5;padding:40px 0;'>
        <div style='max-width:480px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);padding:32px 24px;text-align:center;'>
            <img src='$logo' alt='Facebook-like' style='width:60px;margin-bottom:18px;'>
            <h2 style='color:#1877f2;margin-bottom:8px;'>Bonjour $prenom,</h2>
            <p style='color:#444;font-size:1.08em;margin-bottom:24px;'>Vous avez demandé à réinitialiser votre mot de passe.<br>Pour choisir un nouveau mot de passe, cliquez sur le bouton ci-dessous :</p>
            <a href='$reset_link' style='display:inline-block;background:#1877f2;color:#fff;padding:14px 32px;border-radius:6px;font-size:1.1em;text-decoration:none;font-weight:bold;margin-bottom:18px;'>Réinitialiser mon mot de passe</a>
            <p style='color:#888;font-size:0.98em;margin-top:30px;'>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.</p>
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