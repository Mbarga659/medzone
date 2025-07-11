<?php
$pageTitle = 'Accueil - MedZone';
require_once 'includes/header.php';

// Récupérer les médecins
$pdo = getDB();
$stmt = $pdo->query('SELECT * FROM medecins WHERE actif = 1 LIMIT 4');
$medecins = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center min-vh-100" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/img/acceuil.jpg') center/cover;">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold mb-4">Bienvenue sur MedZone</h1>
        <p class="lead mb-4">Votre pharmacie en ligne de confiance pour tous vos besoins de santé</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="pharmacie.php" class="btn btn-primary btn-lg">
                <span class="material-symbols-outlined me-2">shopping_cart</span>
                Voir nos produits
            </a>
            <a href="#doctors" class="btn btn-outline-light btn-lg">
                <span class="material-symbols-outlined me-2">medical_services</span>
                Nos médecins
            </a>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5 bg-light" id="about">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Pourquoi choisir MedZone ?</h2>
                <p class="lead text-muted">Nous vous offrons des services de qualité pour prendre soin de votre santé</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <span class="material-symbols-outlined text-primary" style="font-size: 2.5rem;">local_shipping</span>
                        </div>
                        <h5 class="card-title">Livraison Rapide</h5>
                        <p class="card-text text-muted">Livraison à domicile en 24-48h dans toute la France métropolitaine</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <span class="material-symbols-outlined text-success" style="font-size: 2.5rem;">verified</span>
                        </div>
                        <h5 class="card-title">Produits Certifiés</h5>
                        <p class="card-text text-muted">Tous nos produits sont authentiques et certifiés par les autorités sanitaires</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <span class="material-symbols-outlined text-info" style="font-size: 2.5rem;">support_agent</span>
                        </div>
                        <h5 class="card-title">Conseils Experts</h5>
                        <p class="card-text text-muted">Nos pharmaciens sont disponibles pour vous conseiller 7j/7</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5" id="faq">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Questions Fréquentes</h2>
                <p class="lead text-muted">Trouvez rapidement les réponses à vos questions</p>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Comment passer une commande de médicaments ?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Vous pouvez facilement vérifier la disponibilité des médicaments sur notre site. 
                                Sélectionnez les articles souhaités, ajoutez-les au panier et suivez les étapes de validation de commande.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Quels sont les délais de livraison ?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                La livraison prend généralement de 24 à 72 heures, selon votre localisation et la disponibilité des produits.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Ai-je besoin d'une ordonnance pour commander ?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Certains médicaments nécessitent une ordonnance valide. Lors de votre commande, il vous sera demandé d'uploader votre prescription pour validation.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Doctors Section -->
<section class="py-5 bg-light" id="doctors">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Nos Médecins Spécialistes</h2>
                <p class="lead text-muted">Une équipe de professionnels qualifiés pour vous accompagner</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($medecins as $medecin): ?>
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex p-3 mb-3" style="width: 80px; height: 80px;">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 2rem;">person</span>
                        </div>
                        <h5 class="card-title">Dr. <?= htmlspecialchars($medecin['nom']) ?> <?= htmlspecialchars($medecin['prenom']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($medecin['specialite']) ?></p>
                        <a href="prendre-rdv.php?medecin=<?= $medecin['id'] ?>" class="btn btn-outline-primary btn-sm">
                            Prendre RDV
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Appointment Section -->
<section class="py-5" id="rdv">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Prenez rendez-vous avec nos médecins</h2>
                <p class="lead text-muted mb-4">
                    Consultez nos spécialistes en toute simplicité. Réservez votre créneau en ligne 
                    et recevez une confirmation par email.
                </p>
                <div class="d-flex gap-3">
                    <a href="prendre-rdv.php" class="btn btn-primary btn-lg">
                        <span class="material-symbols-outlined me-2">schedule</span>
                        Prendre RDV
                    </a>
                    <a href="tel:0123456789" class="btn btn-outline-secondary btn-lg">
                        <span class="material-symbols-outlined me-2">phone</span>
                        Appeler
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Formulaire de contact rapide</h5>
                        <form action="includes/contact.php" method="post">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="nom" placeholder="Votre nom" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Votre email" required>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="message" rows="3" placeholder="Votre message"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Envoyer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5 bg-light" id="contact">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Contactez-nous</h2>
                <p class="lead text-muted">Notre équipe est là pour vous aider</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <span class="material-symbols-outlined text-primary" style="font-size: 2rem;">location_on</span>
                    </div>
                    <h5>Adresse</h5>
                    <p class="text-muted">(IUS) Institut Universitaire de SIANTOU<br>Coron Yaoundé, Cameroun</p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <span class="material-symbols-outlined text-success" style="font-size: 2rem;">phone</span>
                    </div>
                    <h5>Téléphone</h5>
                    <p class="text-muted">+237 659 417 568<br>Lun-Sam: 8h-20h</p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="text-center">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <span class="material-symbols-outlined text-info" style="font-size: 2rem;">email</span>
                    </div>
                    <h5>Email</h5>
                    <p class="text-muted">mbargacharles53@gmail.com<br>mbargacharles53@gmail.com</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 