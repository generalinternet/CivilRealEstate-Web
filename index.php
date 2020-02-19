<?php
/** 
 * @const DEV_MODE Boolean, determines settings in "config.database.php" and "config.project.php" 
 */
DEFINE('DEV_MODE', false);
DEFINE('STAGING_MODE', false);
//Implied Production Mode if DEV_MODE and STAGING_MODE are both False.

DEFINE('FORCE_ERRORS_ON', false);

if (DEV_MODE || FORCE_ERRORS_ON) {
    error_reporting(E_ALL & ~E_STRICT);
} else {
    error_reporting(E_ERROR);
    ini_set('display_errors', 0);
}

require_once 'config/reqs.php';
//@todo change to support multi-language selection
require_once 'config/language/lang_english.php';
//@todo Set Timezone to detected timezone
GI_Time::setTimezone('America/Vancouver');

die('TEST 05');
GI_Index::initSystem();

GI_Index::routeRequest();