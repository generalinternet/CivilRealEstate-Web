<?php

define('MODULE_REALESTATE_VER', 'v4.0');

$curIncludePath = get_include_path();
set_include_path('concrete/modules/RealEstate/' . MODULE_REALESTATE_VER);
    require_once 'view/REListing/mls_openHouseView.php';
    require_once 'view/REListing/mls_openHouseItemView.php';
set_include_path($curIncludePath);