<?php
require_once __DIR__ . '/config/auth.php';
if (!isLoggedIn()) redirect('auth/login.php');
redirect(isAdmin() ? 'admin/dashboard.php' : 'customer/index.php');
