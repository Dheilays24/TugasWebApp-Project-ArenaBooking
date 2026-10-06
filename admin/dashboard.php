<?php
require_once __DIR__ . '/../config/auth.php';
requireAdmin(); $pdo = db();
$n = fn($sql) => (int)$pdo->query($sql)->fetchColumn();
$st = [
 'Total Customer' => $n('SELECT COUNT(*) FROM customers'), 'Total Lapangan' => $n('SELECT COUNT(*) FROM fields'),
 'Total Booking' => $n('SELECT COUNT(*) FROM bookings'), 'Pending' => $n("SELECT COUNT(*) FROM bookings WHERE status='pending'"),
 'Confirmed' => $n("SELECT COUNT(*) FROM bookings WHERE status='confirmed'"), 'Cancelled' => $n("SELECT COUNT(*) FROM bookings WHERE status='cancelled'"),
 'Completed' => $n("SELECT COUNT(*) FROM bookings WHERE status='completed'")];
$recent = $pdo->query("SELECT b.*, f.name AS fname, u.name AS cname FROM bookings b JOIN fields f ON f.id=b.field_id JOIN customers c ON c.id=b.customer_id JOIN users u ON u.id=c.user_id ORDER BY b.id DESC LIMIT 5")->fetchAll();
layout_head('Dashboard', adminNav());
?>
<h1>DASHBOARD</h1><div class="grid stats"><?php foreach ($st as $k => $v): ?><div class="card"><p class="muted"><?= e($k) ?></p><h2><?= $v ?></h2></div><?php endforeach; ?></div>
<h2>Recent Bookings</h2><div class="table-wrap"><table><tr><th>Kode</th><th>Customer</th><th>Lapangan</th><th>Tanggal</th><th>Status</th></tr>
<?php foreach ($recent as $b): ?><tr><td><?= e($b['booking_code']) ?></td><td><?= e($b['cname']) ?></td><td><?= e($b['fname']) ?></td><td><?= e($b['booking_date']) ?></td><td><span class="tag <?= e($b['status']) ?>"><?= e($b['status']) ?></span></td></tr><?php endforeach; ?></table></div>
<?php layout_foot();
