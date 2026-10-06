<?php
require_once __DIR__ . '/../config/auth.php';
requireAdmin(); $pdo = db();
$allowed = ['pending' => ['confirmed', 'cancelled'], 'confirmed' => ['completed', 'cancelled']];
if (isPost()) {
    verify_csrf();
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT); $new = $_POST['status'] ?? '';
    try {
        $pdo->beginTransaction();
        $s = $pdo->prepare('SELECT * FROM bookings WHERE id = ? FOR UPDATE'); $s->execute([$id ?: 0]); $b = $s->fetch();
        if (!$b || !in_array($new, $allowed[$b['status']] ?? [], true)) throw new RuntimeException('Perubahan status tidak diizinkan.');
        $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?')->execute([$new, $b['id']]);
        if ($new === 'cancelled') $pdo->prepare("UPDATE schedules SET status='available' WHERE id = ?")->execute([$b['schedule_id']]);
        $pdo->commit(); flash('Status diperbarui.');
    } catch (RuntimeException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); flash($x->getMessage(), 'err'); }
    catch (PDOException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($x->getMessage()); flash('Terjadi kesalahan. Silakan coba lagi.', 'err'); }
    redirect('admin/bookings.php');
}
$where = []; $p = [];
$fs = $_GET['status'] ?? ''; if (in_array($fs, ['pending', 'confirmed', 'cancelled', 'completed'], true)) { $where[] = 'b.status = ?'; $p[] = $fs; }
$fd = $_GET['date'] ?? ''; if ($fd !== '' && validDate($fd)) { $where[] = 'b.booking_date = ?'; $p[] = $fd; }
$ff = filter_var($_GET['field'] ?? '', FILTER_VALIDATE_INT); if ($ff) { $where[] = 'b.field_id = ?'; $p[] = $ff; }
$fc = trim($_GET['customer'] ?? ''); if ($fc !== '') { $where[] = 'u.name LIKE ?'; $p[] = '%' . $fc . '%'; }
$sql = "SELECT b.*, f.name AS fname, u.name AS cname FROM bookings b JOIN fields f ON f.id=b.field_id JOIN customers c ON c.id=b.customer_id JOIN users u ON u.id=c.user_id" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY b.id DESC';
$q = $pdo->prepare($sql); $q->execute($p); $rows = $q->fetchAll();
$fields = $pdo->query('SELECT id,name FROM fields ORDER BY name')->fetchAll();
layout_head('Booking', adminNav());
?>
<h1>SEMUA <span>BOOKING</span></h1>
<form method="get" class="inline filters"><input type="date" name="date" value="<?= e($fd) ?>">
<select name="field"><option value="">Semua lapangan</option><?php foreach ($fields as $f): ?><option value="<?= (int)$f['id'] ?>" <?= $ff == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option><?php endforeach; ?></select>
<select name="status"><option value="">Semua status</option><?php foreach (['pending', 'confirmed', 'cancelled', 'completed'] as $s): ?><option <?= $fs === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
<input name="customer" placeholder="Nama customer" value="<?= e($fc) ?>"><button class="btn">FILTER</button></form>
<div class="table-wrap"><table><tr><th>Kode</th><th>Customer</th><th>Lapangan</th><th>Tanggal</th><th>Jam</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
<?php foreach ($rows as $b): ?><tr><td><a href="booking_detail.php?id=<?= (int)$b['id'] ?>"><?= e($b['booking_code']) ?></a></td><td><?= e($b['cname']) ?></td><td><?= e($b['fname']) ?></td><td><?= e($b['booking_date']) ?></td>
<td><?= e(substr($b['start_time'], 0, 5) . '-' . substr($b['end_time'], 0, 5)) ?></td><td><?= formatRupiah($b['total_price']) ?></td><td><span class="tag <?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
<td><?php foreach ($allowed[$b['status']] ?? [] as $n): ?>
<form method="post" class="inline" data-confirm="Ubah status ke <?= e($n) ?>?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><input type="hidden" name="status" value="<?= e($n) ?>"><button class="btn sm"><?= e($n) ?></button></form>
<?php endforeach; ?></td></tr><?php endforeach; ?></table></div>
<?php layout_foot();
