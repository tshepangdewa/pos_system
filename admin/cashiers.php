<?php
require_once '../includes/auth.php';
require_role('admin');
include '../includes/header.php';

// Add cashier
if (isset($_POST['add_cashier'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'cashier')");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    header("Location: cashiers.php");
    exit;
}

// Delete cashier (prevent deleting admin, clean up related data)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Don't allow deleting yourself
    if ($id == $_SESSION['user_id']) {
        echo "<p class='error'>You cannot delete your own account.</p>";
    } else {
        // Delete dependent records first
        $conn->query("DELETE FROM sale_items WHERE sale_id IN (SELECT id FROM sales WHERE cashier_id = $id)");
        $conn->query("DELETE FROM sales WHERE cashier_id = $id");
        $conn->query("DELETE FROM sessions WHERE cashier_id = $id");
        $conn->query("DELETE FROM users WHERE id = $id AND role = 'cashier'");
        
        header("Location: cashiers.php");
        exit;
    }
}
$cashiers = $conn->query("SELECT id, username FROM users WHERE role = 'cashier'");
?>
<div class="cashiers-div">
<h2 class="heading">Add New Cashier</h2>

<form method="post" class="inline-form">
    
    <input class="input" type="text" name="username" placeholder="Username" required>
    <input class="input" type="password" name="password" placeholder="Password" required>
    <button type="submit" name="add_cashier" class="btn">Add</button>
</form>
<h2 class="heading">Manage Cashiers</h2>
<table class="table" border="1">
    <tr><th>ID</th><th>Username</th><th>Action</th></tr>
    <?php while ($c = $cashiers->fetch_assoc()): ?>
    <tr>
        <td><?= $c['id'] ?></td>
        <td><?= htmlspecialchars($c['username']) ?></td>
        <td><a href="?delete=<?= $c['id'] ?>" class="btn-cashier-delete" onclick="return confirm('Delete?')">Delete</a></td>
    </tr>
    <?php endwhile; ?>
</table>
    </div>
<?php include '../includes/footer.php'; ?>