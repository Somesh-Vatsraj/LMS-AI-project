<?php
session_start();
date_default_timezone_set('UTC');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

define('BASE_URL', '/lms-ai'); // Adjust depending on folder structure
