<?php
require_once __DIR__ . '/api/util.php';

auth_api()->logout();

header('Location: /index.php');
exit;

