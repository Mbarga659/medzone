<?php
// Script de nettoyage des doublons dans les tables produits et categories
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

function cleanTable($pdo, $table) {
    // Trouver les doublons
    $sql = "SELECT nom, MIN(id) as min_id FROM $table GROUP BY nom HAVING COUNT(*) > 1";
    $stmt = $pdo->query($sql);
    $to_keep = [];
    while ($row = $stmt->fetch()) {
        $to_keep[] = $row['min_id'];
    }
    if (count($to_keep) > 0) {
        // Supprimer les doublons (garder la première occurrence)
        $ids = implode(',', $to_keep);
        $sql_del = "DELETE FROM $table WHERE nom IN (SELECT nom FROM (SELECT nom FROM $table GROUP BY nom HAVING COUNT(*) > 1) as t) AND id NOT IN ($ids)";
        $pdo->exec($sql_del);
        echo "Doublons supprimés dans $table.\n";
    } else {
        echo "Aucun doublon trouvé dans $table.\n";
    }
}

cleanTable($pdo, 'produits');
cleanTable($pdo, 'categories');

echo "Nettoyage terminé !\n"; 