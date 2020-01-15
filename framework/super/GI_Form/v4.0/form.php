<?php

define('FRMWK_GI_FORM_VER', 'v4.0');
$curIncludePath = get_include_path();
set_include_path($curIncludePath . '/GI_Form/' . FRMWK_GI_FORM_VER);
require_once 'AbstractGI_Form.php';
require_once 'AbstractGI_FormItem.php';
require_once 'AbstractGI_FormFieldFactory.php';
require_once 'GI_FormItems/AbstractGI_FormField.php';
require_once 'GI_FormItems/AbstractGI_FormFieldset.php';
require_once 'GI_FormItems/AbstractGI_FormStep.php';
set_include_path('concrete/super/GI_Form/' . FRMWK_GI_FORM_VER);
require_once 'GI_Form.php';
require_once 'GI_FormItem.php';
require_once 'GI_FormItems/GI_FormField.php';
require_once 'GI_FormItems/GI_FormFieldset.php';
require_once 'GI_FormItems/GI_FormStep.php';
set_include_path($curIncludePath);
