<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php');
    exit;
}
$pdo = getDB();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: login.php');
    exit;
}
$pageTitle = 'Mon Profil - MedZone';
require_once 'includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <h1 class="mb-4">Mon Profil</h1>
        <div class="card mb-4">
            <div class="card-body">
                <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
                <a href="#" class="btn btn-primary disabled">Modifier mes informations</a>
            </div>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">Retour à l'accueil</a>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?> 