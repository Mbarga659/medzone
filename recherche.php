<?php
$pageTitle = 'Recherche - MedZone';
require_once 'includes/header.php';

$query = trim($_GET['q'] ?? '');
$categorie_id = (int)($_GET['categorie'] ?? 0);
$produits = [];
$total_results = 0;

if ($query || $categorie_id) {
    try {
        $pdo = getDB();
        
        $sql = 'SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.actif = 1';
        $params = [];
        
        if ($query) {
            $sql .= ' AND (p.nom LIKE ? OR p.description LIKE ? OR p.fabricant LIKE ?)';
            $search_term = '%' . $query . '%';
            $params = [$search_term, $search_term, $search_term];
        }
        
        if ($categorie_id) {
            $sql .= ' AND p.categorie_id = ?';
            $params[] = $categorie_id;
        }
        
        $sql .= ' ORDER BY p.created_at DESC';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $produits = $stmt->fetchAll();
        $total_results = count($produits);
    } catch (Exception $e) {
        $error = 'Erreur lors de la recherche';
    }
}

// Récupérer les catégories pour le filtre
$pdo = getDB();
$stmt = $pdo->query('SELECT * FROM categories ORDER BY nom');
$categories = $stmt->fetchAll();

function format_fcfa($prix) {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
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
                        <input type="text" 
                               class="form-control" 
                               name="q" 
                               value="<?= htmlspecialchars($query) ?>"
                               placeholder="Nom du médicament, fabricant..." 
                               required>
                        <button class="btn btn-light" type="submit">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Results Section -->
<section class="py-5">
    <div class="container">
        <!-- Filtres et résultats -->
        <div class="row mb-4">
            <div class="col-lg-3">
                <!-- Filtres -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <span class="material-symbols-outlined me-2">filter_list</span>
                            Filtres
                        </h5>
                        
                        <form action="recherche.php" method="GET">
                            <?php if ($query): ?>
                                <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Catégorie</label>
                                <select class="form-select" name="categorie" onchange="this.form.submit()">
                                    <option value="">Toutes les catégories</option>
                                    <?php foreach ($categories as $categorie): ?>
                                        <option value="<?= $categorie['id'] ?>" 
                                                <?= $categorie_id == $categorie['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categorie['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Prix</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="min_price" placeholder="Min" min="0">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="max_price" placeholder="Max" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="promo" id="promo">
                                    <label class="form-check-label" for="promo">
                                        En promotion uniquement
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <span class="material-symbols-outlined me-1">search</span>
                                Appliquer les filtres
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <!-- Résultats -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-1">
                            <?php if ($query): ?>
                                Résultats pour "<?= htmlspecialchars($query) ?>"
                            <?php elseif ($categorie_id): ?>
                                <?php 
                                $categorie_nom = '';
                                foreach ($categories as $cat) {
                                    if ($cat['id'] == $categorie_id) {
                                        $categorie_nom = $cat['nom'];
                                        break;
                                    }
                                }
                                ?>
                                Catégorie : <?= htmlspecialchars($categorie_nom) ?>
                            <?php else: ?>
                                Tous nos produits
                            <?php endif; ?>
                        </h3>
                        <p class="text-muted mb-0">
                            <?= $total_results ?> produit<?= $total_results > 1 ? 's' : '' ?> trouvé<?= $total_results > 1 ? 's' : '' ?>
                        </p>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <span class="material-symbols-outlined me-1">sort</span>
                            Trier par
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'name'])) ?>">Nom A-Z</a></li>
                            <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_asc'])) ?>">Prix croissant</a></li>
                            <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_desc'])) ?>">Prix décroissant</a></li>
                            <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['sort' => 'newest'])) ?>">Plus récents</a></li>
                        </ul>
                    </div>
                </div>

                <?php if (empty($produits)): ?>
                    <!-- Aucun résultat -->
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <span class="material-symbols-outlined text-muted" style="font-size: 4rem;">search_off</span>
                        </div>
                        <h4 class="text-muted">Aucun produit trouvé</h4>
                        <p class="text-muted mb-4">
                            <?php if ($query): ?>
                                Aucun produit ne correspond à votre recherche "<?= htmlspecialchars($query) ?>".
                            <?php else: ?>
                                Aucun produit disponible dans cette catégorie.
                            <?php endif; ?>
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="pharmacie.php" class="btn btn-primary">
                                <span class="material-symbols-outlined me-1">shopping_bag</span>
                                Voir tous les produits
                            </a>
                            <a href="recherche.php" class="btn btn-outline-secondary">
                                <span class="material-symbols-outlined me-1">clear</span>
                                Effacer les filtres
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Grille de produits -->
                    <div class="row g-4">
                        <?php foreach ($produits as $produit): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card product-card h-100">
                                    <div class="position-relative">
                                        <?php if ($produit['image']): ?>
                                            <img src="uploads/<?= htmlspecialchars($produit['image']) ?>" 
                                                 class="card-img-top" 
                                                 alt="<?= htmlspecialchars($produit['nom']) ?>">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <span class="material-symbols-outlined text-muted" style="font-size: 3rem;">medication</span>
                                            </div>
                                        <?php endif; ?>
                                        
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
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
// Gestion du panier (même code que dans pharmacie.php)
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const productName = this.dataset.productName;
        const productPrice = this.dataset.productPrice;
        
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
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
                MedZone.showNotification('Produit ajouté au panier !', 'success');
            } else {
                MedZone.showNotification('Erreur lors de l\'ajout au panier', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            MedZone.showNotification('Erreur lors de l\'ajout au panier', 'error');
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 