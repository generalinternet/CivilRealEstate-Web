<?php
//External Dependencies
require 'vendor/autoload.php';

define('FRMWK_VERSION', 'v4');
set_include_path('framework/super');
    require_once FRMWK_VERSION . '.0/super.php';
    require_once 'GI_Form/' . FRMWK_VERSION . '.0/form.php';
    require_once 'GI_DataSearch/' . FRMWK_VERSION . '.0/dataSearch.php';
set_include_path('');
die('TEST 07');

set_include_path('framework/core');
    require_once FRMWK_VERSION . '.0/core.php';
set_include_path('');
die('TEST 08');

set_include_path('framework/modules');
//    require_once 'Accounting/' . FRMWK_VERSION . '.0/accounting.php';
   require_once 'Contact/' . FRMWK_VERSION . '.0/contact.php';
die('TEST 09');
//    require_once 'Inventory/' . FRMWK_VERSION . '.0/inventory.php';
//    require_once 'Invoice/' . FRMWK_VERSION . '.0/invoice.php';
    require_once 'Content/' . FRMWK_VERSION . '.0/content.php';
die('TEST 10');
//    require_once 'Billing/' . FRMWK_VERSION . '.0/billing.php';
//    require_once 'Schedule/' . FRMWK_VERSION . '.0/schedule.php';
//    require_once 'Order/' . FRMWK_VERSION . '.0/order.php';
//    require_once 'Blog/' . FRMWK_VERSION . '.0/blog.php';
//    require_once 'Project/' . FRMWK_VERSION . '.0/project.php';
//    require_once 'Chat/' . FRMWK_VERSION . '.0/chat.php';
//    require_once 'Forms/' . FRMWK_VERSION . '.0/forms.php';
//    require_once 'QnA/' . FRMWK_VERSION . '.0/qna.php';
    require_once 'MLS/' . FRMWK_VERSION . '.0/mls.php';
    require_once 'RealEstate/' . FRMWK_VERSION . '.0/realEstate.php';
die('TEST 11');
set_include_path('');

require_once 'concrete/modules/Civil/civil.php';
