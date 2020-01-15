<?php

$retsCreds = array(
    'rets_res_login' => 'http://reb.retsiq.com/contactres/rets/login',
    'rets_res_version' => '1.7.2',
    'rets_res_user' => 'RETSGENINT',
    'rets_res_pass' => 'RE@LE$7AT3',
    'rets_res_user_agent' => 'RETSGeneralInternet/1.0',
    'rets_res_user_agent_pass' => '63F#0$$%6b*i@)@',
    'rets_com_login' => 'http://reb.retsiq.com/contact/rets/login',
    'rets_com_version' => '1.8',
    'rets_com_user' => 'RETSGENINT',
    'rets_com_pass' => 'RE@LE$7AT3',
    'rets_com_user_agent' => 'RETSGeneralInternet/1.0',
    'rets_com_user_agent_pass' => '63F#0$$%6b*i@)@'
);

$retsDevCreds = $retsCreds;
$retsDevCreds = array(
    'rets_res_login' => 'http://reb-stage.apps.retsiq.com/contactstageres/rets/login',
    'rets_com_login' => 'http://reb-stage.apps.retsiq.com/contactstage/rets/login'
);

$currentRets = $retsCreds;

define('RETS_RES_LOGIN', $currentRets['rets_res_login']);

define('RETS_RES_VERSION', $currentRets['rets_res_version']);

define('RETS_RES_USER', $currentRets['rets_res_user']);

define('RETS_RES_PASS', $currentRets['rets_res_pass']);

define('RETS_RES_USER_AGENT', $currentRets['rets_res_user_agent']);

define('RETS_RES_USER_AGENT_PASS', $currentRets['rets_res_user_agent_pass']);

define('RETS_COM_LOGIN', $currentRets['rets_com_login']);

define('RETS_COM_VERSION', $currentRets['rets_com_version']);

define('RETS_COM_USER', $currentRets['rets_com_user']);

define('RETS_COM_PASS', $currentRets['rets_com_pass']);

define('RETS_COM_USER_AGENT', $currentRets['rets_com_user_agent']);

define('RETS_COM_USER_AGENT_PASS', $currentRets['rets_com_user_agent_pass']);

define('RETS_THUMBNAIL_LOGO', 'http://mlsr.realtylink.org/mlsrcommon/images/mlsrlogosmall.gif ');

define('RETS_DISPLAY_LOGO', 'http://mlsr.realtylink.org/mlsrcommon/images/mlsrlogo.gif');

//Local
$retsLocalDB = array(
    'host' => 'localhost:3306',
    'db' => 'mls_dev_bos',
    'user' => 'root',
    'pass' => 'root'
);
//AWS - mySQL
$retsLiveDB = array(
    'host' => 'mls-central.cuixwp9bhwxo.us-west-2.rds.amazonaws.com:3306',
    'db' => 'mls_central_bos',
    'user' => 'Realtor5000',
    'pass' => 'RN8OyVL7xgfScH'
);

if (!DEV_MODE) {
    $retsCurrentDB = $retsLiveDB;
} else {
    $retsCurrentDB = $retsLocalDB;
}

define('RETS_DB_HOST', $retsCurrentDB['host']); //BOS DB HOST
define('RETS_DB_NAME', $retsCurrentDB['db']); //BOS DB NAME
define('RETS_DB_USER', $retsCurrentDB['user']); //BOS DB USER
define('RETS_DB_PASS', $retsCurrentDB['pass']); //BOS DB PASSWORD
define('RETS_DB_PREFIX', 'bos_'); //BOS DB TABLE PREFIX
