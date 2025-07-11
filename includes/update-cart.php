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

if (!$input || !isset($input['index']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$index = (int)$input['index'];
$quantity = (int)$input['quantity'];

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantité invalide']);
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || !isset($_SESSION['cart'][$index])) {
    echo json_encode(['success' => false, 'message' => 'Article non trouvé dans le panier']);
    exit;
}

try {
    require_once '../config/database.php';
    $pdo = getDB();
    
    // Vérifier le stock disponible
    $product_id = $_SESSION['cart'][$index]['product_id'];
    $stmt = $pdo->prepare('SELECT stock FROM produits WHERE id = ? AND actif = 1');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
        exit;
    }
    
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
        exit;
    }
    
    // Mettre à jour la quantité
    $_SESSION['cart'][$index]['quantity'] = $quantity;
    
    echo json_encode(['success' => true, 'message' => 'Quantité mise à jour']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
} 