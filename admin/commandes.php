<?php
session_start();
require_once '../config/database.php';

// Sécurité admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/commandes.php');
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

// Filtres
$filtre_statut = $_GET['statut'] ?? '';
$filtre_user = $_GET['user'] ?? '';
$filtre_date_debut = $_GET['date_debut'] ?? '';
$filtre_date_fin = $_GET['date_fin'] ?? '';
$filtre_mode = $_GET['mode'] ?? '';

// Construction de la requête avec filtres dynamiques
$where = [];
$params = [];
if ($filtre_statut && in_array($filtre_statut, ['en_attente','confirme','expedie','livre','annule'])) {
    $where[] = 'c.statut = ?';
    $params[] = $filtre_statut;
}
if ($filtre_user) {
    $where[] = '(u.nom LIKE ? OR u.email LIKE ?)';
    $params[] = "%$filtre_user%";
    $params[] = "%$filtre_user%";
}
if ($filtre_date_debut) {
    $where[] = 'DATE(c.created_at) >= ?';
    $params[] = $filtre_date_debut;
}
if ($filtre_date_fin) {
    $where[] = 'DATE(c.created_at) <= ?';
    $params[] = $filtre_date_fin;
}
if ($filtre_mode && in_array($filtre_mode, ['livraison','mobile'])) {
    $where[] = 'c.mode_paiement = ?';
    $params[] = $filtre_mode;
}
$sql = 'SELECT c.*, u.nom as user_nom, u.email FROM commandes c JOIN users u ON c.user_id = u.id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY c.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll();

// Changement de statut
if (isset($_POST['statut']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $statut = $_POST['statut'];
    if (in_array($statut, ['en_attente','confirme','expedie','livre','annule'])) {
        $stmt = $pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?');
        if ($stmt->execute([$statut, $id])) {
            $success = 'Statut mis à jour.';
        } else {
            $error = 'Erreur lors de la modification du statut.';
        }
    }
}

// Suppression d'une commande
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM commandes WHERE id = ?');
    if ($stmt->execute([$id])) {
        $success = 'Commande supprimée avec succès.';
    } else {
        $error = 'Erreur lors de la suppression.';
    }
}

function format_fcfa($prix) {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Commandes</title>
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
            <li class="nav-item mb-2"><a href="commandes.php" class="nav-link text-white fw-bold"><span class="material-symbols-outlined me-1">receipt_long</span> Commandes</a></li>
            <li class="nav-item mb-2"><a href="stats.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">insights</span> Statistiques</a></li>
            <li class="nav-item mb-2"><a href="stocks.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">inventory_2</span> Stocks</a></li>
            <li class="nav-item mt-4"><a href="../index.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">logout</span> Retour au site</a></li>
        </ul>
    </nav>
    <!-- Contenu principal -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Gestion des commandes</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <!-- Filtres -->
        <form method="get" class="row g-2 mb-4 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="en_attente" <?= $filtre_statut=='en_attente'?'selected':'' ?>>En attente</option>
                    <option value="confirme" <?= $filtre_statut=='confirme'?'selected':'' ?>>Confirmée</option>
                    <option value="expedie" <?= $filtre_statut=='expedie'?'selected':'' ?>>Expédiée</option>
                    <option value="livre" <?= $filtre_statut=='livre'?'selected':'' ?>>Livrée</option>
                    <option value="annule" <?= $filtre_statut=='annule'?'selected':'' ?>>Annulée</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Utilisateur (nom ou email)</label>
                <input type="text" name="user" class="form-control" value="<?= htmlspecialchars($filtre_user) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date début</label>
                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($filtre_date_debut) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($filtre_date_fin) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Mode de paiement</label>
                <select name="mode" class="form-select">
                    <option value="">Tous</option>
                    <option value="livraison" <?= $filtre_mode=='livraison'?'selected':'' ?>>À la livraison</option>
                    <option value="mobile" <?= $filtre_mode=='mobile'?'selected':'' ?>>Mobile Money</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>
        <!-- Fin filtres -->
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Liste des commandes</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Mode paiement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commandes as $commande): ?>
                            <tr>
                                <td><?= $commande['id'] ?></td>
                                <td><?= htmlspecialchars($commande['user_nom']) ?></td>
                                <td><?= htmlspecialchars($commande['email']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($commande['created_at'])) ?></td>
                                <td><?= format_fcfa($commande['total']) ?></td>
                                <td>
                                    <form method="post" class="d-flex align-items-center gap-2 mb-0">
                                        <input type="hidden" name="id" value="<?= $commande['id'] ?>">
                                        <select name="statut" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="en_attente" <?= $commande['statut']=='en_attente'?'selected':'' ?>>En attente</option>
                                            <option value="confirme" <?= $commande['statut']=='confirme'?'selected':'' ?>>Confirmée</option>
                                            <option value="expedie" <?= $commande['statut']=='expedie'?'selected':'' ?>>Expédiée</option>
                                            <option value="livre" <?= $commande['statut']=='livre'?'selected':'' ?>>Livrée</option>
                                            <option value="annule" <?= $commande['statut']=='annule'?'selected':'' ?>>Annulée</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($commande['mode_paiement'] === 'mobile'): ?>
                                        <span class="badge bg-info">Mobile Money</span><br><span class="small text-muted"><?= htmlspecialchars($commande['numero_paiement']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">À la livraison</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#commandeModal<?= $commande['id'] ?>">Détail</button>
                                    <a href="?delete=<?= $commande['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette commande ?');">Supprimer</a>
                                </td>
                            </tr>
                            <!-- Modal détail commande -->
                            <div class="modal fade" id="commandeModal<?= $commande['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Commande #<?= $commande['id'] ?> du <?= date('d/m/Y H:i', strtotime($commande['created_at'])) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h6>Adresse de livraison</h6>
                                            <p><?= htmlspecialchars($commande['adresse_livraison']) ?><br>Tél : <?= htmlspecialchars($commande['telephone_livraison']) ?></p>
                                            <h6>Mode de paiement</h6>
                                            <?php if ($commande['mode_paiement'] === 'mobile'): ?>
                                                <p><span class="badge bg-info">Mobile Money</span> <?= htmlspecialchars($commande['numero_paiement']) ?></p>
                                            <?php else: ?>
                                                <p><span class="badge bg-secondary">À la livraison</span></p>
                                            <?php endif; ?>
                                            <h6>Produits commandés</h6>
                                            <ul class="list-group mb-3">
                                                <?php
                                                $stmt2 = $pdo->prepare('SELECT cd.*, p.nom FROM commande_details cd JOIN produits p ON cd.produit_id = p.id WHERE cd.commande_id = ?');
                                                $stmt2->execute([$commande['id']]);
                                                $details = $stmt2->fetchAll();
                                                foreach ($details as $detail): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?= htmlspecialchars($detail['nom']) ?></strong><br>
                                                            <small>x<?= $detail['quantite'] ?></small>
                                                        </div>
                                                        <span><?= format_fcfa($detail['prix_unitaire'] * $detail['quantite']) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-bold">Total</span>
                                                <span class="fw-bold text-primary fs-5"><?= format_fcfa($commande['total']) ?></span>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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