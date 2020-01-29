<?php

//Local
$localDB = array(
    'host' => 'localhost:3306',
    'db' => 'civil_real_estate_bos',
    'user' => 'root',
    'pass' => 'root'
);

define('DB_PREFIX', 'bos_'); //BOS DB TABLE PREFIX
if (DEV_MODE) {
    define('DB_HOST', $localDB['host']); //BOS DB HOST
    define('DB_NAME', $localDB['db']); //BOS DB NAME
    define('DB_USER', $localDB['user']); //BOS DB USER
    define('DB_PASS', $localDB['pass']); //BOS DB PASSWORD
} 
