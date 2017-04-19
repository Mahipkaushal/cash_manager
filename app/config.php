<?php

define('APP', 'Expense_Manager');
define('APP_ID', '1');
define('APP_VERSION', '1.0');


// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'locahost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'expense');
define('DB_PREFIX', '');

$dbConfig = array(
    'driver'        => DB_DRIVER,
    'hostname'      => DB_HOSTNAME,
    'username'      => DB_USERNAME,
    'password'      => DB_PASSWORD,
    'database'      => DB_DATABASE,
    'prefix'        => DB_PREFIX
);

$date = new DateTime();
$date->setTimezone(new DateTimeZone('Asia/Kolkata'));

$mdate = $date->format('Y-m-d H:i:s');
define('REAL_TIME', $mdate);

// To turn on SMS notification set SMS_SERVICE to true and Set your MSG91 Auth key below
define('SMS_SERVICE', false);

// Replace <YOUR_MSG91_AUTH_KEY> with your Auth Key provided by msg91
define('MSG91_AUTH_KEY', '<YOUR_MSG91_AUTH_KEY>');
