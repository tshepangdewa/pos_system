<?php
require_once '../includes/auth.php';
require_role('admin');
include '../includes/header.php';

$sessions = $conn->query("
    SELECT s.id, u.username, s.start_time, s.end_time, s.total_items, s.total_amount
    FROM sessions s
    JOIN users u ON s.cashier_id = u.id
    ORDER BY s.start_time DESC
");
?>
<div class="sessions-div">
<div>
<h2 class="heading">All Cashier Sessions</h2>
</div>
<div>
  <table class="table" border="1">
    <tr>
        <th>Cashier</th><th>Start</th><th>End</th><th>Items Sold</th><th>Total ($)</th>
    </tr>
    <?php while ($row = $sessions->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= $row['start_time'] ?></td>
        <td><?= $row['end_time'] ?? 'Active' ?></td>
        <td><?= $row['total_items'] ?></td>
        <td>$<?= number_format($row['total_amount'], 2, '.', '') ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
  </div>
<?php include '../includes/footer.php'; ?>