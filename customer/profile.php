<?php
require_once __DIR__ . '/../config/auth.php';
requireCustomer(); $pdo = db(); $uid = currentUser()['id']; $errs = [];
if (isPost()) {
    verify_csrf();
    $name = trim($_POST['name'] ?? ''); $phone = trim($_POST['phone'] ?? ''); $addr = trim($_POST['address'] ?? '');
    if ($name === '') $errs[] = 'Nama wajib diisi.';
    if ($phone === '') $errs[] = 'Nomor HP wajib diisi.';
    if (!$errs) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE users SET name=? WHERE id=?')->execute([$name, $uid]);
            $pdo->prepare('UPDATE customers SET phone=?,address=? WHERE user_id=?')->execute([$phone, $addr, $uid]);
            $pdo->commit(); $_SESSION['user']['name'] = $name; flash('Profil diperbarui.'); redirect('customer/profile.php');
        } catch (PDOException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($x->getMessage()); $errs[] = 'Terjadi kesalahan. Silakan coba lagi.'; }
    }
}
$s = $pdo->prepare('SELECT u.name,u.email,c.phone,c.address FROM users u JOIN customers c ON c.user_id=u.id WHERE u.id=?'); $s->execute([$uid]); $p = $s->fetch();
layout_head('Profile', customerNav());
?>
<form method="post" class="card narrow"><h1>PROFILE</h1><?php foreach ($errs as $x) echo '<div class="alert err">' . e($x) . '</div>'; ?><?= csrf_field() ?>
<label>Email<input value="<?= e($p['email']) ?>" disabled></label>
<label>Nama<input name="name" required value="<?= e($p['name']) ?>"></label>
<label>Nomor HP<input name="phone" required value="<?= e($p['phone']) ?>"></label>
<label>Alamat<textarea name="address"><?= e($p['address']) ?></textarea></label><button class="btn">SIMPAN</button></form>
<?php layout_foot();
