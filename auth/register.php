<?php
require_once __DIR__ . '/../config/auth.php';
if (isLoggedIn()) redirect('index.php');
$errs = [];
if (isPost()) {
    verify_csrf();
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $pw = (string)($_POST['password'] ?? '');
    $pw2 = (string)($_POST['password2'] ?? ''); $phone = trim($_POST['phone'] ?? ''); $addr = trim($_POST['address'] ?? '');
    if ($name === '') $errs[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'Email tidak valid.';
    if (strlen($pw) < 6) $errs[] = 'Password minimal 6 karakter.';
    if ($pw !== $pw2) $errs[] = 'Konfirmasi password tidak sama.';
    if ($phone === '') $errs[] = 'Nomor HP wajib diisi.';
    try {
        if (!$errs) {
            $c = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ?'); $c->execute([$email]);
            if ($c->fetchColumn() > 0) $errs[] = 'Email sudah digunakan.';
        }
        if (!$errs) {
            $pdo = db(); $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,"customer")')->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT)]);
            $pdo->prepare('INSERT INTO customers(user_id,phone,address) VALUES(?,?,?)')->execute([$pdo->lastInsertId(), $phone, $addr]);
            $pdo->commit(); flash('Registrasi berhasil. Silakan login.'); redirect('auth/login.php');
        }
    } catch (PDOException $x) { if (db()->inTransaction()) db()->rollBack(); error_log($x->getMessage()); $errs[] = 'Terjadi kesalahan. Silakan coba lagi.'; }
}
layout_head('Register');
?>
<form method="post" class="card narrow"><h1>DAFTAR</h1>
<?php foreach ($errs as $x) echo '<div class="alert err">' . e($x) . '</div>'; ?>
<?= csrf_field() ?>
<label>Nama<input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></label>
<label>Email<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
<label>Password<input type="password" name="password" required></label>
<label>Konfirmasi Password<input type="password" name="password2" required></label>
<label>Nomor HP<input name="phone" required value="<?= e($_POST['phone'] ?? '') ?>"></label>
<label>Alamat<textarea name="address"><?= e($_POST['address'] ?? '') ?></textarea></label>
<button class="btn">DAFTAR</button><p class="muted">Sudah punya akun? <a href="login.php">Login</a></p></form>
<?php layout_foot();
