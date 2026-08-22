<?php
require_once __DIR__ . '/lib/auth.php';
logout_all();
header('Location: landing.php');
exit;
