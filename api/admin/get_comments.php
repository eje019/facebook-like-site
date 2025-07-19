<?php
// --- Initialisation de la session et des entêtes ---
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// --- Vérification des droits d'accès (admin ou modérateur) ---
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], ['admin', 'moderator'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
    exit;
}

// --- Connexion à la base de données ---
require_once '../../config.php';
$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// --- Gestion de la pagination et de la recherche ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$per_page = 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $per_page;

// --- Construction de la clause WHERE pour la recherche ---
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR p.content LIKE :search OR c.content LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// --- Compter le nombre total de commentaires (pour la pagination) ---
$count_sql = "SELECT COUNT(*) as total FROM comments c JOIN users u ON c.user_id = u.id JOIN posts p ON c.post_id = p.id $where";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total = (int)($count_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// --- Récupérer les commentaires paginés avec jointure utilisateur et post ---
$sql = "SELECT c.id, c.content as comment_content, c.created_at, u.id as user_id, u.prenom, u.nom, u.email, p.id as post_id, p.content as post_content FROM comments c JOIN users u ON c.user_id = u.id JOIN posts p ON c.post_id = p.id $where ORDER BY c.created_at DESC LIMIT :offset, :per_page";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Calcul du nombre total de pages ---
$total_pages = max(1, ceil($total / $per_page));

// --- Statistiques : top posts les plus commentés ---
$top_posts_sql = "SELECT p.id, p.content, COUNT(c.id) as total_comments FROM comments c JOIN posts p ON c.post_id = p.id GROUP BY p.id ORDER BY total_comments DESC LIMIT 5";
$top_posts = $pdo->query($top_posts_sql)->fetchAll(PDO::FETCH_ASSOC);

// --- Statistiques : top commentateurs ---
$top_users_sql = "SELECT u.id, u.prenom, u.nom, u.email, COUNT(c.id) as total_comments FROM comments c JOIN users u ON c.user_id = u.id GROUP BY u.id ORDER BY total_comments DESC LIMIT 5";
$top_users = $pdo->query($top_users_sql)->fetchAll(PDO::FETCH_ASSOC);

// --- Réponse JSON structurée ---
echo json_encode([
    'success' => true,
    'comments' => $comments,
    'total' => $total,
    'page' => $page,
    'per_page' => $per_page,
    'total_pages' => $total_pages,
    'top_posts' => $top_posts,
    'top_users' => $top_users
]); 