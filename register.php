<?php
session_start();

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telephone = trim($_POST['telephone'] ?? '');
    $genre = $_POST['genre'] ?? '';
    
    // Validation
    if (empty($nom) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } else {
        try {
            $pdo = getDB();
            
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Cette adresse email est déjà utilisée';
            } else {
                // Créer le compte
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare('
                    INSERT INTO users (nom, email, password, telephone, genre, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ');
                
                if ($stmt->execute([$nom, $email, $hashed_password, $telephone, $genre])) {
                    $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                    
                    // Vider le formulaire
                    $_POST = [];
                } else {
                    $error = 'Erreur lors de la création du compte';
                }
            }
        } catch (Exception $e) {
            $error = 'Erreur de connexion à la base de données';
        }
    }
}

$pageTitle = 'Inscription - MedZone';
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

    <!-- Register Section -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <span class="material-symbols-outlined text-success" style="font-size: 2.5rem;">person_add</span>
                            </div>
                            <h2 class="fw-bold">Créer un compte</h2>
                            <p class="text-muted">Rejoignez MedZone pour accéder à nos services</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <span class="material-symbols-outlined me-2">error</span>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <span class="material-symbols-outlined me-2">check_circle</span>
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="registerForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom complet *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-symbols-outlined">person</span>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="nom" 
                                               name="nom" 
                                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Adresse email *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-symbols-outlined">email</span>
                                        </span>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-symbols-outlined">phone</span>
                                        </span>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="telephone" 
                                               name="telephone" 
                                               value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="genre" class="form-label">Genre</label>
                                    <select class="form-select" id="genre" name="genre">
                                        <option value="">Sélectionner</option>
                                        <option value="Homme" <?= ($_POST['genre'] ?? '') === 'Homme' ? 'selected' : '' ?>>Homme</option>
                                        <option value="Femme" <?= ($_POST['genre'] ?? '') === 'Femme' ? 'selected' : '' ?>>Femme</option>
                                        <option value="Autre" <?= ($_POST['genre'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Mot de passe *</label>
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
                                    <div class="form-text">
                                        <small>Minimum 6 caractères</small>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="material-symbols-outlined">lock_reset</span>
                                        </span>
                                        <input type="password" 
                                               class="form-control" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               required>
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                id="toggleConfirmPassword">
                                            <span class="material-symbols-outlined">visibility</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a> 
                                    et la <a href="#" class="text-decoration-none">politique de confidentialité</a>
                                </label>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Je souhaite recevoir les newsletters et offres spéciales
                                </label>
                            </div>

                            <button type="submit" class="btn btn-success w-100 mb-3">
                                <span class="material-symbols-outlined me-2">person_add</span>
                                Créer mon compte
                            </button>
                        </form>

                        <div class="text-center">
                            <p class="text-muted mb-0">
                                Déjà un compte ? 
                                <a href="login.php" class="text-decoration-none">Se connecter</a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Avantages -->
                <div class="row mt-4 g-3">
                    <div class="col-md-4">
                        <div class="card border-0 bg-primary bg-opacity-10 text-center">
                            <div class="card-body">
                                <span class="material-symbols-outlined text-primary mb-2">local_shipping</span>
                                <h6>Livraison gratuite</h6>
                                <small class="text-muted">À partir de 50€</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-success bg-opacity-10 text-center">
                            <div class="card-body">
                                <span class="material-symbols-outlined text-success mb-2">verified</span>
                                <h6>Produits certifiés</h6>
                                <small class="text-muted">100% authentiques</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-info bg-opacity-10 text-center">
                            <div class="card-body">
                                <span class="material-symbols-outlined text-info mb-2">support_agent</span>
                                <h6>Support 24/7</h6>
                                <small class="text-muted">Assistance en ligne</small>
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
        function togglePasswordVisibility(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            const icon = button.querySelector('.material-symbols-outlined');
            
            button.addEventListener('click', function() {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.textContent = 'visibility_off';
                } else {
                    input.type = 'password';
                    icon.textContent = 'visibility';
                }
            });
        }

        togglePasswordVisibility('password', 'togglePassword');
        togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                this.setCustomValidity('');
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