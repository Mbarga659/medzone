<?php
session_start();
require_once 'config/database.php';
$pdo = getDB();

// Traitement du formulaire d'avis
$avis_message = '';
if (isset($_POST['envoyer_avis'], $_SESSION['user_id'])) {
    $produit_id = (int)($_POST['avis_produit_id'] ?? 0);
    $note = (int)($_POST['avis_note'] ?? 0);
    $commentaire = trim($_POST['avis_commentaire'] ?? '');
    $user_id = $_SESSION['user_id'];
    // Vérifier validité
    if ($produit_id && $note >= 1 && $note <= 5 && $commentaire && strlen($commentaire) <= 500) {
        // Vérifier unicité
        $stmt = $pdo->prepare('SELECT id FROM avis WHERE produit_id = ? AND user_id = ?');
        $stmt->execute([$produit_id, $user_id]);
        if ($stmt->fetch()) {
            $avis_message = '<div class="alert alert-info">Vous avez déjà laissé un avis pour ce produit.</div>';
        } else {
            $stmt = $pdo->prepare('INSERT INTO avis (produit_id, user_id, note, commentaire, statut) VALUES (?, ?, ?, ?, "en_attente")');
            if ($stmt->execute([$produit_id, $user_id, $note, $commentaire])) {
                $avis_message = '<div class="alert alert-success">Votre avis a bien été envoyé et sera publié après validation.</div>';
            } else {
                $avis_message = '<div class="alert alert-danger">Erreur lors de l\'envoi de l\'avis.</div>';
            }
        }
    } else {
        $avis_message = '<div class="alert alert-warning">Veuillez remplir tous les champs correctement.</div>';
    }
}

$pageTitle = 'Pharmacie - MedZone';
require_once 'includes/header.php';

$pdo = getDB();

// Récupérer l'ID de la catégorie sélectionnée
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;

// Récupérer les produits (filtrés si catégorie sélectionnée)
if ($categorie_id > 0) {
    $stmt = $pdo->prepare('SELECT DISTINCT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.actif = 1 AND p.categorie_id = ? ORDER BY p.created_at DESC');
    $stmt->execute([$categorie_id]);
} else {
    $stmt = $pdo->query('SELECT DISTINCT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.actif = 1 ORDER BY p.created_at DESC');
}
$produits = $stmt->fetchAll();

// Récupérer les catégories pour le filtre
$stmt = $pdo->query('SELECT * FROM categories ORDER BY nom');
$categories = $stmt->fetchAll();

// Mapping images de fallback pour les produits de la maquette
$fallback_images = [
    'Paracétamol 500mg' => 'assets/img/paracetamol.jpg',
    'Ibuprofène 400mg' => 'assets/img/ibuprofene.jpg',
    'Vitamine C 1000mg' => 'assets/img/vitamine-c.jpg',
    'Crème hydratante' => 'assets/img/creme-hydratante.jpg',
    'Doliprane 500 mg' => 'assets/img/doliprane.jpg',
    'Baume Francoi' => 'assets/img/baume.jpg',
];
// Mapping images de catégories
$cat_images = [
    'Analgésiques' => 'assets/img/cat-analgesiques.jpg',
    'Antibiotiques' => 'assets/img/cat-antibiotiques.jpg',
    'Vitamines' => 'assets/img/cat-vitamines.jpg',
    'Soins de la peau' => 'assets/img/cat-soins.jpg',
    'Soins' => 'assets/img/cat-soins.jpg',
    'Premiers soins' => 'assets/img/cat-premiers-soins.jpg',
];
function format_fcfa($prix) {
    return number_format($prix, 0, ',', ' ') . ' fcfa';
}

// Après avoir récupéré $categorie_id et $categories
$categorie_nom_selectionnee = '';
if ($categorie_id > 0) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categorie_id) {
            $categorie_nom_selectionnee = $cat['nom'];
            break;
        }
    }
}
?>

<!-- Search Section -->
<section class="search-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="text-white text-center mb-4">Rechercher un produit</h2>
                <form class="search-form" action="recherche.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" placeholder="Nom du médicament, fabricant..." required>
                        <button class="btn btn-light" type="submit">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold">
                    <?php if ($categorie_nom_selectionnee): ?>
                        Catégorie : <?= htmlspecialchars($categorie_nom_selectionnee) ?>
                    <?php else: ?>
                        Nos Produits
                    <?php endif; ?>
                </h2>
                <p class="lead text-muted">Découvrez notre large gamme de produits pharmaceutiques</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <span class="material-symbols-outlined me-2">filter_list</span>
                        Filtrer par catégorie
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="pharmacie.php">Toutes les catégories</a></li>
                        <?php foreach ($categories as $categorie): ?>
                        <li><a class="dropdown-item" href="pharmacie.php?categorie=<?= $categorie['id'] ?>"><?= htmlspecialchars($categorie['nom']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <?php if (empty($produits)): ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <span class="material-symbols-outlined text-muted" style="font-size: 4rem;">inventory_2</span>
            </div>
            <h4 class="text-muted">Aucun produit disponible</h4>
            <p class="text-muted">Nous travaillons actuellement pour enrichir notre catalogue.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($produits as $produit): ?>
            <div class="col-lg-3 col-md-6">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <?php
                            $img = $produit['image'] ? 'uploads/' . htmlspecialchars($produit['image']) : ($fallback_images[$produit['nom']] ?? null);
                        ?>
                        <?php if ($img): ?>
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" style="max-height: 180px; max-width: 100%; object-fit: contain;">
                        <?php else: ?>
                            <span class="material-symbols-outlined text-muted" style="font-size: 3rem;">medication</span>
                        <?php endif; ?>
                        </div>
                        <?php if ($produit['prix_promo'] && $produit['prix_promo'] < $produit['prix']): ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger">Promo</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($produit['prescription_requise']): ?>
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-warning text-dark">Ordonnance requise</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($produit['nom']) ?></h5>
                        <?php if ($produit['categorie_nom']): ?>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($produit['categorie_nom']) ?></p>
                        <?php endif; ?>
                        <p class="card-text text-muted small flex-grow-1">
                            <?= htmlspecialchars(substr($produit['description'], 0, 100)) ?>...
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex align-items-center mb-3">
                                <?php if ($produit['prix_promo'] && $produit['prix_promo'] < $produit['prix']): ?>
                                    <span class="product-price promo me-2"><?= format_fcfa($produit['prix_promo']) ?></span>
                                    <span class="product-price original"><?= format_fcfa($produit['prix']) ?></span>
                                <?php else: ?>
                                    <span class="product-price"><?= format_fcfa($produit['prix']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="btn btn-primary btn-sm flex-fill add-to-cart" 
                                            data-product-id="<?= $produit['id'] ?>"
                                            data-product-name="<?= htmlspecialchars($produit['nom']) ?>"
                                            data-product-price="<?= $produit['prix_promo'] ?: $produit['prix'] ?>">
                                        <span class="material-symbols-outlined me-1">add_shopping_cart</span>
                                        Ajouter
                                    </button>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline-primary btn-sm flex-fill">
                                        <span class="material-symbols-outlined me-1">login</span>
                                        Se connecter
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-outline-secondary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#productModal<?= $produit['id'] ?>">
                                    <span class="material-symbols-outlined">info</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal pour les détails du produit -->
            <?php
            // Récupérer les avis validés et la moyenne pour ce produit
            $stmt_avis = $pdo->prepare('SELECT a.*, u.nom as user_nom FROM avis a JOIN users u ON a.user_id = u.id WHERE a.produit_id = ? AND a.statut = "valide" ORDER BY a.date DESC');
            $stmt_avis->execute([$produit['id']]);
            $avis = $stmt_avis->fetchAll();
            $stmt_moy = $pdo->prepare('SELECT AVG(note) as moyenne, COUNT(*) as nb FROM avis WHERE produit_id = ? AND statut = "valide"');
            $stmt_moy->execute([$produit['id']]);
            $moyenne = $stmt_moy->fetch();
            $moyenne_note = $moyenne['moyenne'] ? round($moyenne['moyenne'],1) : null;
            $nb_avis = $moyenne['nb'];
            // Vérifier si l'utilisateur connecté a déjà laissé un avis
            $deja_avis = false;
            if (isset($_SESSION['user_id'])) {
                $stmt_deja = $pdo->prepare('SELECT id FROM avis WHERE produit_id = ? AND user_id = ?');
                $stmt_deja->execute([$produit['id'], $_SESSION['user_id']]);
                $deja_avis = $stmt_deja->fetch();
            }
            ?>
            <div class="modal fade" id="productModal<?= $produit['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars($produit['nom']) ?></h5>
                            <?php if ($moyenne_note): ?>
                                <span class="ms-3">
                                    <span class="badge bg-warning text-dark"><span class="material-symbols-outlined align-middle">star</span> <?= $moyenne_note ?>/5</span>
                                    <span class="text-muted small ms-1">(<?= $nb_avis ?> avis)</span>
                                </span>
                            <?php else: ?>
                                <span class="ms-3 text-muted small">Aucun avis</span>
                            <?php endif; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php if ($img): ?>
                                        <img src="<?= $img ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($produit['nom']) ?>">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <span class="material-symbols-outlined text-muted" style="font-size: 3rem;">medication</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <h6>Description</h6>
                                    <p class="text-muted"><?= htmlspecialchars($produit['description']) ?></p>
                                    <h6>Fabricant</h6>
                                    <p class="text-muted"><?= htmlspecialchars($produit['fabricant']) ?></p>
                                    <?php if ($produit['date_expiration']): ?>
                                        <h6>Date d'expiration</h6>
                                        <p class="text-muted"><?= date('d/m/Y', strtotime($produit['date_expiration'])) ?></p>
                                    <?php endif; ?>
                                    <h6>Prix</h6>
                                    <div class="d-flex align-items-center">
                                        <?php if ($produit['prix_promo'] && $produit['prix_promo'] < $produit['prix']): ?>
                                            <span class="product-price promo me-2"><?= format_fcfa($produit['prix_promo']) ?></span>
                                            <span class="product-price original"><?= format_fcfa($produit['prix']) ?></span>
                                        <?php else: ?>
                                            <span class="product-price"><?= format_fcfa($produit['prix']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($produit['prescription_requise']): ?>
                                        <div class="alert alert-warning mt-3">
                                            <span class="material-symbols-outlined me-2">warning</span>
                                            Ce produit nécessite une ordonnance médicale.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Avis produits -->
                            <hr>
                            <h6 class="mt-4 mb-2">Avis des clients</h6>
                            <?php if ($avis): ?>
                                <ul class="list-group mb-3">
                                    <?php foreach ($avis as $a): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-warning text-dark me-1"><span class="material-symbols-outlined align-middle">star</span> <?= $a['note'] ?>/5</span>
                                                <strong><?= htmlspecialchars($a['user_nom']) ?></strong>
                                                <span class="text-muted small ms-2"><?= date('d/m/Y', strtotime($a['date'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="mt-1"> <?= nl2br(htmlspecialchars($a['commentaire'])) ?> </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-muted mb-3">Aucun avis pour ce produit.</div>
                            <?php endif; ?>
                            <!-- Formulaire d'avis -->
                            <?php if ($avis_message) echo $avis_message; ?>
                            <?php if (isset($_SESSION['user_id']) && !$deja_avis): ?>
                            <form method="post" class="border rounded p-3 bg-light">
                                <input type="hidden" name="avis_produit_id" value="<?= $produit['id'] ?>">
                                <div class="mb-2">
                                    <label class="form-label">Votre note :</label>
                                    <select name="avis_note" class="form-select w-auto d-inline-block" required>
                                        <option value="">Choisir</option>
                                        <?php for ($n=5; $n>=1; $n--): ?>
                                            <option value="<?= $n ?>"><?= $n ?> / 5</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Votre avis :</label>
                                    <textarea name="avis_commentaire" class="form-control" rows="2" maxlength="500" required></textarea>
                                </div>
                                <button type="submit" name="envoyer_avis" class="btn btn-primary">Envoyer mon avis</button>
                            </form>
                            <?php elseif (isset($_SESSION['user_id']) && $deja_avis): ?>
                                <div class="alert alert-info">Vous avez déjà laissé un avis pour ce produit.</div>
                            <?php else: ?>
                                <div class="alert alert-light">Connectez-vous pour laisser un avis.</div>
                            <?php endif; ?>
                            <!-- Fin avis produits -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button class="btn btn-primary add-to-cart" 
                                        data-product-id="<?= $produit['id'] ?>"
                                        data-product-name="<?= htmlspecialchars($produit['nom']) ?>"
                                        data-product-price="<?= $produit['prix_promo'] ?: $produit['prix'] ?>">
                                    <span class="material-symbols-outlined me-1">add_shopping_cart</span>
                                    Ajouter au panier
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">
                                    <span class="material-symbols-outlined me-1">login</span>
                                    Se connecter pour acheter
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-4">Nos Catégories</h3>
        <div class="row g-4">
            <?php foreach ($categories as $categorie): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="pharmacie.php?categorie=<?= $categorie['id'] ?>" class="text-decoration-none">
                    <div class="card text-center h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <?php if (isset($cat_images[$categorie['nom']])): ?>
                                    <img src="<?= $cat_images[$categorie['nom']] ?>" alt="<?= htmlspecialchars($categorie['nom']) ?>" style="width:48px;height:48px;object-fit:contain;">
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-primary">category</span>
                                <?php endif; ?>
                            </div>
                            <h6 class="card-title"><?= htmlspecialchars($categorie['nom']) ?></h6>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
// Gestion du panier
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const productName = this.dataset.productName;
        const productPrice = this.dataset.productPrice;
        
        // Ajouter au panier via AJAX
        fetch('includes/add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour le compteur du panier
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
                
                // Afficher une notification
                showNotification('Produit ajouté au panier !', 'success');
            } else {
                showNotification('Erreur lors de l\'ajout au panier', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Erreur lors de l\'ajout au panier', 'error');
        });
    });
});

function showNotification(message, type) {
    // Créer une notification toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Supprimer le toast après qu'il soit caché
    toast.addEventListener('hidden.bs.toast', () => {
        document.body.removeChild(toast);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>