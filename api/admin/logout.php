<?php
session_start();
header('Content-Type: application/json);
header('Access-Control-Allow-Origin: *);
header('Access-Control-Allow-Methods: POST);
header('Access-Control-Allow-Headers: Content-Type');

// Détruire la session admin
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);
unset($_SESSION[admin_name']);
unset($_SESSION['is_admin']);

// Détruire complètement la session
session_destroy();

echo json_encode(success' => true,
  message' => Déconnexion réussie'
]);
?> 