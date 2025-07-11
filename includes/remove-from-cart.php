<?php
session_start();
header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['index'])) {
    echo json_encode(['success' => false, 'message' => 'Index manquant']);
    exit;
}

$index = (int)$input['index'];

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || !isset($_SESSION['cart'][$index])) {
    echo json_encode(['success' => false, 'message' => 'Article non trouvé dans le panier']);
    exit;
}

// Supprimer l'article du panier
unset($_SESSION['cart'][$index]);

// Réindexer le tableau
$_SESSION['cart'] = array_values($_SESSION['cart']);

echo json_encode(['success' => true, 'message' => 'Article supprimé du panier']); 