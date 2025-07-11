<?php
/**
 * Script de réinitialisation complète de la base de données MedZone
 * ATTENTION : Ce script supprime toutes les données existantes !
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
    
    // Supprimer la base de données si elle existe
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname`");
    echo "✅ Ancienne base de données supprimée\n";
    
    // Créer la nouvelle base de données
    $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Nouvelle base de données '$dbname' créée\n";
    
    // Sélectionner la base de données
    $pdo->exec("USE `$dbname`");
    
    // Lire et exécuter le fichier SQL
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // Supprimer les lignes CREATE DATABASE et USE car on les a déjà fait
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE.*?;/i', '', $sql);
    
    // Diviser le SQL en requêtes individuelles
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    
    echo "✅ Tables créées avec succès\n";
    echo "✅ Données de base insérées\n";
    echo "\n🎉 Base de données réinitialisée avec succès !\n";
    echo "Tu peux maintenant utiliser MedZone avec toutes ses fonctionnalités.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Vérifie que WAMP est démarré et que MySQL fonctionne.\n";
}
?> 