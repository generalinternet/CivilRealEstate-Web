<?php

$curIncludePath = get_include_path();
set_include_path('concrete/modules');
require_once 'RealEstate/v4.0/realEstate.php';
require_once 'Civil/CharityFactory.php';
// require_once 'Civil/Charity.php';
require_once 'Civil/view/CharityFormView.php';
set_include_path($curIncludePath);