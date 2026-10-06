<?php
require_once __DIR__ . '/../config/auth.php';
requireCustomer(); $pdo = db();
$fields = $pdo->query("SELECT * FROM fields WHERE status='active' ORDER BY type,name")->fetchAll();
$cnt = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
$q = $pdo->prepare('SELECT status, COUNT(*) AS c FROM bookings WHERE customer_id = ? GROUP BY status'); $q->execute([customerId()]);
foreach ($q->fetchAll() as $r) $cnt[$r['status']] = (int)$r['c'];
$today = date('Y-m-d'); $now = date('H:i:s');
$q = $pdo->prepare("SELECT b.*, f.name AS fname, f.type FROM bookings b JOIN fields f ON f.id=b.field_id WHERE b.customer_id = ? AND b.status IN ('pending','confirmed') AND (b.booking_date > ? OR (b.booking_date = ? AND b.end_time > ?)) ORDER BY b.booking_date, b.start_time LIMIT 1");
$q->execute([customerId(), $today, $today, $now]); $up = $q->fetch();
layout_head('Dashboard');
?>
<section class="hero"><div><span class="eyebrow">ARENA BOOK / CUSTOMER</span>
<h1>Welcome back,<br><span><?= e(currentUser()['name']) ?></span></h1>
<p class="sub">Temukan lapangan, pilih waktu bermain, dan kelola reservasi kamu dari satu tempat.</p>
<div class="actions"><a class="btn" href="booking.php">BOOK A COURT &rarr;</a><a class="btn ghost" href="bookings.php">VIEW BOOKINGS</a></div></div><div class="hero-img" role="img" aria-label="Outdoor court"></div></section>
<div class="stats">
<div class="card stat"><small>Total Booking</small><b><?= array_sum($cnt) ?></b></div>
<div class="card stat"><small>Confirmed</small><b class="acc"><?= $cnt['confirmed'] ?></b></div>
<div class="card stat"><small>Pending</small><b class="amber"><?= $cnt['pending'] ?></b></div>
<div class="card stat"><small>Completed</small><b><?= $cnt['completed'] ?></b></div></div>
<?php if ($up): ?><h2 class="sec">Upcoming booking</h2>
<div class="card ticket"><div><span class="eyebrow"><?= e($up['type']) ?></span><h3><?= e($up['fname']) ?></h3><p class="muted"><?= e($up['booking_code']) ?></p></div>
<div><small>Date</small><b><?= e(formatTanggalIndonesia($up['booking_date'])) ?></b></div>
<div><small>Time</small><b><?= e(substr($up['start_time'], 0, 5) . ' - ' . substr($up['end_time'], 0, 5)) ?></b></div>
<div><span class="tag <?= e($up['status']) ?>"><?= e($up['status']) ?></span><a class="link" href="booking_detail.php?id=<?= (int)$up['id'] ?>">VIEW DETAIL &rarr;</a></div></div><?php endif; ?>
<h2 class="sec">Available courts</h2><div class="courts">
<?php foreach ($fields as $f): ?><div class="card court"><div class="court-img <?= $f['type'] === 'Futsal' ? 'futsal' : 'badminton' ?>"></div><span class="eyebrow"><?= e($f['type']) ?></span><h3><?= e($f['name']) ?></h3>
<p class="muted"><?= e($f['description']) ?></p><p class="small"><?= e($f['facilities']) ?></p>
<div class="row"><b><?= formatRupiah($f['price_per_hour']) ?> <small>/ hour</small></b><a class="link" href="booking.php?type=<?= e($f['type']) ?>">BOOK &rarr;</a></div></div><?php endforeach; ?></div>
<?php layout_foot();
