<?php
require_once '../includes/auth.php';
require_role('admin');
include '../includes/header.php';

// Handle restock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock_id'])) {
    $id = (int)$_POST['restock_id'];
    $change = (int)$_POST['change'];
    $conn->query("UPDATE products SET quantity = quantity + $change WHERE id = $id");
    header("Location: inventory.php");
    exit;
}

// Add product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $qty = $_POST['quantity'];
    $stmt = $conn->prepare("INSERT INTO products (name, price, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $name, $price, $qty);
    $stmt->execute();
    header("Location: inventory.php");
    exit;
}

// Delete product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: inventory.php");
    exit;
}

$products = $conn->query("SELECT * FROM products ORDER BY name");
?>
<div>
<h2 class="heading">Inventory Management</h2>
</div>
<!-- Add Product Form -->
<div><form method="post" class="inline-form">
    <h3 class="heading">Add New Product</h3>
    <input class="input" type="text" name="name" placeholder="Product name" required>
    <input class="input" type="number" step="0.01" name="price" placeholder="Price" required>
    <input class="input" type="number" name="quantity" placeholder="Quantity" required>
    <button type="submit" name="add_product" class="add-btn">Add</button>
</form>
</div>
<!-- Product List -->
<div>
<table class="table" border="1">
    <tr>
        <th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Restock</th><th>Action</th>
    </tr>
    <?php while ($p = $products->fetch_assoc()): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td>$<?= number_format($p['price'], 2, '.', '') ?></td>
        <td><?= $p['quantity'] ?></td>
        <td>
            <form method="post" style="display:flex; gap:4px;">
                <input type="hidden" name="restock_id" value="<?= $p['id'] ?>">
                <input type="number" name="change" placeholder="+/-" required style="width:60px;">
                <button type="submit" class="btn">Update</button>
            </form>
        </td>
        <td><a href="?delete=<?= $p['id'] ?>" class="btn-danger" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
    <?php endwhile; ?>
</table>
    </div>
<?php include '../includes/footer.php'; ?>