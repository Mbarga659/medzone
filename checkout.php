<?php
$pageTitle = 'Finaliser la commande - MedZone';
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$cart_items = [];
$total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $pdo = getDB();
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $pdo->prepare('SELECT * FROM produits WHERE id = ? AND actif = 1');
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch();
        if ($product) {
            $price = $product['prix_promo'] ?: $product['prix'];
            $subtotal = $price * $item['quantity'];
            $total += $subtotal;
            $cart_items[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $price,
                'subtotal' => $subtotal
            ];
        }
    }
}

// Traitement du formulaire
$confirmation = false;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mode_paiement = $_POST['mode_paiement'] ?? 'livraison';
    $numero_paiement = trim($_POST['numero_paiement'] ?? '');
    if ($nom === '' || $adresse === '' || $telephone === '' || $email === '') {
        $errors[] = 'Tous les champs sont obligatoires.';
    }
    if ($mode_paiement === 'mobile' && !$numero_paiement) {
        $errors[] = 'Veuillez saisir un numéro de paiement mobile.';
    }
    if (empty($errors)) {
        // Enregistrement réel de la commande
        try {
            $pdo->beginTransaction();
            // Insérer la commande
            $stmt = $pdo->prepare('INSERT INTO commandes (user_id, total, statut, adresse_livraison, telephone_livraison, mode_paiement, numero_paiement, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
            $statut = 'en_attente';
            $stmt->execute([
                $_SESSION['user_id'],
                $total,
                $statut,
                $adresse,
                $telephone,
                $mode_paiement,
                $mode_paiement === 'mobile' ? $numero_paiement : null
            ]);
            $commande_id = $pdo->lastInsertId();
            // Insérer les produits de la commande
            $stmt_detail = $pdo->prepare('INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)');
            foreach ($cart_items as $item) {
                $stmt_detail->execute([
                    $commande_id,
                    $item['product']['id'],
                    $item['quantity'],
                    $item['price']
                ]);
                // Mettre à jour le stock du produit
                $stmt_stock = $pdo->prepare('UPDATE produits SET stock = stock - ? WHERE id = ?');
                $stmt_stock->execute([$item['quantity'], $item['product']['id']]);
            }
            $pdo->commit();
            $confirmation = true;
            unset($_SESSION['cart']);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors de l'enregistrement de la commande : " . $e->getMessage();
        }
    }
}

function format_fcfa($prix) {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}
?>

<section class="py-5">
    <div class="container">
        <h1 class="mb-4">Finaliser la commande</h1>
        <?php if ($confirmation): ?>
            <div class="alert alert-success">
                <span class="material-symbols-outlined me-2">check_circle</span>
                Merci pour votre commande ! Vous recevrez un email de confirmation sous peu.<br>
                <strong>Mode de paiement :</strong> <?= ($mode_paiement === 'mobile') ? 'Paiement mobile ('.$numero_paiement.')' : 'Paiement à la livraison' ?>
            </div>
            <a href="pharmacie.php" class="btn btn-primary mt-3">Retour à la boutique</a>
        <?php elseif (empty($cart_items)): ?>
            <div class="alert alert-info">
                Votre panier est vide. <a href="pharmacie.php">Voir les produits</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Informations de livraison</h5>
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?= implode('<br>', $errors) ?>
                                </div>
                            <?php endif; ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="adresse" class="form-label">Adresse de livraison</label>
                                    <input type="text" class="form-control" id="adresse" name="adresse" required value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="text" class="form-control" id="telephone" name="telephone" required value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mode de paiement</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="mode_paiement" id="paiement_livraison" value="livraison" <?= (!isset($_POST['mode_paiement']) || $_POST['mode_paiement']==='livraison') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="paiement_livraison">Paiement à la livraison</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="mode_paiement" id="paiement_mobile" value="mobile" <?= (isset($_POST['mode_paiement']) && $_POST['mode_paiement']==='mobile') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="paiement_mobile">Paiement mobile (Orange Money, MTN Mobile Money...)</label>
                                    </div>
                                </div>
                                <div class="mb-3" id="champ_numero_mobile" style="display:<?= (isset($_POST['mode_paiement']) && $_POST['mode_paiement']==='mobile') ? 'block' : 'none' ?>;">
                                    <label for="numero_paiement" class="form-label">Numéro de paiement mobile</label>
                                    <input type="text" class="form-control" id="numero_paiement" name="numero_paiement" value="<?= htmlspecialchars($_POST['numero_paiement'] ?? '') ?>">
                                    <div class="form-text">Orange Money, MTN Mobile Money, etc.</div>
                                </div>
                                <button type="submit" class="btn btn-success">Valider la commande</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Récapitulatif de la commande</h5>
                            <ul class="list-group mb-3">
                                <?php foreach ($cart_items as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($item['product']['nom']) ?></strong><br>
                                            <small>x<?= $item['quantity'] ?></small>
                                        </div>
                                        <span><?= format_fcfa($item['subtotal']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold text-primary fs-5"><?= format_fcfa($total) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Afficher/masquer le champ numéro mobile selon le choix
const radioLivraison = document.getElementById('paiement_livraison');
const radioMobile = document.getElementById('paiement_mobile');
const champNumero = document.getElementById('champ_numero_mobile');
function toggleNumeroMobile() {
    champNumero.style.display = radioMobile.checked ? 'block' : 'none';
}
radioLivraison.addEventListener('change', toggleNumeroMobile);
radioMobile.addEventListener('change', toggleNumeroMobile);
</script>

<?php require_once 'includes/footer.php'; ?> 