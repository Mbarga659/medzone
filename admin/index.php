<?php
session_start();
require_once '../config/database.php';

// Vérifier que l'utilisateur est connecté et admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/index.php');
    exit;
}
$pdo = getDB();
$stmt = $pdo->prepare('SELECT role, nom FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$pageTitle = 'Admin - Tableau de bord';

// Statistiques globales
$totalCommandes = $pdo->query('SELECT COUNT(*) FROM commandes')->fetchColumn();
$totalCA = $pdo->query("SELECT IFNULL(SUM(total),0) FROM commandes WHERE statut IN ('confirme','expedie','livre')")->fetchColumn();
$totalProduits = $pdo->query('SELECT COUNT(*) FROM produits')->fetchColumn();
$totalStock = $pdo->query('SELECT IFNULL(SUM(stock),0) FROM produits')->fetchColumn();
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$commandesAttente = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'")->fetchColumn();
// Top 3 produits les plus vendus
$topProduits = $pdo->query('SELECT p.nom, SUM(cd.quantite) as total_vendus FROM commande_details cd JOIN produits p ON cd.produit_id = p.id GROUP BY cd.produit_id ORDER BY total_vendus DESC LIMIT 3')->fetchAll();

// Alertes admin
$nb_stock_bas = $pdo->query('SELECT COUNT(*) FROM produits WHERE stock <= seuil_alerte')->fetchColumn();
$nb_cmd_attente = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'")->fetchColumn();
$nb_avis_attente = $pdo->query("SELECT COUNT(*) FROM avis WHERE statut = 'en_attente'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex" style="min-height:100vh;">
    <!-- Menu latéral -->
    <nav class="bg-primary text-white p-3" style="width:240px;min-height:100vh;">
        <h4 class="mb-4"><span class="material-symbols-outlined me-2">admin_panel_settings</span>Admin</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="index.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">dashboard</span> Tableau de bord</a></li>
            <li class="nav-item mb-2"><a href="produits.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">medication</span> Produits</a></li>
            <li class="nav-item mb-2"><a href="categories.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">category</span> Catégories</a></li>
            <li class="nav-item mb-2"><a href="utilisateurs.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">group</span> Utilisateurs</a></li>
            <li class="nav-item mb-2"><a href="commandes.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">receipt_long</span> Commandes</a></li>
            <li class="nav-item mb-2"><a href="stats.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">insights</span> Statistiques</a></li>
            <li class="nav-item mb-2"><a href="stocks.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">inventory_2</span> Stocks</a></li>
            <li class="nav-item mt-4"><a href="../index.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">logout</span> Retour au site</a></li>
        </ul>
    </nav>
    <!-- Contenu principal -->
    <div class="flex-grow-1 p-4">
        <h1 class="mb-4">Bienvenue, <?= htmlspecialchars($user['nom']) ?> !</h1>
        <?php if ($nb_stock_bas || $nb_cmd_attente || $nb_avis_attente): ?>
        <div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
            <span class="material-symbols-outlined me-2">notification_important</span>
            <div>
                <?php if ($nb_stock_bas): ?>
                    <a href="stocks.php" class="alert-link fw-bold"><?= $nb_stock_bas ?> produit<?= $nb_stock_bas>1?'s':'' ?> sous le seuil de stock</a><br>
                <?php endif; ?>
                <?php if ($nb_cmd_attente): ?>
                    <a href="commandes.php?statut=en_attente" class="alert-link fw-bold"><?= $nb_cmd_attente ?> commande<?= $nb_cmd_attente>1?'s':'' ?> en attente</a><br>
                <?php endif; ?>
                <?php if ($nb_avis_attente): ?>
                    <a href="avis.php?statut=en_attente" class="alert-link fw-bold"><?= $nb_avis_attente ?> avis à valider</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center">
                    <div class="card-body">
                        <span class="material-symbols-outlined text-primary" style="font-size:2.5rem;">receipt_long</span>
                        <h5 class="mt-2 mb-1">Commandes</h5>
                        <div class="fs-4 fw-bold"><?= $totalCommandes ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center">
                    <div class="card-body">
                        <span class="material-symbols-outlined text-success" style="font-size:2.5rem;">payments</span>
                        <h5 class="mt-2 mb-1">Chiffre d'affaires</h5>
                        <div class="fs-4 fw-bold"><?= number_format($totalCA, 0, ',', ' ') ?> FCFA</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center">
                    <div class="card-body">
                        <span class="material-symbols-outlined text-info" style="font-size:2.5rem;">inventory_2</span>
                        <h5 class="mt-2 mb-1">Produits en stock</h5>
                        <div class="fs-4 fw-bold"><?= $totalStock ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 text-center">
                    <div class="card-body">
                        <span class="material-symbols-outlined text-warning" style="font-size:2.5rem;">group</span>
                        <h5 class="mt-2 mb-1">Utilisateurs</h5>
                        <div class="fs-4 fw-bold"><?= $totalUsers ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 text-center">
                    <div class="card-body">
                        <span class="material-symbols-outlined text-danger" style="font-size:2.5rem;">pending_actions</span>
                        <h5 class="mt-2 mb-1">Commandes en attente</h5>
                        <div class="fs-4 fw-bold"><?= $commandesAttente ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">Top 3 produits les plus vendus</h5>
                        <table class="table table-sm">
                            <thead><tr><th>Produit</th><th>Quantité vendue</th></tr></thead>
                            <tbody>
                            <?php foreach ($topProduits as $prod): ?>
                                <tr><td><?= htmlspecialchars($prod['nom']) ?></td><td><?= $prod['total_vendus'] ?></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card h-100 border-primary border-2">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <span class="material-symbols-outlined text-primary mb-2" style="font-size:2.5rem;">cloud_download</span>
                        <h6 class="mb-3">Sauvegarde de la base</h6>
                        <a href="backup.php" class="btn btn-outline-primary w-100 fw-bold" target="_blank">
                            <span class="material-symbols-outlined me-1 align-middle">download</span>
                            Sauvegarder la base
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="alert alert-info">
            Ceci est le tableau de bord administrateur. Utilisez le menu à gauche pour gérer le site.
        </div>
        <!-- Ici, on pourra ajouter des widgets/statistiques plus tard -->
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 