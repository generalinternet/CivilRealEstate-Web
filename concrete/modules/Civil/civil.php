<?php

$curIncludePath = get_include_path();
set_include_path('concrete/modules');
require_once 'RealEstate/v4.0/realEstate.php';
set_include_path($curIncludePath);