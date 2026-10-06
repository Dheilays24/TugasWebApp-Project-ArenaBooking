<?php
require_once __DIR__ . '/../config/auth.php';
if (isLoggedIn()) redirect('index.php');
$err = '';
if (isPost()) {
    verify_csrf();
    $email = trim($_POST['email'] ?? ''); $pass = (string)($_POST['password'] ?? '');
    try {
        $s = db()->prepare('SELECT id,name,email,password,role FROM users WHERE email = ?'); $s->execute([$email]);
        $u = $s->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
            redirect($u['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/index.php');
        }
        $err = 'Email atau password salah.';
    } catch (PDOException $x) { error_log($x->getMessage()); $err = 'Terjadi kesalahan. Silakan coba lagi.'; }
}
layout_head('Login');
?>
<form method="post" class="card narrow"><h1>LOGIN</h1>
<?php if ($err) echo '<div class="alert err">' . e($err) . '</div>'; ?>
<?= csrf_field() ?>
<label>Email<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
<label>Password<input type="password" name="password" required></label>
<button class="btn">MASUK</button>
<p class="muted">Belum punya akun? <a href="register.php">Daftar</a></p></form>
<?php layout_foot();
