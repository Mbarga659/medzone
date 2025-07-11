-- MedZone Database Schema
-- Application de Pharmacie en Ligne

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS medzone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medzone;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    date_naissance DATE,
    genre ENUM('Homme', 'Femme', 'Autre'),
    role ENUM('client', 'admin', 'pharmacien') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email(100))
);

-- Table des catégories de produits
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des produits
CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    prix_promo DECIMAL(10,2),
    stock INT DEFAULT 0,
    image VARCHAR(255),
    categorie_id INT,
    fabricant VARCHAR(255),
    date_expiration DATE,
    prescription_requise BOOLEAN DEFAULT FALSE,
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Table des médecins
CREATE TABLE medecins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    specialite VARCHAR(255),
    telephone VARCHAR(20),
    email VARCHAR(100),
    image VARCHAR(255),
    description TEXT,
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des rendez-vous
CREATE TABLE rendez_vous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medecin_id INT NOT NULL,
    date_rdv DATE NOT NULL,
    heure_rdv TIME NOT NULL,
    motif TEXT,
    statut ENUM('en_attente', 'confirme', 'annule', 'termine') DEFAULT 'en_attente',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE
);

-- Table des commandes
CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    statut ENUM('en_attente', 'confirme', 'expedie', 'livre', 'annule') DEFAULT 'en_attente',
    adresse_livraison TEXT,
    telephone_livraison VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des détails de commande
CREATE TABLE commande_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
);

-- Insertion des données de base

-- Catégories
INSERT INTO categories (nom, description) VALUES
('Analgésiques', 'Médicaments pour soulager la douleur'),
('Antibiotiques', 'Médicaments pour traiter les infections'),
('Vitamines', 'Compléments vitaminiques'),
('Soins de la peau', 'Produits dermatologiques'),
('Premiers soins', 'Matériel de premiers secours');

-- Médecins
INSERT INTO medecins (nom, prenom, specialite, telephone, email, description) VALUES
('Dupont', 'Marie', 'Médecin généraliste', '0123456789', 'marie.dupont@medzone.com', 'Spécialisée en médecine générale avec 15 ans d\'expérience'),
('Martin', 'Pierre', 'Cardiologue', '0123456790', 'pierre.martin@medzone.com', 'Expert en cardiologie et maladies cardiovasculaires'),
('Bernard', 'Sophie', 'Dermatologue', '0123456791', 'sophie.bernard@medzone.com', 'Spécialiste en dermatologie et esthétique'),
('Leroy', 'Jean', 'Pédiatre', '0123456792', 'jean.leroy@medzone.com', 'Médecin spécialisé dans les soins aux enfants');

-- Produits d'exemple
INSERT INTO produits (nom, description, prix, stock, categorie_id, fabricant, prescription_requise) VALUES
('Paracétamol 500mg', 'Antidouleur et antipyrétique', 5.50, 100, 1, 'Laboratoire Pharma', FALSE),
('Ibuprofène 400mg', 'Anti-inflammatoire non stéroïdien', 6.80, 75, 1, 'Laboratoire Pharma', FALSE),
('Vitamine C 1000mg', 'Complément vitaminique', 12.90, 50, 3, 'Vitamines Plus', FALSE),
('Crème hydratante', 'Hydratation intensive de la peau', 15.50, 30, 4, 'DermaCare', FALSE),
('Pansements stériles', 'Pansements adhésifs stériles', 8.90, 200, 5, 'FirstAid', FALSE); 