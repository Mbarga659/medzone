<?php
session_start();
require_once '../config/database.php';

// Sécurité admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/avis.php');
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

// Actions modération
if (isset($_POST['action'], $_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($_POST['action'] === 'valider') {
        $stmt = $pdo->prepare('UPDATE avis SET statut = "valide" WHERE id = ?');
        if ($stmt->execute([$id])) $success = 'Avis validé.';
        else $error = 'Erreur lors de la validation.';
    } elseif ($_POST['action'] === 'masquer') {
        $stmt = $pdo->prepare('UPDATE avis SET statut = "masque" WHERE id = ?');
        if ($stmt->execute([$id])) $success = 'Avis masqué.';
        else $error = 'Erreur lors du masquage.';
    } elseif ($_POST['action'] === 'supprimer') {
        $stmt = $pdo->prepare('DELETE FROM avis WHERE id = ?');
        if ($stmt->execute([$id])) $success = 'Avis supprimé.';
        else $error = 'Erreur lors de la suppression.';
    }
}

// Filtres
$filtre_statut = $_GET['statut'] ?? '';
$filtre_user = $_GET['user'] ?? '';
$filtre_produit = $_GET['produit'] ?? '';
$where = [];
$params = [];
if ($filtre_statut && in_array($filtre_statut, ['en_attente','valide','masque'])) {
    $where[] = 'a.statut = ?';
    $params[] = $filtre_statut;
}
if ($filtre_user) {
    $where[] = '(u.nom LIKE ? OR u.email LIKE ?)';
    $params[] = "%$filtre_user%";
    $params[] = "%$filtre_user%";
}
if ($filtre_produit) {
    $where[] = 'p.nom LIKE ?';
    $params[] = "%$filtre_produit%";
}
$sql = 'SELECT a.*, u.nom as user_nom, u.email, p.nom as produit_nom FROM avis a JOIN users u ON a.user_id = u.id JOIN produits p ON a.produit_id = p.id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY a.date DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$avis = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Avis produits</title>
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
            <li class="nav-item mb-2"><a href="avis.php" class="nav-link text-white fw-bold"><span class="material-symbols-outlined me-1">rate_review</span> Avis</a></li>
            <li class="nav-item mt-4"><a href="../index.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">logout</span> Retour au site</a></li>
        </ul>
    </nav>
    <!-- Contenu principal -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Modération des avis produits</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <!-- Filtres -->
        <form method="get" class="row g-2 mb-4 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="en_attente" <?= $filtre_statut=='en_attente'?'selected':'' ?>>En attente</option>
                    <option value="valide" <?= $filtre_statut=='valide'?'selected':'' ?>>Validé</option>
                    <option value="masque" <?= $filtre_statut=='masque'?'selected':'' ?>>Masqué</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Utilisateur (nom ou email)</label>
                <input type="text" name="user" class="form-control" value="<?= htmlspecialchars($filtre_user) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Produit</label>
                <input type="text" name="produit" class="form-control" value="<?= htmlspecialchars($filtre_produit) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>
        <!-- Fin filtres -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produit</th>
                                <th>Utilisateur</th>
                                <th>Note</th>
                                <th>Commentaire</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($avis as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['produit_nom']) ?></td>
                                <td><?= htmlspecialchars($a['user_nom']) ?><br><span class="text-muted small"><?= htmlspecialchars($a['email']) ?></span></td>
                                <td><span class="badge bg-warning text-dark"><span class="material-symbols-outlined align-middle">star</span> <?= $a['note'] ?>/5</span></td>
                                <td><?= nl2br(htmlspecialchars($a['commentaire'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($a['date'])) ?></td>
                                <td>
                                    <?php if ($a['statut']=='valide'): ?>
                                        <span class="badge bg-success">Validé</span>
                                    <?php elseif ($a['statut']=='en_attente'): ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Masqué</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <?php if ($a['statut']!='valide'): ?>
                                            <button name="action" value="valider" class="btn btn-success btn-sm" onclick="return confirm('Valider cet avis ?');">Valider</button>
                                        <?php endif; ?>
                                        <?php if ($a['statut']!='masque'): ?>
                                            <button name="action" value="masquer" class="btn btn-warning btn-sm" onclick="return confirm('Masquer cet avis ?');">Masquer</button>
                                        <?php endif; ?>
                                        <button name="action" value="supprimer" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet avis ?');">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 