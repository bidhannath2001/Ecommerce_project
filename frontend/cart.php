<?php
// Start output buffering and session
ob_start();
session_start();

// Include header and database configuration
include 'header.php';
include 'config.php';

// Redirect to login if user not logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$userEmail = $_SESSION['email'];

// Handle product removal from cart
if (isset($_GET['remove'])) {
    $removeProductID = intval($_GET['remove']);
    unset($_SESSION['cart'][$removeProductID]);
    header('Location: cart.php');
    exit();
}

// Handle quantity update
if (isset($_POST['update_quantity']) && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $productID => $quantity) {
        $quantity = intval($quantity);
        if ($quantity > 0) {
            $_SESSION['cart'][$productID]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$productID]);
        }
    }
    header('Location: cart.php');
    exit();
}

// Handle order placement
if (isset($_POST['place_order'])) {
    $orderDate = date('Y-m-d H:i:s');

    foreach ($_SESSION['cart'] as $productID => $cartItem) {
        // Sanitize product ID and fetch product from DB
        $productID = intval($productID);
        $result = $conn->query("SELECT Image FROM products WHERE ProductID = $productID");
        if ($result && $product = $result->fetch_assoc()) {
            // Insert order into DB
            $sql = "INSERT INTO orders (product_id, image, quantity, user_email, order_date) 
                    VALUES ('$productID', '" . mysqli_real_escape_string($conn, $product['Image']) . "', 
                    '{$cartItem['quantity']}', '" . mysqli_real_escape_string($conn, $userEmail) . "', '$orderDate')";
            $conn->query($sql) or die('Error inserting order: ' . htmlspecialchars($conn->error));
        }
    }

    // Clear cart and show order success message
    unset($_SESSION['cart']);
    $orderPlaced = true;
}

ob_end_flush();
?>

<div class="container mt-4">
    <h2>Shopping Cart</h2>
    <?php if (isset($orderPlaced) && $orderPlaced): ?>
        <p class="alert alert-success">Your order has been placed successfully!</p>
    <?php endif; ?>

    <?php if (!empty($_SESSION['cart'])): ?>
        <form method="post" action="cart.php">
            <table class="table text-center">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $productID => $cartItem) {
                        // Fetch product details from the database
                        $result = $conn->query("SELECT * FROM products WHERE ProductID = " . intval($productID));
                        if ($result && $product = $result->fetch_assoc()) {
                            $quantity = $cartItem['quantity'];
                            $subtotal = $product['Price'] * $quantity;
                            $total += $subtotal;
                            ?>
                            <tr>
                                <td><img src="images/<?php echo htmlspecialchars($product['Image']); ?>" class="img-fluid" style="width: 60px;"></td>
                                <td><?php echo htmlspecialchars($product['Name']); ?></td>
                                <td>$<?php echo htmlspecialchars($product['Price']); ?></td>
                                <td>
                                    <input type="number" name="quantities[<?php echo $productID; ?>]" value="<?php echo $quantity; ?>" min="1" class="form-control" style="width: 60px;">
                                </td>
                                <td>$<?php echo htmlspecialchars($subtotal); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $productID; ?>" class="btn btn-danger btn-sm">Remove</a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Total</strong></td>
                        <td><strong>$<?php echo $total; ?></strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" name="update_quantity" class="btn btn-primary">Update Quantities</button>
            <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
        </form>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
