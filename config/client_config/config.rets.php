<?php

define('RETS_THUMBNAIL_LOGO', 'http://mlsr.realtylink.org/mlsrcommon/images/mlsrlogosmall.gif ');

define('RETS_DISPLAY_LOGO', 'http://mlsr.realtylink.org/mlsrcommon/images/mlsrlogo.gif');

define('RETS_DB_PREFIX', 'bos_'); //BOS DB TABLE PREFIX

define('RETS_MODIFY_ROWS', false);

define('RETS_REALTOR_IDS', serialize(array(
    
)));

if (DEV_MODE) {
    define('RETS_DB_HOST', 'localhost:3306');
    define('RETS_DB_NAME', 'mls_dev_bos');
    define('RETS_DB_USER', 'root');
    define('RETS_DB_PASS', 'root');
}
