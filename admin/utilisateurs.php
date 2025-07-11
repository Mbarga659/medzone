<?php
session_start();
require_once '../config/database.php';

// Sécurité admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/utilisateurs.php');
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

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    if ($stmt->execute([$id])) {
        if ($id == $_SESSION['user_id']) {
            session_destroy();
            header('Location: ../index.php');
            exit;
        }
        $success = 'Utilisateur supprimé avec succès.';
    } else {
        $error = 'Erreur lors de la suppression.';
    }
}

// Modification du rôle
if (isset($_POST['role']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $role = $_POST['role'];
    if (in_array($role, ['admin', 'client', 'pharmacien'])) {
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        if ($stmt->execute([$role, $id])) {
            $success = 'Rôle mis à jour.';
        } else {
            $error = 'Erreur lors de la modification du rôle.';
        }
    }
}

// Ajout d'utilisateur
if (isset($_POST['add_user'])) {
    $nom = trim($_POST['add_nom'] ?? '');
    $email = trim($_POST['add_email'] ?? '');
    $telephone = trim($_POST['add_telephone'] ?? '');
    $password = $_POST['add_password'] ?? '';
    $role = $_POST['add_role'] ?? 'client';
    if ($nom && $email && $password && in_array($role, ['admin','client','pharmacien'])) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (nom, email, password, telephone, role) VALUES (?, ?, ?, ?, ?)');
        if ($stmt->execute([$nom, $email, $hash, $telephone, $role])) {
            $success = "Nouvel utilisateur ajouté avec succès.";
        } else {
            $error = "Erreur lors de l'ajout (email déjà utilisé ?).";
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}

// Récupérer les utilisateurs
$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
    function confirmDeleteSelf() {
        return confirm('Êtes-vous sûr de vouloir supprimer votre propre compte administrateur ? Cette action est irréversible. Vous serez déconnecté.');
    }
    </script>
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
            <li class="nav-item mb-2"><a href="utilisateurs.php" class="nav-link text-white fw-bold"><span class="material-symbols-outlined me-1">group</span> Utilisateurs</a></li>
            <li class="nav-item mb-2"><a href="commandes.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">receipt_long</span> Commandes</a></li>
            <li class="nav-item mb-2"><a href="stats.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">insights</span> Statistiques</a></li>
            <li class="nav-item mb-2"><a href="stocks.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">inventory_2</span> Stocks</a></li>
            <li class="nav-item mt-4"><a href="../index.php" class="nav-link text-white"><span class="material-symbols-outlined me-1">logout</span> Retour au site</a></li>
        </ul>
    </nav>
    <!-- Contenu principal -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Gestion des utilisateurs</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <!-- Formulaire d'ajout d'utilisateur -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Ajouter un utilisateur</h5>
                <form method="post" class="row g-3">
                    <input type="hidden" name="add_user" value="1">
                    <div class="col-md-3">
                        <input type="text" name="add_nom" class="form-control" placeholder="Nom complet" required>
                    </div>
                    <div class="col-md-3">
                        <input type="email" name="add_email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="add_telephone" class="form-control" placeholder="Téléphone">
                    </div>
                    <div class="col-md-2">
                        <input type="password" name="add_password" class="form-control" placeholder="Mot de passe" required>
                    </div>
                    <div class="col-md-2">
                        <select name="add_role" class="form-select" required>
                            <option value="client">Client</option>
                            <option value="admin">Admin</option>
                            <option value="pharmacien">Pharmacien</option>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Liste des utilisateurs</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Rôle</th>
                                <th>Date inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['nom']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['telephone']) ?></td>
                                <td>
                                    <form method="post" class="d-flex align-items-center gap-2 mb-0">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" <?= $u['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                            <option value="client" <?= $u['role']=='client'?'selected':'' ?>>Client</option>
                                            <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                            <option value="pharmacien" <?= $u['role']=='pharmacien'?'selected':'' ?>>Pharmacien</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return <?= $u['id'] == $_SESSION['user_id'] ? 'confirmDeleteSelf()' : 'confirm(\'Supprimer cet utilisateur ?\')' ?>;">Supprimer</a>
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