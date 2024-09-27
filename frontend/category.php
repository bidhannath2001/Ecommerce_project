<?php
// Include header and database configuration
include 'header.php';
include 'config.php';

// Get and validate CategoryID from the URL
$categoryID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initialize variables
$categoryName = '';
$products = [];

if ($categoryID > 0) {
    // Fetch the CategoryName
    $sql = "SELECT CategoryName FROM categories WHERE CategoryID = $categoryID";
    $categoryResult = $conn->query($sql);

    if ($categoryResult->num_rows > 0) {
        $categoryName = $categoryResult->fetch_assoc()['CategoryName'];

        // Fetch products for this CategoryID
        $sql = "SELECT * FROM products WHERE CategoryID = $categoryID";
        $productResult = $conn->query($sql);
        $products = $productResult->fetch_all(MYSQLI_ASSOC);
    } else {
        $categoryName = 'Category not found';
    }
} else {
    $categoryName = 'Invalid Category ID';
}
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($categoryName); ?></h2>
    <div class="row">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-sm-6 col-md-3 mb-4">
                    <div class="card">
                        <img src="images/<?php echo htmlspecialchars($product['Image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['Name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['Name']); ?></h5>
                            <p class="card-text">$<?php echo htmlspecialchars($product['Price']); ?></p>
                            <a href="product.php?id=<?php echo intval($product['ProductID']); ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products found in this category.</p>
        <?php endif; ?>
    </div>
</div>

<style>
    .card {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
    }
    .card:hover {
        transform: scale(1.05);
    }
    .card-img-top {
        width: 100%;
        height: 220px;
    }
</style>

<?php
// Include footer
include 'footer.php';
?>
