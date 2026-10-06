<?php
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$s = db()->prepare('SELECT b.*, f.name AS fname, f.type, u.name AS cname, u.email, c.phone FROM bookings b JOIN fields f ON f.id=b.field_id JOIN customers c ON c.id=b.customer_id JOIN users u ON u.id=c.user_id WHERE b.id = ?');
$s->execute([$id ?: 0]); $b = $s->fetch();
if (!$b) { flash('Booking tidak ditemukan.', 'err'); redirect('admin/bookings.php'); }
layout_head('Detail Booking', adminNav());
?>
<div class="card narrow invoice"><span class="eyebrow">Booking confirmation</span><h1 class="code"><?= e($b['booking_code']) ?></h1>
<dl><dt>Customer</dt><dd><?= e($b['cname']) ?> (<?= e($b['email']) ?>, <?= e($b['phone']) ?>)</dd><dt>Lapangan</dt><dd><?= e($b['fname']) ?> (<?= e($b['type']) ?>)</dd>
<dt>Tanggal</dt><dd><?= e(formatTanggalIndonesia($b['booking_date'])) ?></dd><dt>Jam</dt><dd><?= e(substr($b['start_time'], 0, 5) . ' - ' . substr($b['end_time'], 0, 5)) ?></dd>
<dt>Harga</dt><dd><?= formatRupiah($b['total_price']) ?></dd><dt>Status</dt><dd><?= e($b['status']) ?></dd><dt>Catatan</dt><dd><?= e($b['notes'] ?: '-') ?></dd><dt>Dibuat</dt><dd><?= e($b['created_at']) ?></dd></dl>
<a href="bookings.php">&larr; Kembali</a></div><?php layout_foot();
