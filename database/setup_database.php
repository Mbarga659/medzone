<?php
/**
 * Script de configuration automatique de la base de données MedZone
 * À exécuter une seule fois pour initialiser la base de données
 */

// Configuration de la base de données
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'medzone';

try {
    // Connexion à MySQL sans sélectionner de base de données
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à MySQL réussie\n";
    
    // Créer la base de données si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Base de données '$dbname' créée ou déjà existante\n";
    
    // Sélectionner la base de données
    $pdo->exec("USE `$dbname`");
    
    // Lire et exécuter le fichier SQL
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // Supprimer les lignes CREATE DATABASE et USE car on les a déjà fait
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE.*?;/i', '', $sql);
    
    // Exécuter les requêtes SQL
    $pdo->exec($sql);
    
    echo "✅ Tables créées avec succès\n";
    echo "✅ Données de base insérées\n";
    echo "\n🎉 Configuration de la base de données terminée !\n";
    echo "Tu peux maintenant utiliser MedZone avec toutes ses fonctionnalités.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Vérifie que WAMP est démarré et que MySQL fonctionne.\n";
}
?> 