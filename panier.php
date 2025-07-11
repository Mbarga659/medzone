<?php
$pageTitle = 'Mon Panier - MedZone';
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=panier.php');
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

function format_fcfa($prix) {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}
?>

<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Mon Panier</h1>
                <p class="lead mb-0">Vérifiez vos articles et finalisez votre commande en toute sécurité.</p>
            </div>
            <div class="col-lg-4 text-center">
                <span class="material-symbols-outlined" style="font-size: 5rem; opacity: 0.8;">shopping_cart</span>
            </div>
        </div>
    </div>
</section>

<!-- Cart Section -->
<section class="py-5">
    <div class="container">
        <?php if (empty($cart_items)): ?>
            <!-- Panier vide -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <span class="material-symbols-outlined text-muted" style="font-size: 5rem;">shopping_cart</span>
                </div>
                <h3 class="text-muted mb-3">Votre panier est vide</h3>
                <p class="text-muted mb-4">Découvrez nos produits et commencez vos achats.</p>
                <a href="pharmacie.php" class="btn btn-primary btn-lg">
                    <span class="material-symbols-outlined me-2">shopping_bag</span>
                    Voir nos produits
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-4">
                            <h3 class="mb-4">Articles dans votre panier</h3>
                            
                            <?php foreach ($cart_items as $index => $item): ?>
                                <div class="card mb-3 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <?php if ($item['product']['image']): ?>
                                                    <img src="uploads/<?= htmlspecialchars($item['product']['image']) ?>" 
                                                         class="img-fluid rounded" 
                                                         alt="<?= htmlspecialchars($item['product']['nom']) ?>">
                                                <?php else: ?>
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                        <span class="material-symbols-outlined text-muted">medication</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <h6 class="mb-1"><?= htmlspecialchars($item['product']['nom']) ?></h6>
                                                <p class="text-muted small mb-0"><?= htmlspecialchars($item['product']['fabricant']) ?></p>
                                                <?php if ($item['product']['prix_promo']): ?>
                                                    <small class="text-danger">Promo !</small>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-2">
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary" 
                                                            type="button" 
                                                            onclick="updateQuantity(<?= $index ?>, -1)">
                                                        <span class="material-symbols-outlined">remove</span>
                                                    </button>
                                                    <input type="number" 
                                                           class="form-control text-center" 
                                                           value="<?= $item['quantity'] ?>" 
                                                           min="1" 
                                                           max="<?= $item['product']['stock'] ?>"
                                                           onchange="updateQuantity(<?= $index ?>, this.value - <?= $item['quantity'] ?>)">
                                                    <button class="btn btn-outline-secondary" 
                                                            type="button" 
                                                            onclick="updateQuantity(<?= $index ?>, 1)">
                                                        <span class="material-symbols-outlined">add</span>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-2 text-end">
                                                <div class="fw-bold"><?= format_fcfa($item['price']) ?></div>
                                                <?php if ($item['product']['prix_promo']): ?>
                                                    <small class="text-decoration-line-through text-muted">
                                                        <?= format_fcfa($item['product']['prix']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-2 text-end">
                                                <div class="fw-bold text-primary"><?= format_fcfa($item['subtotal']) ?></div>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="removeItem(<?= $index ?>)">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Résumé de la commande -->
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Résumé de la commande</h4>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total</span>
                                <span><?= format_fcfa($total) ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Livraison</span>
                                <span class="text-success">Gratuite</span>
                            </div>
                            
                            <?php if ($total < 50): ?>
                                <div class="alert alert-info small mb-3">
                                    <span class="material-symbols-outlined me-1">info</span>
                                    Plus que <?= format_fcfa(50 - $total) ?> pour la livraison gratuite !
                                </div>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <strong>Total</strong>
                                <strong class="text-primary fs-5"><?= format_fcfa($total) ?></strong>
                            </div>
                            
                            <button class="btn btn-primary btn-lg w-100 mb-3" onclick="checkout()">
                                <span class="material-symbols-outlined me-2">shopping_cart_checkout</span>
                                Finaliser la commande
                            </button>
                            
                            <a href="pharmacie.php" class="btn btn-outline-secondary w-100">
                                <span class="material-symbols-outlined me-2">add_shopping_cart</span>
                                Continuer les achats
                            </a>
                        </div>
                    </div>
                    
                    <!-- Informations de livraison -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <span class="material-symbols-outlined me-2 text-primary">local_shipping</span>
                                Livraison
                            </h6>
                            <ul class="list-unstyled small">
                                <li class="mb-1">
                                    <span class="material-symbols-outlined text-success me-1">check_circle</span>
                                    Livraison gratuite dès 50 FCFA
                                </li>
                                <li class="mb-1">
                                    <span class="material-symbols-outlined text-info me-1">schedule</span>
                                    Délai : 24-48h
                                </li>
                                <li class="mb-1">
                                    <span class="material-symbols-outlined text-warning me-1">location_on</span>
                                    France métropolitaine
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Sécurité -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <span class="material-symbols-outlined me-2 text-success">security</span>
                                Paiement sécurisé
                            </h6>
                            <p class="small text-muted mb-0">
                                Vos données sont protégées par un cryptage SSL. 
                                Nous ne stockons jamais vos informations de paiement.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Mettre à jour la quantité d'un article
function updateQuantity(index, change) {
    const newQuantity = Math.max(1, change);
    
    fetch('includes/update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            index: index,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            MedZone.showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        MedZone.showNotification('Erreur lors de la mise à jour', 'error');
    });
}

// Supprimer un article du panier
function removeItem(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
        fetch('includes/remove-from-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                index: index
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                MedZone.showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            MedZone.showNotification('Erreur lors de la suppression', 'error');
        });
    }
}

// Finaliser la commande
function checkout() {
    // Rediriger vers la page de finalisation
    window.location.href = 'checkout.php';
}
</script>

<?php require_once 'includes/footer.php'; ?> 