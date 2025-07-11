<?php
session_start();
require_once '../config/database.php';

// Sécurité admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin/backup.php');
    exit;
}
$pdo = getDB();
$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Infos connexion
$db = $pdo->query('select database()')->fetchColumn();
$host = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);

// Récupérer les infos de connexion depuis database.php
require_once '../config/database.php';
$dsn = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);

// Utiliser mysqldump si disponible
$filename = 'backup_medzone_' . date('Ymd_His') . '.sql';
$cmd = "mysqldump --user={$_ENV['DB_USER']} --password={$_ENV['DB_PASS']} --host={$_ENV['DB_HOST']} {$db} 2>&1";

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename=' . $filename);

passthru($cmd);
exit; 