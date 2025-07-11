<?php
session_start();
require_once '../config/database.php';

// Sécurité admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/stats.php');
    exit;
}
$pdo = getDB();
$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Chiffres clés
$nb_commandes = $pdo->query('SELECT COUNT(*) FROM commandes')->fetchColumn();
$ca_total = $pdo->query('SELECT SUM(total) FROM commandes WHERE statut != "annule"')->fetchColumn();
$nb_clients = $pdo->query('SELECT COUNT(DISTINCT user_id) FROM commandes')->fetchColumn();
$nb_produits_vendus = $pdo->query('SELECT SUM(quantite) FROM commande_details')->fetchColumn();

// Top 5 produits
$top_produits = $pdo->query('SELECT p.nom, SUM(cd.quantite) as total_vendus FROM commande_details cd JOIN produits p ON cd.produit_id = p.id GROUP BY cd.produit_id ORDER BY total_vendus DESC LIMIT 5')->fetchAll();

// Ventes par mois (12 derniers mois)
$ventes_mois = $pdo->query('SELECT DATE_FORMAT(created_at, "%Y-%m") as mois, SUM(total) as ca, COUNT(*) as nb FROM commandes WHERE statut != "annule" GROUP BY mois ORDER BY mois DESC LIMIT 12')->fetchAll();
$ventes_mois = array_reverse($ventes_mois);

// Statut des commandes
$statuts = $pdo->query('SELECT statut, COUNT(*) as nb FROM commandes GROUP BY statut')->fetchAll();

function format_fcfa($prix) {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Statistiques</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
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
            <li class="nav-item mb-2"><a href="stats.php" class="nav-link text-white fw-bold"><span class="material-symbols-outlined me-1">insights</span> Statistiques</a></li>
            <li class="nav-item mb-2"><a href="stocks.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">inventory_2</span> Stocks</a></li>
            <li class="nav-item mt-4"><a href="../index.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">logout</span> Retour au site</a></li>
        </ul>
    </nav>
    <!-- Contenu principal -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Statistiques & Ventes</h2>
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card text-bg-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2"><span class="material-symbols-outlined me-2">receipt_long</span><span class="fs-5">Commandes</span></div>
                        <div class="fs-3 fw-bold"><?= $nb_commandes ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-success h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2"><span class="material-symbols-outlined me-2">payments</span><span class="fs-5">CA total</span></div>
                        <div class="fs-3 fw-bold"><?= format_fcfa($ca_total) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2"><span class="material-symbols-outlined me-2">group</span><span class="fs-5">Clients</span></div>
                        <div class="fs-3 fw-bold"><?= $nb_clients ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2"><span class="material-symbols-outlined me-2">inventory_2</span><span class="fs-5">Produits vendus</span></div>
                        <div class="fs-3 fw-bold"><?= $nb_produits_vendus ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4 g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Top 5 produits les plus vendus</h5>
                        <ol class="list-group list-group-numbered">
                            <?php foreach ($top_produits as $prod): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($prod['nom']) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $prod['total_vendus'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Répartition des commandes par statut</h5>
                        <canvas id="statutChart" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Évolution du chiffre d'affaires (12 derniers mois)</h5>
                <canvas id="caChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Statut chart
const statutData = {
    labels: [
        <?php foreach ($statuts as $s) echo "'".ucfirst($s['statut'])."',"; ?>
    ],
    datasets: [{
        data: [<?php foreach ($statuts as $s) echo $s['nb'].","; ?>],
        backgroundColor: [
            '#0d6efd','#198754','#ffc107','#6c757d','#dc3545'
        ]
    }]
};
new Chart(document.getElementById('statutChart'), {
    type: 'doughnut',
    data: statutData,
    options: {plugins:{legend:{position:'bottom'}}}
});
// CA chart
const caData = {
    labels: [<?php foreach ($ventes_mois as $v) echo "'".substr($v['mois'],5,2)."/".substr($v['mois'],0,4)."',"; ?>],
    datasets: [{
        label: 'CA (FCFA)',
        data: [<?php foreach ($ventes_mois as $v) echo $v['ca'].","; ?>],
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.1)',
        tension: 0.3,
        fill: true
    }]
};
new Chart(document.getElementById('caChart'), {
    type: 'line',
    data: caData,
    options: {
        scales: {y: {beginAtZero: true}},
        plugins: {legend: {display: false}}
    }
});
</script>
</body>
</html> 