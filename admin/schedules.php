<?php
require_once __DIR__ . '/../config/auth.php';
requireAdmin(); $pdo = db();
$date = $_GET['date'] ?? date('Y-m-d'); if (!validDate($date)) $date = date('Y-m-d');
$ff = filter_var($_GET['field'] ?? '', FILTER_VALIDATE_INT) ?: 0;
if (isPost()) {
    verify_csrf();
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
    try {
        $pdo->beginTransaction();
        $s = $pdo->prepare('SELECT * FROM schedules WHERE id = ? FOR UPDATE'); $s->execute([$id ?: 0]); $sc = $s->fetch();
        if (!$sc) throw new RuntimeException('Jadwal tidak ditemukan.');
        if ($sc['status'] === 'booked') throw new RuntimeException('Slot sudah dibooking, ubah lewat menu Booking.');
        $new = $sc['status'] === 'available' ? 'blocked' : 'available';
        $pdo->prepare('UPDATE schedules SET status = ? WHERE id = ?')->execute([$new, $sc['id']]);
        $pdo->commit(); flash('Status slot menjadi ' . $new . '.');
    } catch (RuntimeException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); flash($x->getMessage(), 'err'); }
    catch (PDOException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($x->getMessage()); flash('Terjadi kesalahan. Silakan coba lagi.', 'err'); }
    redirect('admin/schedules.php?date=' . $date . '&field=' . $ff);
}
try {
    ensureSchedules($date);
    $sql = 'SELECT s.*, f.name FROM schedules s JOIN fields f ON f.id=s.field_id WHERE s.schedule_date = ?' . ($ff ? ' AND s.field_id = ?' : '') . ' ORDER BY f.name, s.start_time';
    $q = $pdo->prepare($sql); $q->execute($ff ? [$date, $ff] : [$date]); $rows = $q->fetchAll();
    $fields = $pdo->query('SELECT id,name FROM fields ORDER BY name')->fetchAll();
} catch (PDOException $x) { error_log($x->getMessage()); exit('Terjadi kesalahan. Silakan coba lagi.'); }
layout_head('Jadwal', adminNav());
?>
<h1>KELOLA <span>JADWAL</span></h1>
<form method="get" class="inline filters"><input type="date" name="date" value="<?= e($date) ?>">
<select name="field"><option value="">Semua lapangan</option><?php foreach ($fields as $f): ?><option value="<?= (int)$f['id'] ?>" <?= $ff === (int)$f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option><?php endforeach; ?></select><button class="btn">TAMPILKAN</button></form>
<div class="table-wrap"><table><tr><th>Tanggal</th><th>Lapangan</th><th>Jam</th><th>Status</th><th>Aksi</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['schedule_date']) ?></td><td><?= e($r['name']) ?></td><td><?= e(substr($r['start_time'], 0, 5) . '-' . substr($r['end_time'], 0, 5)) ?></td><td><span class="tag <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
<td><?php if ($r['status'] !== 'booked'): ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn sm"><?= $r['status'] === 'available' ? 'Block' : 'Unblock' ?></button></form><?php else: ?>-<?php endif; ?></td></tr><?php endforeach; ?></table></div>
<?php layout_foot();
