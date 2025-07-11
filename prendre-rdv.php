<?php
$pageTitle = 'Prendre un rendez-vous - MedZone';
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=prendre-rdv.php');
    exit;
}

// Récupérer les médecins
$pdo = getDB();
$stmt = $pdo->query('SELECT * FROM medecins WHERE actif = 1 ORDER BY nom, prenom');
$medecins = $stmt->fetchAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medecin_id = (int)($_POST['medecin_id'] ?? 0);
    $date_rdv = $_POST['date_rdv'] ?? '';
    $heure_rdv = $_POST['heure_rdv'] ?? '';
    $motif = trim($_POST['motif'] ?? '');
    
    if (!$medecin_id || !$date_rdv || !$heure_rdv) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } else {
        // Vérifier que la date n'est pas dans le passé
        $date_rdv_obj = new DateTime($date_rdv);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($date_rdv_obj < $today) {
            $error = 'La date du rendez-vous ne peut pas être dans le passé';
        } else {
            try {
                // Vérifier si le créneau est disponible
                $stmt = $pdo->prepare('
                    SELECT COUNT(*) FROM rendez_vous 
                    WHERE medecin_id = ? AND date_rdv = ? AND heure_rdv = ? AND statut != "annule"
                ');
                $stmt->execute([$medecin_id, $date_rdv, $heure_rdv]);
                
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Ce créneau n\'est plus disponible. Veuillez choisir un autre horaire.';
                } else {
                    // Créer le rendez-vous
                    $stmt = $pdo->prepare('
                        INSERT INTO rendez_vous (user_id, medecin_id, date_rdv, heure_rdv, motif, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ');
                    
                    if ($stmt->execute([$_SESSION['user_id'], $medecin_id, $date_rdv, $heure_rdv, $motif])) {
                        $success = 'Votre rendez-vous a été pris avec succès ! Vous recevrez une confirmation par email.';
                        $_POST = []; // Vider le formulaire
                    } else {
                        $error = 'Erreur lors de la prise de rendez-vous';
                    }
                }
            } catch (Exception $e) {
                $error = 'Erreur de base de données';
            }
        }
    }
}
?>

<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Prendre un rendez-vous</h1>
                <p class="lead mb-0">Consultez nos spécialistes en toute simplicité. Réservez votre créneau en ligne et recevez une confirmation par email.</p>
            </div>
            <div class="col-lg-4 text-center">
                <span class="material-symbols-outlined" style="font-size: 5rem; opacity: 0.8;">schedule</span>
            </div>
        </div>
    </div>
</section>

<!-- Appointment Form Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <h3 class="mb-4">Formulaire de rendez-vous</h3>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <span class="material-symbols-outlined me-2">check_circle</span>
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <span class="material-symbols-outlined me-2">error</span>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="appointmentForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="medecin_id" class="form-label">Médecin *</label>
                                    <select class="form-select" id="medecin_id" name="medecin_id" required>
                                        <option value="">Sélectionner un médecin</option>
                                        <?php foreach ($medecins as $medecin): ?>
                                            <option value="<?= $medecin['id'] ?>" 
                                                    <?= ($_POST['medecin_id'] ?? '') == $medecin['id'] ? 'selected' : '' ?>>
                                                Dr. <?= htmlspecialchars($medecin['nom']) ?> <?= htmlspecialchars($medecin['prenom']) ?> 
                                                - <?= htmlspecialchars($medecin['specialite']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="date_rdv" class="form-label">Date du rendez-vous *</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="date_rdv" 
                                           name="date_rdv" 
                                           value="<?= htmlspecialchars($_POST['date_rdv'] ?? '') ?>"
                                           min="<?= date('Y-m-d') ?>"
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="heure_rdv" class="form-label">Heure du rendez-vous *</label>
                                    <select class="form-select" id="heure_rdv" name="heure_rdv" required>
                                        <option value="">Sélectionner une heure</option>
                                        <?php
                                        // Générer les créneaux horaires (8h-18h)
                                        for ($hour = 8; $hour <= 18; $hour++) {
                                            $time = sprintf('%02d:00', $hour);
                                            $selected = ($_POST['heure_rdv'] ?? '') === $time ? 'selected' : '';
                                            echo "<option value=\"$time\" $selected>$time</option>";
                                            
                                            if ($hour < 18) {
                                                $time = sprintf('%02d:30', $hour);
                                                $selected = ($_POST['heure_rdv'] ?? '') === $time ? 'selected' : '';
                                                echo "<option value=\"$time\" $selected>$time</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="motif" class="form-label">Motif de consultation</label>
                                    <textarea class="form-control" 
                                              id="motif" 
                                              name="motif" 
                                              rows="3" 
                                              placeholder="Décrivez brièvement le motif de votre consultation..."><?= htmlspecialchars($_POST['motif'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="confirm" required>
                                <label class="form-check-label" for="confirm">
                                    Je confirme que les informations fournies sont exactes et j'accepte les conditions de prise de rendez-vous
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg">
                                <span class="material-symbols-outlined me-2">schedule</span>
                                Confirmer le rendez-vous
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Informations utiles -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <span class="material-symbols-outlined me-2 text-primary">info</span>
                            Informations importantes
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="material-symbols-outlined text-success me-2">check_circle</span>
                                Rendez-vous confirmés par email
                            </li>
                            <li class="mb-2">
                                <span class="material-symbols-outlined text-warning me-2">schedule</span>
                                Durée moyenne : 30 minutes
                            </li>
                            <li class="mb-2">
                                <span class="material-symbols-outlined text-info me-2">cancel</span>
                                Annulation possible 24h avant
                            </li>
                            <li class="mb-2">
                                <span class="material-symbols-outlined text-primary me-2">location_on</span>
                                Consultation sur place
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Horaires d'ouverture -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <span class="material-symbols-outlined me-2 text-primary">schedule</span>
                            Horaires d'ouverture
                        </h5>
                        <div class="row">
                            <div class="col-6">
                                <strong>Lundi - Vendredi</strong><br>
                                8h00 - 18h00
                            </div>
                            <div class="col-6">
                                <strong>Samedi</strong><br>
                                8h00 - 12h00
                            </div>
                        </div>
                        <hr>
                        <small class="text-muted">
                            <span class="material-symbols-outlined me-1">phone</span>
                            Pour les urgences : 01 23 45 67 89
                        </small>
                    </div>
                </div>

                <!-- Mes rendez-vous -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <span class="material-symbols-outlined me-2 text-primary">event</span>
                            Mes rendez-vous
                        </h5>
                        <p class="text-muted">Consultez et gérez vos rendez-vous existants.</p>
                        <a href="mes-rendez-vous.php" class="btn btn-outline-primary btn-sm">
                            <span class="material-symbols-outlined me-1">list</span>
                            Voir mes RDV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Available Doctors Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-5">Nos Médecins Disponibles</h3>
        <div class="row g-4">
            <?php foreach ($medecins as $medecin): ?>
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <span class="material-symbols-outlined text-primary" style="font-size: 2rem;">person</span>
                        </div>
                        <h5 class="card-title">Dr. <?= htmlspecialchars($medecin['nom']) ?> <?= htmlspecialchars($medecin['prenom']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($medecin['specialite']) ?></p>
                        <?php if ($medecin['description']): ?>
                            <p class="card-text small"><?= htmlspecialchars(substr($medecin['description'], 0, 100)) ?>...</p>
                        <?php endif; ?>
                        <button class="btn btn-outline-primary btn-sm" 
                                onclick="selectDoctor(<?= $medecin['id'] ?>, '<?= htmlspecialchars($medecin['nom']) ?> <?= htmlspecialchars($medecin['prenom']) ?>')">
                            <span class="material-symbols-outlined me-1">schedule</span>
                            Choisir ce médecin
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
// Sélectionner un médecin depuis la liste
function selectDoctor(doctorId, doctorName) {
    document.getElementById('medecin_id').value = doctorId;
    
    // Afficher une notification
    MedZone.showNotification(`Médecin sélectionné : Dr. ${doctorName}`, 'success');
    
    // Scroll vers le formulaire
    document.getElementById('appointmentForm').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
}

// Validation du formulaire
document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    const dateRdv = document.getElementById('date_rdv').value;
    const heureRdv = document.getElementById('heure_rdv').value;
    const medecinId = document.getElementById('medecin_id').value;
    
    if (!medecinId) {
        e.preventDefault();
        MedZone.showNotification('Veuillez sélectionner un médecin', 'error');
        return;
    }
    
    if (!dateRdv) {
        e.preventDefault();
        MedZone.showNotification('Veuillez sélectionner une date', 'error');
        return;
    }
    
    if (!heureRdv) {
        e.preventDefault();
        MedZone.showNotification('Veuillez sélectionner une heure', 'error');
        return;
    }
    
    // Vérifier que la date n'est pas dans le passé
    const selectedDate = new Date(dateRdv);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        e.preventDefault();
        MedZone.showNotification('La date du rendez-vous ne peut pas être dans le passé', 'error');
        return;
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

<?php require_once 'includes/footer.php'; ?> 