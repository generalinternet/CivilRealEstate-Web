<?php
require_once 'config/client_config/config.autoload.php';

$map = array(
//    'default' => 'model/Domain/Core/'
);

$map = array_merge($map, unserialize(AUTOLOAD_MAP_DEFAULT));

return $map;
