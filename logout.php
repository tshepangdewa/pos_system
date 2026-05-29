<?php
require_once 'includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];

if ($role === 'cashier') {
    // Update session totals
    $session_id = $_SESSION['session_id'];
    $cashier_id = $_SESSION['user_id'];

    // Sum sales for this session
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount),0) AS total, COALESCE(SUM(
        (SELECT SUM(quantity) FROM sale_items WHERE sale_id = sales.id)
    ),0) AS items FROM sales WHERE session_id = ?");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("UPDATE sessions SET end_time = NOW(), total_amount = ?, total_items = ? WHERE id = ?");
    $stmt->bind_param("dii", $data['total'], $data['items'], $session_id);
    $stmt->execute();

    // Store summary for display
    $_SESSION['logout_summary'] = [
        'items' => $data['items'],
        'amount' => number_format($data['total'], 2, '.', '')
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - SuperMarket POS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="logout-page">
    <div class="logout-box">
        <?php if ($role === 'cashier' && isset($_SESSION['logout_summary'])): ?>
            <h2 class="heading">Shift Summary</h2>
            <p class="logout-text">Items sold: <strong><?= $_SESSION['logout_summary']['items'] ?></strong></p>
            <p class="logout-text">Total cash expected: <strong>$<?= $_SESSION['logout_summary']['amount'] ?></strong></p>
            <form method="post" action="logout.php?finalize=1">
                <button type="submit" class="btn">Close Register</button>
            </form>
        <?php else: ?>
            <p>Logging out...</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
if (isset($_GET['finalize']) || $role === 'admin') {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>