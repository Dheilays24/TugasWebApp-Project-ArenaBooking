<?php
require_once __DIR__ . '/../config/auth.php';
requireCustomer();
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$s = db()->prepare("SELECT b.*, f.name AS field_name, f.type, u.name AS cname FROM bookings b JOIN fields f ON f.id=b.field_id JOIN customers c ON c.id=b.customer_id JOIN users u ON u.id=c.user_id WHERE b.id = ? AND b.customer_id = ?");
$s->execute([$id ?: 0, customerId()]); $b = $s->fetch();
if (!$b) { flash('Booking tidak ditemukan.', 'err'); redirect('customer/bookings.php'); }
$canCancel = in_array($b['status'], ['pending', 'confirmed'], true) && $b['booking_date'] >= date('Y-m-d');
layout_head('Detail Booking', customerNav());
?>
<div class="card narrow invoice"><span class="eyebrow">Booking confirmation</span><h1 class="code"><?= e($b['booking_code']) ?></h1>
<dl><dt>Customer</dt><dd><?= e($b['cname']) ?></dd><dt>Lapangan</dt><dd><?= e($b['field_name']) ?> (<?= e($b['type']) ?>)</dd>
<dt>Tanggal</dt><dd><?= e(formatTanggalIndonesia($b['booking_date'])) ?></dd><dt>Jam</dt><dd><?= e(substr($b['start_time'], 0, 5) . ' - ' . substr($b['end_time'], 0, 5)) ?></dd>
<dt>Harga</dt><dd><?= formatRupiah($b['total_price']) ?></dd><dt>Status</dt><dd><span class="tag <?= e($b['status']) ?>"><?= e($b['status']) ?></span></dd>
<dt>Catatan</dt><dd><?= e($b['notes'] ?: '-') ?></dd><dt>Dibuat</dt><dd><?= e($b['created_at']) ?></dd></dl>
<?php if ($canCancel): ?><form method="post" action="cancel_booking.php" data-confirm="Batalkan booking ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><button class="btn danger">BATALKAN</button></form><?php endif; ?>
</div><?php layout_foot();
