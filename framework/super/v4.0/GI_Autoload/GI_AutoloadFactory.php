<?php
require_once 'config/client_config/config.autoload.php';

$map = array(
//    'default' => 'model/Factory/Core/'
);

$map = array_merge($map, unserialize(AUTOLOAD_MAP_FACTORY));

return $map;
