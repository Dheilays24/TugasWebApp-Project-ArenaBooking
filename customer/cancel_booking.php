<?php
require_once __DIR__ . '/../config/auth.php';
requireCustomer();
if (!isPost()) redirect('customer/bookings.php');
verify_csrf();
$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT); $pdo = db();
try {
    $pdo->beginTransaction();
    $s = $pdo->prepare('SELECT * FROM bookings WHERE id = ? AND customer_id = ? FOR UPDATE');
    $s->execute([$id ?: 0, customerId()]); $b = $s->fetch();
    if (!$b) throw new RuntimeException('Booking tidak ditemukan.');
    if (!in_array($b['status'], ['pending', 'confirmed'], true) || $b['booking_date'] < date('Y-m-d')) throw new RuntimeException('Booking tidak dapat dibatalkan.');
    $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id = ?")->execute([$b['id']]);
    $pdo->prepare("UPDATE schedules SET status='available' WHERE id = ?")->execute([$b['schedule_id']]);
    $pdo->commit(); flash('Booking dibatalkan.');
} catch (RuntimeException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); flash($x->getMessage(), 'err'); }
catch (PDOException $x) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($x->getMessage()); flash('Terjadi kesalahan. Silakan coba lagi.', 'err'); }
redirect('customer/bookings.php');
