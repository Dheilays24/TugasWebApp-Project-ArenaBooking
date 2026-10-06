<?php
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
$rows = db()->query('SELECT * FROM fields ORDER BY type,name')->fetchAll();
layout_head('Lapangan', adminNav());
?>
<h1>KELOLA <span>LAPANGAN</span></h1><p><a class="btn" href="field_create.php">+ TAMBAH</a></p>
<div class="table-wrap"><table><tr><th>Nama</th><th>Jenis</th><th>Harga/jam</th><th>Fasilitas</th><th>Status</th><th>Aksi</th></tr>
<?php foreach ($rows as $f): ?><tr><td><?= e($f['name']) ?></td><td><?= e($f['type']) ?></td><td><?= formatRupiah($f['price_per_hour']) ?></td><td><?= e($f['facilities']) ?></td><td><span class="tag <?= $f['status'] === 'active' ? 'available' : 'blocked' ?>"><?= e($f['status']) ?></span></td>
<td><a href="field_edit.php?id=<?= (int)$f['id'] ?>">Edit</a>
<form method="post" action="field_delete.php" class="inline" data-confirm="Hapus/nonaktifkan lapangan ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$f['id'] ?>"><button class="btn danger sm">Hapus</button></form></td></tr><?php endforeach; ?></table></div>
<?php layout_foot();
