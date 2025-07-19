<?php
// create_admin.php
require_once 'config.php';


$email = 'nouveladmin@example.com'; 
$password = 'AdminTest123!';        
$prenom = 'Super';                  
$nom = 'Admin';                     
$avatar = null;                     

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Vérifie si l'admin existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "Un utilisateur avec cet email existe déjà.<br>";
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO users (prenom, nom, email, password, is_active, role, is_moderator, avatar)
        VALUES (?, ?, ?, ?, 1, 'admin', 0, ?)");
    $stmt->execute([$prenom, $nom, $email, $hash, $avatar]);

    echo "Compte admin créé avec succès !<br>Email : $email<br>Mot de passe : $password";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
