<?php

define('FRMWK_GI_DATASEARCH_VER', 'v4.0');
$curIncludePath = get_include_path();
set_include_path($curIncludePath . '/GI_DataSearch/' . FRMWK_GI_DATASEARCH_VER);
require_once 'GI_DataSearchFilterable.php';
require_once 'GI_DataSearch.php';
require_once 'GI_DataSearchJoin.php';
require_once 'GI_DataSearchGroup.php';
require_once 'GI_DataSearchFilter.php';
require_once 'GI_DataSearchCase.php';
require_once 'GI_DataSearchOrderByCase.php';
set_include_path($curIncludePath);
