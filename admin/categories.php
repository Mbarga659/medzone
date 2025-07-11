<?php
session_start();
require_once '../config/database.php';

// Sécurité admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/categories.php');
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
$edit_cat = null;

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    if ($stmt->execute([$id])) {
        $success = 'Catégorie supprimée avec succès.';
    } else {
        $error = 'Erreur lors de la suppression.';
    }
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($nom) {
        if ($id > 0) {
            // Modification
            $stmt = $pdo->prepare('UPDATE categories SET nom=?, description=? WHERE id=?');
            if ($stmt->execute([$nom, $description, $id])) {
                $success = 'Catégorie modifiée avec succès.';
            } else {
                $error = 'Erreur lors de la modification.';
            }
        } else {
            // Ajout
            $stmt = $pdo->prepare('INSERT INTO categories (nom, description) VALUES (?, ?)');
            if ($stmt->execute([$nom, $description])) {
                $success = 'Catégorie ajoutée avec succès.';
            } else {
                $error = 'Erreur lors de l\'ajout.';
            }
        }
    } else {
        $error = 'Le nom est obligatoire.';
    }
}

// Pré-remplir le formulaire en cas de modification
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $edit_cat = $stmt->fetch();
}

// Récupérer les catégories
$categories = $pdo->query('SELECT * FROM categories ORDER BY nom')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Catégories</title>
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
            <li class="nav-item mb-2"><a href="categories.php" class="nav-link text-white fw-bold"><span class="material-symbols-outlined me-1">category</span> Catégories</a></li>
            <li class="nav-item mb-2"><a href="utilisateurs.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">group</span> Utilisateurs</a></li>
            <li class="nav-item mb-2"><a href="commandes.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">receipt_long</span> Commandes</a></li>
            <li class="nav-item mb-2"><a href="stats.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">insights</span> Statistiques</a></li>
            <li class="nav-item mb-2"><a href="stocks.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">inventory_2</span> Stocks</a></li>
            <li class="nav-item mt-4"><a href="../index.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">logout</span> Retour au site</a></li>
        </ul>
    </nav>
    <!-- Contenu principal -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Gestion des catégories</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <!-- Formulaire ajout/modification -->
        <div class="card mb-4">
            <div class="card-body">
                <h5><?= $edit_cat ? 'Modifier la catégorie' : 'Ajouter une catégorie' ?></h5>
                <form method="post">
                    <?php if ($edit_cat): ?><input type="hidden" name="id" value="<?= $edit_cat['id'] ?>"><?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($edit_cat['nom'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($edit_cat['description'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end">
                            <button type="submit" class="btn btn-success w-100"><?= $edit_cat ? 'Enregistrer' : 'Ajouter' ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Liste des catégories -->
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Liste des catégories</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= $cat['id'] ?></td>
                                <td><?= htmlspecialchars($cat['nom']) ?></td>
                                <td><?= htmlspecialchars($cat['description']) ?></td>
                                <td>
                                    <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                                    <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette catégorie ?');">Supprimer</a>
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