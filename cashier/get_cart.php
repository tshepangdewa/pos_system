<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

$cart = $_SESSION['cart'] ?? [];
$result = [];

if (!empty($cart)) {
    foreach ($cart as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT name, price, quantity FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if ($product) {
            $result[$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'qty' => $quantity,
                'stock' => $product['quantity']
            ];
        }
    }
}

echo json_encode($result);