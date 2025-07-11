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

if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$product_id = (int)$input['product_id'];
$quantity = (int)$input['quantity'];

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantité invalide']);
    exit;
}

try {
    require_once '../config/database.php';
    $pdo = getDB();
    
    // Vérifier que le produit existe et est actif
    $stmt = $pdo->prepare('SELECT id, nom, prix, prix_promo, stock FROM produits WHERE id = ? AND actif = 1');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
        exit;
    }
    
    // Vérifier le stock
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
        exit;
    }
    
    // Initialiser le panier si nécessaire
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Vérifier si le produit est déjà dans le panier
    $product_in_cart = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product_id) {
            $item['quantity'] += $quantity;
            $product_in_cart = true;
            break;
        }
    }
    
    // Ajouter le produit s'il n'est pas déjà dans le panier
    if (!$product_in_cart) {
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'name' => $product['nom'],
            'price' => $product['prix_promo'] ?: $product['prix'],
            'quantity' => $quantity
        ];
    }
    
    // Calculer le nombre total d'articles dans le panier
    $cart_count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Produit ajouté au panier',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
} 