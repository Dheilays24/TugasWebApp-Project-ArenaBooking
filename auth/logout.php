<?php
require_once __DIR__ . '/../config/auth.php';
logoutUser(); session_start(); flash('Anda sudah logout.'); redirect('auth/login.php');
