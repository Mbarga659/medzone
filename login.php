<?php
session_start();

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nom'];
                
                // Redirection spéciale admin
                if (isset($_GET['admin']) && $user['role'] === 'admin') {
                    header('Location: admin/index.php');
                    exit;
                }
                // Redirection normale
                if (isset($_GET['redirect'])) {
                    header('Location: ' . $_GET['redirect']);
                    exit;
                }
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email ou mot de passe incorrect';
            }
        } catch (Exception $e) {
            $error = 'Erreur de connexion à la base de données';
        }
    }
}

$pageTitle = 'Connexion - MedZone';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Favicon -->
    <link rel="icon" href="assets/images/logo.png" type="image/png">
</head>
<body class="bg-light">
    <!-- Navigation simple -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span class="material-symbols-outlined me-2">local_pharmacy</span>
                MedZone
            </a>
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <span class="material-symbols-outlined me-1">home</span>
                Retour à l'accueil
            </a>
        </div>
    </nav>

    <!-- Login Section -->
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-5 col-md-7">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <span class="material-symbols-outlined text-primary" style="font-size: 2.5rem;">login</span>
                            </div>
                            <h2 class="fw-bold">Connexion</h2>
                            <p class="text-muted">Connectez-vous à votre compte MedZone</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <span class="material-symbols-outlined me-2">error</span>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="material-symbols-outlined">email</span>
                                    </span>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                           required 
                                           autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="material-symbols-outlined">lock</span>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <span class="material-symbols-outlined me-2">login</span>
                                Se connecter
                            </button>
                        </form>

                        <div class="text-center">
                            <p class="text-muted mb-2">
                                Pas encore de compte ? 
                                <a href="register.php" class="text-decoration-none">Créer un compte</a>
                            </p>
                            <a href="#" class="text-decoration-none small">Mot de passe oublié ?</a>
                        </div>
                    </div>
                </div>

                <!-- Informations supplémentaires -->
                <div class="text-center mt-4">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="material-symbols-outlined text-success me-2">verified</span>
                                <small class="text-muted">Sécurisé</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="material-symbols-outlined text-info me-2">support_agent</span>
                                <small class="text-muted">Support 24/7</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="material-symbols-outlined text-warning me-2">local_shipping</span>
                                <small class="text-muted">Livraison rapide</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('.material-symbols-outlined');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                password.type = 'password';
                icon.textContent = 'visibility';
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html> 