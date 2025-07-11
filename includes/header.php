<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Récupérer les informations de l'utilisateur connecté
$user = null;
if (isset($_SESSION['user_id'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    // Redirection automatique admin (sauf si déjà dans /admin/)
    if ($user && $user['role'] === 'admin' && strpos($_SERVER['REQUEST_URI'], '/admin/') === false) {
        header('Location: admin/index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'MedZone - Pharmacie en Ligne' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Favicon -->
    <link rel="icon" href="assets/images/logo.png" type="image/png">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span class="material-symbols-outlined me-2">local_pharmacy</span>
                MedZone
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#about">À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pharmacie.php">Pharmacie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#doctors">Médecins</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-bold" href="login.php?admin=1">
                            <span class="material-symbols-outlined me-1">admin_panel_settings</span>
                            Espace Admin
                        </a>
                    </li>
                    <?php if ($user): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="material-symbols-outlined me-1">account_circle</span>
                                <?= htmlspecialchars($user['nom']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Mon Profil</a></li>
                                <li><a class="dropdown-item" href="mes-commandes.php">Mes Commandes</a></li>
                                <li><a class="dropdown-item" href="prendre-rdv.php">Mes Rendez-vous</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Déconnexion</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="panier.php">
                                <span class="material-symbols-outlined">shopping_cart</span>
                                <span class="badge bg-danger" id="cart-count">0</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm ms-2" href="register.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Espace pour le contenu principal -->
    <main class="main-content"> 