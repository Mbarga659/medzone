<?php
session_start();
require_once '../config/database.php';

// Sécurité admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/stocks.php');
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

$success = $error = '';

// Modification du stock
if (isset($_POST['update_stock'])) {
    $id = (int)$_POST['id'];
    $stock = max(0, (int)$_POST['stock']);
    $seuil = max(0, (int)$_POST['seuil']);
    $stmt = $pdo->prepare('UPDATE produits SET stock = ?, seuil_alerte = ? WHERE id = ?');
    if ($stmt->execute([$stock, $seuil, $id])) {
        $success = 'Stock mis à jour.';
    } else {
        $error = 'Erreur lors de la mise à jour.';
    }
}

// Liste des produits
$produits = $pdo->query('SELECT * FROM produits ORDER BY nom')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Stocks</title>
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
            <li class="nav-item mb-2"><a href="stocks.php" class="nav-link text-white fw-bold"><span class="material-symbols-outlined me-1">inventory_2</span> Stocks</a></li>
            <li class="nav-item mt-4"><a href="../index.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">logout</span> Retour au site</a></li>
        </ul>
    </nav>
    <!-- Contenu principal -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Gestion des stocks</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produit</th>
                                <th>Stock actuel</th>
                                <th>Seuil d'alerte</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produits as $prod): ?>
                            <tr<?= ($prod['stock'] <= $prod['seuil_alerte']) ? ' class="table-danger"' : '' ?>>
                                <td><?= htmlspecialchars($prod['nom']) ?></td>
                                <td><span class="fw-bold<?= ($prod['stock'] <= $prod['seuil_alerte']) ? ' text-danger' : '' ?>"><?= $prod['stock'] ?></span></td>
                                <td><?= $prod['seuil_alerte'] ?></td>
                                <td>
                                    <form method="post" class="d-flex gap-2 align-items-center mb-0">
                                        <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                        <input type="number" name="stock" min="0" value="<?= $prod['stock'] ?>" class="form-control form-control-sm" style="width:90px;">
                                        <input type="number" name="seuil" min="0" value="<?= $prod['seuil_alerte'] ?>" class="form-control form-control-sm" style="width:90px;">
                                        <button type="submit" name="update_stock" class="btn btn-sm btn-primary">Enregistrer</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="form-text">Ligne rouge = stock sous le seuil d'alerte</div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 