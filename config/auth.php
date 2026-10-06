<?php
require_once __DIR__ . '/functions.php';
function currentUser(): ?array { return $_SESSION['user'] ?? null; }
function isLoggedIn(): bool { return currentUser() !== null; }
function isAdmin(): bool { return (currentUser()['role'] ?? '') === 'admin'; }
function isCustomer(): bool { return (currentUser()['role'] ?? '') === 'customer'; }
function requireLogin(): void { if (!isLoggedIn()) { flash('Silakan login terlebih dahulu.', 'err'); redirect('auth/login.php'); } }
function requireAdmin(): void { requireLogin(); if (!isAdmin()) redirect('customer/index.php'); }
function requireCustomer(): void { requireLogin(); if (!isCustomer()) redirect('admin/dashboard.php'); }
function customerId(): int {
    $s = db()->prepare('SELECT id FROM customers WHERE user_id = ?'); $s->execute([currentUser()['id']]);
    return (int)($s->fetchColumn() ?: 0);
}
function logoutUser(): void { $_SESSION = []; session_destroy(); }
