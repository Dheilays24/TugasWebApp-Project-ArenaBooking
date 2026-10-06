<?php
require_once __DIR__ . '/../config/auth.php';
requireCustomer();
$s = db()->prepare("SELECT b.*, f.name AS field_name FROM bookings b JOIN fields f ON f.id=b.field_id WHERE b.customer_id = ? ORDER BY b.created_at DESC, b.id DESC");
$s->execute([customerId()]);
layout_head('Booking Saya', customerNav());
?>
<h1>BOOKING <span>SAYA</span></h1><div class="table-wrap"><table>
<tr><th>Kode</th><th>Lapangan</th><th>Tanggal</th><th>Jam</th><th>Total</th><th>Status</th><th></th></tr>
<?php foreach ($s->fetchAll() as $b): ?>
<tr><td><?= e($b['booking_code']) ?></td><td><?= e($b['field_name']) ?></td><td><?= e(formatTanggalIndonesia($b['booking_date'])) ?></td>
<td><?= e(substr($b['start_time'], 0, 5) . '-' . substr($b['end_time'], 0, 5)) ?></td><td><?= formatRupiah($b['total_price']) ?></td>
<td><span class="tag <?= e($b['status']) ?>"><?= e($b['status']) ?></span></td><td><a href="booking_detail.php?id=<?= (int)$b['id'] ?>">Detail</a></td></tr>
<?php endforeach; ?></table></div>
<?php layout_foot();
