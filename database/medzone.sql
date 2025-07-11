-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 02 juil. 2025 à 15:52
-- Version du serveur : 9.1.0
-- Version de PHP : 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `medzone`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produit_id` int NOT NULL,
  `user_id` int NOT NULL,
  `note` tinyint NOT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('en_attente','valide','masque') COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_avis` (`produit_id`,`user_id`),
  KEY `fk_avis_user` (`user_id`)
) ;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id`, `produit_id`, `user_id`, `note`, `commentaire`, `date`, `statut`) VALUES
(1, 5, 1, 4, 'éfficace', '2025-07-02 15:52:51', 'en_attente');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `description`, `created_at`) VALUES
(1, 'Analgésiques', 'Médicaments pour soulager la douleur', '2025-07-01 02:56:48'),
(2, 'Antibiotiques', 'Médicaments pour traiter les infections', '2025-07-01 02:56:48'),
(3, 'Vitamines', 'Compléments vitaminiques', '2025-07-01 02:56:48'),
(4, 'Soins de la peau', 'Produits dermatologiques', '2025-07-01 02:56:48'),
(5, 'Premiers soins', 'Matériel de premiers secours', '2025-07-01 02:56:48');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

DROP TABLE IF EXISTS `commandes`;
CREATE TABLE IF NOT EXISTS `commandes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','confirme','expedie','livre','annule') COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `adresse_livraison` text COLLATE utf8mb4_unicode_ci,
  `telephone_livraison` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `mode_paiement` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'livraison',
  `numero_paiement` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `user_id`, `total`, `statut`, `adresse_livraison`, `telephone_livraison`, `notes`, `created_at`, `updated_at`, `mode_paiement`, `numero_paiement`) VALUES
(1, 1, 2500.00, 'en_attente', 'odza', '659417568', NULL, '2025-07-02 14:00:33', '2025-07-02 14:00:33', 'livraison', NULL),
(2, 1, 3900.00, 'en_attente', 'odza', '659 417 568', NULL, '2025-07-02 15:16:45', '2025-07-02 15:16:45', 'mobile', '+237659417568');

-- --------------------------------------------------------

--
-- Structure de la table `commande_details`
--

DROP TABLE IF EXISTS `commande_details`;
CREATE TABLE IF NOT EXISTS `commande_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `commande_id` int NOT NULL,
  `produit_id` int NOT NULL,
  `quantite` int NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `commande_id` (`commande_id`),
  KEY `produit_id` (`produit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande_details`
--

INSERT INTO `commande_details` (`id`, `commande_id`, `produit_id`, `quantite`, `prix_unitaire`) VALUES
(1, 1, 1, 1, 2500.00),
(2, 2, 2, 1, 3900.00);

-- --------------------------------------------------------

--
-- Structure de la table `medecins`
--

DROP TABLE IF EXISTS `medecins`;
CREATE TABLE IF NOT EXISTS `medecins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `actif` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `medecins`
--

INSERT INTO `medecins` (`id`, `nom`, `prenom`, `specialite`, `telephone`, `email`, `image`, `description`, `actif`, `created_at`) VALUES
(1, 'Dupont', 'Marie', 'Médecin généraliste', '0123456789', 'marie.dupont@medzone.com', NULL, 'Spécialisée en médecine générale avec 15 ans d\'expérience', 1, '2025-07-01 02:56:48'),
(2, 'Martin', 'Pierre', 'Cardiologue', '0123456790', 'pierre.martin@medzone.com', NULL, 'Expert en cardiologie et maladies cardiovasculaires', 1, '2025-07-01 02:56:48'),
(3, 'Bernard', 'Sophie', 'Dermatologue', '0123456791', 'sophie.bernard@medzone.com', NULL, 'Spécialiste en dermatologie et esthétique', 1, '2025-07-01 02:56:48'),
(4, 'Leroy', 'Jean', 'Pédiatre', '0123456792', 'jean.leroy@medzone.com', NULL, 'Médecin spécialisé dans les soins aux enfants', 1, '2025-07-01 02:56:48'),
(5, 'Dupont', 'Marie', 'Médecin généraliste', '0123456789', 'marie.dupont@medzone.com', NULL, 'Spécialisée en médecine générale avec 15 ans d\'expérience', 1, '2025-07-01 04:10:53'),
(6, 'Martin', 'Pierre', 'Cardiologue', '0123456790', 'pierre.martin@medzone.com', NULL, 'Expert en cardiologie et maladies cardiovasculaires', 1, '2025-07-01 04:10:53'),
(7, 'Bernard', 'Sophie', 'Dermatologue', '0123456791', 'sophie.bernard@medzone.com', NULL, 'Spécialiste en dermatologie et esthétique', 1, '2025-07-01 04:10:53'),
(8, 'Leroy', 'Jean', 'Pédiatre', '0123456792', 'jean.leroy@medzone.com', NULL, 'Médecin spécialisé dans les soins aux enfants', 1, '2025-07-01 04:10:53'),
(9, 'Dupont', 'Marie', 'Médecin généraliste', '0123456789', 'marie.dupont@medzone.com', NULL, 'Spécialisée en médecine générale avec 15 ans d\'expérience', 1, '2025-07-01 04:34:46'),
(10, 'Martin', 'Pierre', 'Cardiologue', '0123456790', 'pierre.martin@medzone.com', NULL, 'Expert en cardiologie et maladies cardiovasculaires', 1, '2025-07-01 04:34:46'),
(11, 'Bernard', 'Sophie', 'Dermatologue', '0123456791', 'sophie.bernard@medzone.com', NULL, 'Spécialiste en dermatologie et esthétique', 1, '2025-07-01 04:34:46'),
(12, 'Leroy', 'Jean', 'Pédiatre', '0123456792', 'jean.leroy@medzone.com', NULL, 'Médecin spécialisé dans les soins aux enfants', 1, '2025-07-01 04:34:46');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

DROP TABLE IF EXISTS `produits`;
CREATE TABLE IF NOT EXISTS `produits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `prix` decimal(10,2) NOT NULL,
  `prix_promo` decimal(10,2) DEFAULT NULL,
  `stock` int DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie_id` int DEFAULT NULL,
  `fabricant` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `prescription_requise` tinyint(1) DEFAULT '0',
  `actif` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `seuil_alerte` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `categorie_id` (`categorie_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `description`, `prix`, `prix_promo`, `stock`, `image`, `categorie_id`, `fabricant`, `date_expiration`, `prescription_requise`, `actif`, `created_at`, `updated_at`, `seuil_alerte`) VALUES
(1, 'Paracétamol 500mg', 'Antidouleur et antipyrétique', 2500.00, NULL, 99, NULL, 1, 'Laboratoire Pharma', NULL, 0, 1, '2025-07-01 02:56:48', '2025-07-02 14:00:33', 0),
(2, 'Ibuprofène 400mg', 'Anti-inflammatoire non stéroïdien', 4500.00, 3900.00, 74, NULL, 1, 'Laboratoire Pharma', NULL, 0, 1, '2025-07-01 02:56:48', '2025-07-02 15:16:45', 0),
(3, 'Vitamine C 1000mg', 'Complément vitaminique', 8500.00, NULL, 50, NULL, 3, 'Vitamines Plus', NULL, 0, 1, '2025-07-01 02:56:48', '2025-07-02 13:32:18', 0),
(4, 'Crème hydratante', 'Hydratation intensive de la peau', 5500.00, NULL, 30, NULL, 4, 'DermaCare', NULL, 1, 1, '2025-07-01 02:56:48', '2025-07-02 14:22:24', 0),
(5, 'Pansements stériles', 'Pansements adhésifs stériles', 5800.00, 4500.00, 200, NULL, 5, 'FirstAid', NULL, 0, 1, '2025-07-01 02:56:48', '2025-07-02 13:33:51', 0),
(16, 'Bome francois', 'utilisé pour soulager :le rhume et la toux, les douleurs musculaires et articulaires, les malaises nerveux ou courbatures.', 500.00, 250.00, 70, 'prod_68654082e6dda.jpg', 4, 'François Santé', NULL, 0, 1, '2025-07-02 13:52:45', '2025-07-02 14:21:54', 0);

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

DROP TABLE IF EXISTS `rendez_vous`;
CREATE TABLE IF NOT EXISTS `rendez_vous` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `medecin_id` int NOT NULL,
  `date_rdv` date NOT NULL,
  `heure_rdv` time NOT NULL,
  `motif` text COLLATE utf8mb4_unicode_ci,
  `statut` enum('en_attente','confirme','annule','termine') COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `medecin_id` (`medecin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text COLLATE utf8mb4_unicode_ci,
  `date_naissance` date DEFAULT NULL,
  `genre` enum('Homme','Femme','Autre') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('client','admin','pharmacien') COLLATE utf8mb4_unicode_ci DEFAULT 'client',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `email`, `password`, `telephone`, `adresse`, `date_naissance`, `genre`, `role`, `created_at`, `updated_at`) VALUES
(1, 'charles mbarga', 'mbargacharles53@gmail.com', '$2y$12$5Au9tu9r6zDdNbaDX6rszeXS3Ps2oT7ezs3.Lu8dgfbTpoU.kHy4y', '659417568', NULL, NULL, 'Homme', 'admin', '2025-07-01 04:14:05', '2025-07-02 14:13:24'),
(5, 'abouna loic', 'abounaloic53@gmail.com', '$2y$12$0ZtVEorv9yeumqP560OcLOAoAzSA1ujvyLh9U3GKlf8Fo6MXo83lu', '+237659417568', NULL, NULL, 'Homme', 'client', '2025-07-02 15:51:16', '2025-07-02 15:51:16');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
