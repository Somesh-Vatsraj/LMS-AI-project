<?php
require_once __DIR__ . '/config/app.php';
logout_user($pdo);
set_flash_message('success', 'You have been logged out.');
redirect('/login.php');
