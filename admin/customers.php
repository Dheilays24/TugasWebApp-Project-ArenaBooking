<?php
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
$rows = db()->query('SELECT u.name,u.email,u.created_at,c.phone,c.address,(SELECT COUNT(*) FROM bookings b WHERE b.customer_id=c.id) AS total FROM customers c JOIN users u ON u.id=c.user_id ORDER BY u.created_at DESC')->fetchAll();
layout_head('Customer', adminNav());
?>
<h1>DATA <span>CUSTOMER</span></h1><div class="table-wrap"><table><tr><th>Nama</th><th>Email</th><th>HP</th><th>Alamat</th><th>Booking</th><th>Daftar</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['address']) ?></td><td><?= (int)$r['total'] ?></td><td><?= e($r['created_at']) ?></td></tr><?php endforeach; ?></table></div>
<?php layout_foot();
