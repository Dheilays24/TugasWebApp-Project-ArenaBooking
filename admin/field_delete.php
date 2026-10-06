<?php
require_once __DIR__ . '/../config/auth.php';
requireAdmin();
if (!isPost()) redirect('admin/fields.php');
verify_csrf();
$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT); $pdo = db();
try {
    $pdo->beginTransaction();
    $s = $pdo->prepare('SELECT id FROM fields WHERE id = ? FOR UPDATE'); $s->execute([$id ?: 0]);
    if (!$s->fetch()) throw new RuntimeException('Lapangan tidak ditemukan.');
    $c = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE field_id = ?'); $c->execute([$id]);
    if ($c->fetchColumn() > 0) {
        $pdo->prepare("UPDATE fields SET status='inactive' WHERE id = ?")->execute([$id]);
        $msg = 'Lapangan sudah punya booking, jadi dinonaktifkan (tidak dihapus).';
    } else {
        $pdo->prepare('DELETE FROM schedules WHERE field_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM fields WHERE id = ?')->execute([$id]);
        $msg = 'Lapangan dihapus.';
    }
    $pdo->commit(); flash($msg);
} catch (RuntimeException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); flash($x->getMessage(), 'err'); }
catch (PDOException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($x->getMessage()); flash('Terjadi kesalahan. Silakan coba lagi.', 'err'); }
redirect('admin/fields.php');
