<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.html');
    exit;
}
$adminNom = $_SESSION['admin_nom'] ?? '';
$adminPrenom = $_SESSION['admin_prenom'] ?? '';
$adminEmail = $_SESSION['admin_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin - Facebook-like</title>
    <link rel="stylesheet" href="../../assets/css/admin.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <div class="admin-sidebar-logo">
                    <i class="fab fa-facebook fa-2x"></i>
                </div>
                <div class="admin-sidebar-title">Facebook-like Admin</div>
            </div>
            <nav class="admin-sidebar-nav">
                <a href="dashboard.php" class="admin-nav-item active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                <a href="users.php" class="admin-nav-item"><i class="fa-solid fa-users"></i> Utilisateurs</a>
                <a href="posts.php" class="admin-nav-item"><i class="fa-solid fa-newspaper"></i> Posts</a>
                <a href="comments.php" class="admin-nav-item"><i class="fa-solid fa-comments"></i> Commentaires</a>
                <a href="likes.php" class="admin-nav-item"><i class="fa-solid fa-thumbs-up"></i> Likes</a>
            </nav>
        </aside>
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <button id="refresh-stats-btn" class="admin-btn" style="margin-left: 1.5em; background: var(--color-primary); color: #fff; font-weight: 600;">
                    <i class="fa-solid fa-rotate"></i> Rafraîchir
                </button>
                <div id="stats-loader" style="display:none; margin-left: 2em;">
                    <i class="fa fa-spinner fa-spin" style="font-size:1.7em; color: var(--color-primary);"></i>
                </div>
                <div class="admin-user-info">
                    <div class="admin-user-avatar" id="admin-avatar">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <span id="admin-name">
                        <?php
                        echo htmlspecialchars(trim($adminPrenom . ' ' . $adminNom));
                        ?>
                        <br>
                        <small style="color:var(--color-text-muted);font-size:0.95em;">
                            <?php echo htmlspecialchars($adminEmail); ?>
                        </small>
                    </span>
                    <form method="post" action="../../api/admin/logout.php" style="display:inline;">
                        <button class="admin-logout-btn" type="submit">
                            <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </header>
            <section class="admin-stats-grid">
                <!-- ... (tes cartes de stats ici, inchangées) ... -->
                <div class="admin-stat-card">
                    <div class="admin-stat-icon users">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="admin-stat-number" id="stat-users">0</div>
                    <div class="admin-stat-label">Utilisateurs</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon posts">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div class="admin-stat-number" id="stat-posts">0</div>
                    <div class="admin-stat-label">Posts</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon comments">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <div class="admin-stat-number" id="stat-comments">0</div>
                    <div class="admin-stat-label">Commentaires</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon likes">
                        <i class="fa-solid fa-thumbs-up"></i>
                    </div>
                    <div class="admin-stat-number" id="stat-likes">0</div>
                    <div class="admin-stat-label">Likes</div>
                </div>
            </section>
            <section>
                <h2>Bienvenue sur le panneau d’administration !</h2>
                <p>
                    Gérez les utilisateurs, les publications, les commentaires et surveillez l’activité du site.
                </p>
            </section>
        </main>
    </div>
    <script src="../../assets/js/admin-dashboard.js"></script>
</body>
</html>