<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid product']);
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($quantity <= 0) {
    // Remove item from cart
    unset($_SESSION['cart'][$product_id]);
} else {
    // Check stock
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
    
    if ($quantity > $product['quantity']) {
        echo json_encode(['success' => false, 'error' => 'Not enough stock']);
        exit;
    }
    
    $_SESSION['cart'][$product_id] = $quantity;
}

echo json_encode(['success' => true]);