<?php
require 'include/session_check.php';
echo "<pre>";
echo "PHP default timezone: " . date_default_timezone_get() . PHP_EOL;
echo "PHP now (date):        " . date('Y-m-d H:i:s P') . PHP_EOL;
$dt = new DateTime();
echo "new DateTime():       " . $dt->format('Y-m-d H:i:s P') . PHP_EOL;
echo "</pre>";
