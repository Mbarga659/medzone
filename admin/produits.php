<?php
session_start();
require_once '../config/database.php';

// Sécurité admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/produits.php');
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

// Gestion des actions (ajout, modification, suppression)
$success = $error = '';
$edit_product = null;

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM produits WHERE id = ?');
    if ($stmt->execute([$id])) {
        $success = 'Produit supprimé avec succès.';
    } else {
        $error = 'Erreur lors de la suppression.';
    }
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $prix_promo = $_POST['prix_promo'] !== '' ? floatval($_POST['prix_promo']) : null;
    $stock = intval($_POST['stock'] ?? 0);
    $categorie_id = intval($_POST['categorie_id'] ?? 0);
    $fabricant = trim($_POST['fabricant'] ?? '');
    $prescription = isset($_POST['prescription_requise']) ? 1 : 0;
    $actif = isset($_POST['actif']) ? 1 : 0;
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    // Image (optionnel)
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid('prod_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $image);
    }
    if ($nom && $prix > 0 && $stock >= 0 && $categorie_id > 0) {
        if ($id > 0) {
            // Modification
            $sql = 'UPDATE produits SET nom=?, description=?, prix=?, prix_promo=?, stock=?, categorie_id=?, fabricant=?, prescription_requise=?, actif=?';
            $params = [$nom, $description, $prix, $prix_promo, $stock, $categorie_id, $fabricant, $prescription, $actif];
            if ($image) {
                $sql .= ', image=?';
                $params[] = $image;
            }
            $sql .= ' WHERE id=?';
            $params[] = $id;
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $success = 'Produit modifié avec succès.';
            } else {
                $error = 'Erreur lors de la modification.';
            }
        } else {
            // Ajout
            $stmt = $pdo->prepare('INSERT INTO produits (nom, description, prix, prix_promo, stock, categorie_id, fabricant, prescription_requise, actif, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            if ($stmt->execute([$nom, $description, $prix, $prix_promo, $stock, $categorie_id, $fabricant, $prescription, $actif, $image])) {
                $success = 'Produit ajouté avec succès.';
            } else {
                $error = 'Erreur lors de l\'ajout.';
            }
        }
    } else {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    }
}

// Pré-remplir le formulaire en cas de modification
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM produits WHERE id = ?');
    $stmt->execute([$id]);
    $edit_product = $stmt->fetch();
}

// Récupérer les produits et catégories
$produits = $pdo->query('SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id ORDER BY p.created_at DESC')->fetchAll();
$categories = $pdo->query('SELECT * FROM categories ORDER BY nom')->fetchAll();

function format_fcfa($prix) {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Produits</title>
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
            <li class="nav-item mb-2"><a href="produits.php" class="nav-link text-white fw-bold"><span class="material-symbols-outlined me-1">medication</span> Produits</a></li>
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
        <h2 class="mb-4">Gestion des produits</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <!-- Formulaire ajout/modification -->
        <div class="card mb-4">
            <div class="card-body">
                <h5><?= $edit_product ? 'Modifier le produit' : 'Ajouter un produit' ?></h5>
                <form method="post" enctype="multipart/form-data">
                    <?php if ($edit_product): ?><input type="hidden" name="id" value="<?= $edit_product['id'] ?>"><?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($edit_product['nom'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Catégorie *</label>
                            <select name="categorie_id" class="form-control" required>
                                <option value="">Choisir...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (isset($edit_product['categorie_id']) && $edit_product['categorie_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prix (FCFA) *</label>
                            <input type="number" name="prix" class="form-control" required min="0" step="0.01" value="<?= htmlspecialchars($edit_product['prix'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prix promo (FCFA)</label>
                            <input type="number" name="prix_promo" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($edit_product['prix_promo'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock *</label>
                            <input type="number" name="stock" class="form-control" required min="0" value="<?= htmlspecialchars($edit_product['stock'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fabricant</label>
                            <input type="text" name="fabricant" class="form-control" value="<?= htmlspecialchars($edit_product['fabricant'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control">
                            <?php if (!empty($edit_product['image'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($edit_product['image']) ?>" alt="Image" class="img-fluid mt-2" style="max-height:60px;">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" name="prescription_requise" id="presc" <?= !empty($edit_product['prescription_requise']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="presc">Ordonnance requise</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="actif" id="actif" <?= !isset($edit_product['actif']) || $edit_product['actif'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="actif">Actif</label>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end">
                            <button type="submit" class="btn btn-success w-100"><?= $edit_product ? 'Enregistrer' : 'Ajouter' ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Liste des produits -->
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Liste des produits</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Actif</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produits as $prod): ?>
                            <tr>
                                <td><?= $prod['id'] ?></td>
                                <td><?= htmlspecialchars($prod['nom']) ?></td>
                                <td><?= htmlspecialchars($prod['categorie_nom']) ?></td>
                                <td><?= format_fcfa($prod['prix']) ?></td>
                                <td><?= $prod['stock'] ?></td>
                                <td><?= $prod['actif'] ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-danger">Non</span>' ?></td>
                                <td>
                                    <a href="?edit=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                                    <a href="?delete=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce produit ?');">Supprimer</a>
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