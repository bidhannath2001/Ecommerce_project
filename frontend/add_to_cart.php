<?php
session_start();
include 'config.php';

// Get and sanitize the product ID
$productID = intval($_POST['product_id'] ?? 0);

// Fetch product details from the database
$sql = "SELECT Name, Price FROM products WHERE ProductID = $productID";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();

    // Initialize cart if not already set
    $_SESSION['cart'] ??= [];

    // Add product to cart or update quantity
    $_SESSION['cart'][$productID] = $_SESSION['cart'][$productID] ?? [
        'name' => $product['Name'],
        'price' => $product['Price'],
        'quantity' => 0
    ];
    $_SESSION['cart'][$productID]['quantity'] += 1;

    header('Location: cart.php');
    exit();
} else {
    echo "Product not found.";
}
