<?php
require_once __DIR__ . '/../config/auth.php';
requireCustomer();
$pdo = db(); $today = date('Y-m-d');
$date = $_GET['date'] ?? $today;
if (!validDate($date) || $date < $today) $date = $today;
$type = in_array($_GET['type'] ?? '', ['Futsal', 'Badminton'], true) ? $_GET['type'] : '';

if (isPost()) {
    verify_csrf();
    $sid = filter_var($_POST['schedule_id'] ?? '', FILTER_VALIDATE_INT);
    $notes = mb_substr(trim($_POST['notes'] ?? ''), 0, 500);
    try {
        if (!$sid) throw new RuntimeException('Pilih slot terlebih dahulu.');
        $cid = customerId(); if (!$cid) throw new RuntimeException('Akun customer tidak ditemukan.');
        $pdo->beginTransaction();
        $s = $pdo->prepare("SELECT s.*, f.price_per_hour, f.status AS fstatus FROM schedules s JOIN fields f ON f.id = s.field_id WHERE s.id = ? FOR UPDATE");
        $s->execute([$sid]); $sc = $s->fetch();
        if (!$sc || $sc['fstatus'] !== 'active') throw new RuntimeException('Slot tidak ditemukan.');
        if ($sc['status'] !== 'available') throw new RuntimeException('Slot sudah tidak tersedia.');
        if ($sc['schedule_date'] < $today || ($sc['schedule_date'] === $today && $sc['start_time'] <= date('H:i:s')))
            throw new RuntimeException('Slot sudah lewat.');
        $code = null;
        for ($i = 0; $i < 5 && !$code; $i++) {
            $c = generateBookingCode($sc['schedule_date']);
            $q = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE booking_code = ?'); $q->execute([$c]);
            if (!$q->fetchColumn()) $code = $c;
        }
        if (!$code) throw new RuntimeException('Gagal membuat kode booking.');
        $pdo->prepare('INSERT INTO bookings(booking_code,customer_id,field_id,schedule_id,booking_date,start_time,end_time,total_price,status,notes) VALUES(?,?,?,?,?,?,?,?,"pending",?)')
            ->execute([$code, $cid, $sc['field_id'], $sc['id'], $sc['schedule_date'], $sc['start_time'], $sc['end_time'], $sc['price_per_hour'], $notes]);
        $id = $pdo->lastInsertId();
        $pdo->prepare("UPDATE schedules SET status='booked' WHERE id = ?")->execute([$sc['id']]);
        $pdo->commit();
        flash('Booking berhasil: ' . $code); redirect('customer/booking_detail.php?id=' . $id);
    } catch (RuntimeException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); flash($x->getMessage(), 'err'); redirect('customer/booking.php?date=' . $date); }
    catch (PDOException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($x->getMessage()); flash('Terjadi kesalahan. Silakan coba lagi.', 'err'); redirect('customer/booking.php?date=' . $date); }
}

// Generate 15 slot per lapangan aktif (tanpa duplikat berkat UNIQUE + INSERT IGNORE)
try {
    $ins = $pdo->prepare('INSERT IGNORE INTO schedules(field_id,schedule_date,start_time,end_time,status) VALUES(?,?,?,?,"available")');
    $ids = $pdo->query("SELECT id FROM fields WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $fid) for ($h = 7; $h < 22; $h++)
        $ins->execute([$fid, $date, sprintf('%02d:00:00', $h), sprintf('%02d:00:00', $h + 1)]);
    $fq = $pdo->prepare("SELECT * FROM fields WHERE status='active'" . ($type ? ' AND type = ?' : '') . ' ORDER BY type,name');
    $fq->execute($type ? [$type] : []); $fields = $fq->fetchAll();
    $q = $pdo->prepare('SELECT * FROM schedules WHERE field_id = ? AND schedule_date = ? ORDER BY start_time');
} catch (PDOException $x) { error_log($x->getMessage()); exit('Terjadi kesalahan. Silakan coba lagi.'); }
$now = date('H:i:s');
layout_head('Booking', customerNav());
?>
<h1>Book your court</h1><p class="sub">Choose a date and select an available time slot.</p>
<form method="get" class="inline"><input type="hidden" name="type" value="<?= e($type) ?>"><input type="date" name="date" min="<?= e($today) ?>" value="<?= e($date) ?>" onchange="this.form.submit()"></form>
<p class="legend"><i class="dot ok"></i> Available <i class="dot no"></i> Tidak tersedia</p>
<form method="post" id="bookForm"><?= csrf_field() ?><div class="book-grid"><div>
<?php foreach ($fields as $f): $q->execute([$f['id'], $date]); ?>
<section class="card"><h3><?= e($f['name']) ?></h3><p class="muted"><?= formatRupiah($f['price_per_hour']) ?> / hour</p>
<div class="slots">
<?php foreach ($q->fetchAll() as $s):
    $past = $date === $today && $s['start_time'] <= $now;
    $can = $s['status'] === 'available' && !$past; ?>
<label class="slot <?= $can ? '' : 'off' ?>"><input type="radio" name="schedule_id" value="<?= (int)$s['id'] ?>" <?= $can ? '' : 'disabled' ?>
 data-field="<?= e($f['name']) ?>" data-time="<?= e(substr($s['start_time'], 0, 5) . ' - ' . substr($s['end_time'], 0, 5)) ?>" data-price="<?= e(formatRupiah($f['price_per_hour'])) ?>">
<span><?= e(substr($s['start_time'], 0, 5)) ?></span></label>
<?php endforeach; ?></div></section>
<?php endforeach; ?>
</div><aside class="card summary"><span class="eyebrow">Booking summary</span>
<dl class="sum"><dt>Court</dt><dd id="sField">-</dd><dt>Date</dt><dd><?= e(formatTanggalIndonesia($date)) ?></dd><dt>Time</dt><dd id="sTime">-</dd><dt>Price</dt><dd id="sPrice" class="acc">-</dd></dl>
<label>Notes<textarea name="notes" maxlength="500" placeholder="Catatan (opsional)"></textarea></label>
<button class="btn" id="bookBtn" disabled>CONFIRM BOOKING</button></aside></div></form>
<?php layout_foot();
