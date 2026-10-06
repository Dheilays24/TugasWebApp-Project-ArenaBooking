<?php
require_once __DIR__ . '/database.php';
const BASE = '/ArenaBook';
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . BASE . '/' . ltrim($url, '/')); exit; }
function isPost(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void {
    $t = $_POST['csrf_token'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) { http_response_code(403); exit('Token tidak valid. Silakan muat ulang halaman.'); }
}
function flash(?string $msg = null, string $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function formatRupiah($n): string { return 'Rp' . number_format((int)$n, 0, ',', '.'); }
function formatTanggalIndonesia(string $d): string {
    $b = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $t = strtotime($d); return date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}
function generateBookingCode(string $date): string {
    return 'AH-' . date('Ymd', strtotime($date)) . '-' . strtoupper(bin2hex(random_bytes(3)));
}
function validDate(string $d): bool { $x = DateTime::createFromFormat('Y-m-d', $d); return $x && $x->format('Y-m-d') === $d; }
function icon(string $n): string {
    static $p = [
        'grid' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
        'cal' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'court' => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="M12 5v14"/><circle cx="12" cy="12" r="2.5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-5 7-5s7 1.5 7 5M17 4.5a3.5 3.5 0 010 7M22 20c0-3-2-4.5-4-5"/>',
        'out' => '<path d="M9 4H5a1 1 0 00-1 1v14a1 1 0 001 1h4M16 8l4 4-4 4M20 12H9"/>'];
    return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$n] ?? '') . '</svg>';
}
function layout_head(string $title, string $nav = ''): void {
    $u = $_SESSION['user'] ?? null; $f = flash();
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e($title) . ' - ArenaBook</title><link rel="stylesheet" href="' . BASE . '/assets/css/style.css"></head>';
    if (!$u) {
        echo '<body class="guest"><div class="brand center">ARENA<span>BOOK</span><small>Sports Booking</small></div><main class="content">';
    } else {
        $admin = ($u['role'] ?? '') === 'admin';
        $menu = $admin
            ? [['admin/dashboard.php', 'grid', 'Dashboard'], ['admin/bookings.php', 'list', 'Bookings'], ['admin/fields.php', 'court', 'Courts'], ['admin/schedules.php', 'clock', 'Schedules'], ['admin/customers.php', 'users', 'Customers']]
            : [['customer/index.php', 'grid', 'Dashboard'], ['customer/booking.php', 'cal', 'Book Court'], ['customer/bookings.php', 'list', 'Booking Saya'], ['customer/profile.php', 'user', 'Profile']];
        $alias = ['admin/field_create.php' => 'admin/fields.php', 'admin/field_edit.php' => 'admin/fields.php', 'admin/booking_detail.php' => 'admin/bookings.php', 'customer/booking_detail.php' => 'customer/bookings.php'];
        $cur = basename(dirname($_SERVER['SCRIPT_NAME'])) . '/' . basename($_SERVER['SCRIPT_NAME']);
        $cur = $alias[$cur] ?? $cur;
        echo '<body><div class="app"><aside class="side"><a class="brand" href="' . BASE . '/">ARENA<span>BOOK</span><small>Sports Booking</small></a><nav class="menu">';
        foreach ($menu as $m) echo '<a href="' . BASE . '/' . $m[0] . '" class="' . ($cur === $m[0] ? 'active' : '') . '">' . icon($m[1]) . '<span>' . e($m[2]) . '</span></a>';
        echo '</nav><div class="me"><div class="av">' . e(strtoupper(mb_substr($u['name'], 0, 1))) . '</div><div class="who"><b>' . e($u['name']) . '</b><small>' . ($admin ? 'Administrator' : 'Customer') . '</small></div>';
        echo '<a class="out" href="' . BASE . '/auth/logout.php" title="Logout">' . icon('out') . '</a></div></aside><div class="main">';
        echo '<header class="top"><span class="eyebrow">ARENA BOOK / ' . ($admin ? 'ADMIN' : 'CUSTOMER') . '</span><span class="ptitle">' . e($title) . '</span></header><main class="content">';
    }
    if ($f) echo '<div class="alert ' . e($f[1]) . '">' . e($f[0]) . '</div>';
}
function layout_foot(): void { echo '</main>' . (isset($_SESSION['user']) ? '</div></div>' : '') . '<script src="' . BASE . '/assets/js/script.js"></script></body></html>'; }
function customerNav(): string { return ''; }
function adminNav(): string { $b = BASE; return "<a href='$b/admin/dashboard.php'>Dashboard</a><a href='$b/admin/fields.php'>Lapangan</a><a href='$b/admin/schedules.php'>Jadwal</a><a href='$b/admin/bookings.php'>Booking</a><a href='$b/admin/customers.php'>Customer</a><a href='$b/auth/logout.php'>Logout</a>"; }
function ensureSchedules(string $date): void {
    $pdo = db();
    $ins = $pdo->prepare('INSERT IGNORE INTO schedules(field_id,schedule_date,start_time,end_time,status) VALUES(?,?,?,?,"available")');
    foreach ($pdo->query("SELECT id FROM fields WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN) as $fid)
        for ($h = 7; $h < 22; $h++) $ins->execute([$fid, $date, sprintf('%02d:00:00', $h), sprintf('%02d:00:00', $h + 1)]);
}
