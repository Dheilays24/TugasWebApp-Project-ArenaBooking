<?php
require_once __DIR__ . '/../config/auth.php';
requireAdmin(); $pdo = db();
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT) ?: 0;
$f = ['name' => '', 'type' => 'Futsal', 'description' => '', 'price_per_hour' => '', 'facilities' => '', 'status' => 'active'];
if ($id) {
    $s = $pdo->prepare('SELECT * FROM fields WHERE id = ?'); $s->execute([$id]); $r = $s->fetch();
    if (!$r) { flash('Lapangan tidak ditemukan.', 'err'); redirect('admin/fields.php'); }
    $f = $r;
}
$errs = [];
if (isPost()) {
    verify_csrf();
    $f['name'] = trim($_POST['name'] ?? ''); $f['type'] = $_POST['type'] ?? ''; $f['description'] = trim($_POST['description'] ?? '');
    $f['price_per_hour'] = filter_var($_POST['price_per_hour'] ?? '', FILTER_VALIDATE_INT); $f['facilities'] = trim($_POST['facilities'] ?? ''); $f['status'] = $_POST['status'] ?? '';
    if ($f['name'] === '') $errs[] = 'Nama wajib diisi.';
    if (!in_array($f['type'], ['Futsal', 'Badminton'], true)) $errs[] = 'Jenis tidak valid.';
    if (!$f['price_per_hour'] || $f['price_per_hour'] < 1) $errs[] = 'Harga harus angka lebih dari 0.';
    if (!in_array($f['status'], ['active', 'inactive'], true)) $errs[] = 'Status tidak valid.';
    if (!$errs) {
        try {
            $v = [$f['name'], $f['type'], $f['description'], $f['price_per_hour'], $f['facilities'], $f['status']];
            if ($id) $pdo->prepare('UPDATE fields SET name=?,type=?,description=?,price_per_hour=?,facilities=?,status=? WHERE id=?')->execute([...$v, $id]);
            else $pdo->prepare('INSERT INTO fields(name,type,description,price_per_hour,facilities,status) VALUES(?,?,?,?,?,?)')->execute($v);
            flash('Lapangan disimpan.'); redirect('admin/fields.php');
        } catch (PDOException $x) { error_log($x->getMessage()); $errs[] = 'Terjadi kesalahan. Silakan coba lagi.'; }
    }
}
layout_head($id ? 'Edit Lapangan' : 'Tambah Lapangan', adminNav());
?>
<form method="post" class="card narrow"><h1><?= $id ? 'EDIT' : 'TAMBAH' ?> LAPANGAN</h1>
<?php foreach ($errs as $x) echo '<div class="alert err">' . e($x) . '</div>'; ?><?= csrf_field() ?>
<label>Nama<input name="name" required value="<?= e($f['name']) ?>"></label>
<label>Jenis<select name="type"><?php foreach (['Futsal', 'Badminton'] as $t): ?><option <?= $f['type'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
<label>Deskripsi<textarea name="description"><?= e($f['description']) ?></textarea></label>
<label>Harga per jam (Rp)<input type="number" min="1" name="price_per_hour" required value="<?= e($f['price_per_hour']) ?>"></label>
<label>Fasilitas<input name="facilities" value="<?= e($f['facilities']) ?>"></label>
<label>Status<select name="status"><?php foreach (['active', 'inactive'] as $t): ?><option <?= $f['status'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
<button class="btn">SIMPAN</button> <a href="fields.php">Batal</a></form>
<?php layout_foot();
