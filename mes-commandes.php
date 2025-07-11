<?php
$pageTitle = 'Mes Commandes - MedZone';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=mes-commandes.php');
    exit;
}

$pdo = getDB();
$user_id = $_SESSION['user_id'];

// Récupérer les commandes de l'utilisateur
$stmt = $pdo->prepare('SELECT * FROM commandes WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user_id]);
$commandes = $stmt->fetchAll();

function format_fcfa($prix) {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}
?>

<section class="py-5">
    <div class="container">
        <h1 class="mb-4">Mes Commandes</h1>
        <?php if (empty($commandes)): ?>
            <div class="alert alert-info">Vous n'avez pas encore passé de commande.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td><?= $commande['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($commande['created_at'])) ?></td>
                            <td><?= format_fcfa($commande['total']) ?></td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                switch ($commande['statut']) {
                                    case 'confirme': $badge = 'info'; break;
                                    case 'expedie': $badge = 'primary'; break;
                                    case 'livre': $badge = 'success'; break;
                                    case 'annule': $badge = 'danger'; break;
                                }
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($commande['statut']) ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#commandeModal<?= $commande['id'] ?>">
                                    Voir détail
                                </button>
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
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 