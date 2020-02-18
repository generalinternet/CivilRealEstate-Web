<?php
/**
 * Description of config.project
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
//Local
$localHost = array(
    'siteBase' => '/CivilRealEstate-Web/',
    'projectBase' => '/CivilRealEstate-Web',
    'aws_bucket' => 'gi12345'
);
$stagingHost = array(
    'siteBase' => '',
    'projectBase' => '',
    'aws_bucket' => 'gi12345'
);

//AWS
$liveHost = array(
    'siteBase' => '',
    'projectBase' => '',
    'aws_bucket' => ''
);

if(DEV_MODE){
    $currentHost = $localHost;
} else if (STAGING_MODE) {
    $currentHost = $stagingHost;
}else {
    $currentHost = $liveHost;
}
    
/** @const should be unique per project to avoid conflicts */
define('APP_REF', 'civil-real-estate');

define('SESSION_NAME', APP_REF);

/** @const base site title */
define('SITE_NAME', 'Civil Real Estate');

/** @const base site title */
define('SITE_TITLE', 'Civil Real Estate Solutions');

/** @const email address of root user account */
define('ROOT_USER_EMAIL', 'admin@generalinternet.ca');

/** @const email address for customer support */
define('SUPPORT_EMAIL', 'support@generalinternet.ca');

/** @const boolean whether or not this application is deployed to a worker environment or not */
define ('IS_WORKER_SERVER', false);

/** @const boolean, whether or not to use clean urls, static websites should be set to "true" */
define('CLEAN_URLS', false);

/** @const boolean, whether or not to use bootstrap */
define('USE_BOOTSTRAP', true);

/** @const boolean, which bootstrap version to use */
define('BOOTSTRAP_VERSION', '3.3.7');

/** @const boolean, whether or not to include font-awesome */
define('USE_FONT_AWESOME', true);

/** @const adds the "powered by gi" logo in the footer */
define('POWERED_BY_GI', true);

/** @const HTML_PROTOCOL */
if (DEV_MODE) {
    define('HTML_PROTOCOL','http');
} else if (STAGING_MODE) {
    define('HTML_PROTOCOL','https');
} else {
    define('HTML_PROTOCOL','https');
}

/** @const Boolean, whether or not to store models when using "::getById()" */
define('STORE_MODELS', true);

define('REVERSE_SITE_TITLE', true);

/*****AWS *****/
    define('AWS_BUCKET', $currentHost['aws_bucket']);

    define('AWS_REGION', 'us-west-2');
    
    define('SQS_QUEUE_NAME', '');
    
    define('SQS_QUEUE_URL', '');
    
    define('AWS_ACCOUNT_ID', '239417912507');
/* * ******* */

/* * ***Socket Constants**** */
/** @const boolean, whether to open the notification socket for push notifications */
define('OPEN_SOCKET', false);

define('SOCKET_SERVER_URL', 'socket2me.businessos.ca');

if (defined('HTML_PROTOCOL') && HTML_PROTOCOL === 'https') {
    define('SOCKET_SERVER_PORT', '8000');  //https;
} else {
    define('SOCKET_SERVER_PORT', '8001'); //http
}

define('ENABLE_BROWSER_NOTIFICATIONS', true);

//define('CHAT_ENABLED', true);
define('CHAT_ENABLED', false); //TEMP
/* * ******* */

/** @const boolean, whether the site allows file uploading */
define('FILE_UPLOADS', true);

/** @const html base tag */
define('SITE_BASE', $currentHost['siteBase']);

/** @const site folder location (ex. if hosted in sub dir. "test" project base should be "/test" */
define('PROJECT_BASE', $currentHost['projectBase']);

/** @const default controller to use if none specified */
define('DEFAULT_CONTROLLER', 'static');

/** @const default action of the default controller to use if none specified */
define('DEFAULT_ACTION', 'dashboard');

/** @const defines if the public view is used in this system */
define('PUBLIC_VIEW_ACTIVE', true);

/** @const default controller for public pages to use if none specified */
define('DEFAULT_PUBLIC_CONTROLLER', 'static');

/** @const default action of the default controller for public pages to use if none specified */
define('DEFAULT_PUBLIC_ACTION', 'home');

/** @const default items per page for UITables */
define('UITABLE_ITEMS_PER_PAGE', 10);

/** @const default autocomplete result items */
define('AUTOCOMPLETE_ITEM_LIMIT', 10);

/** @const whether to show the results per page picker or not [10, 25, 50, 100] */
define('SHOW_RESULTS_PER_PAGE_PICKER', false);

/** @const from email address for transactional emails */
define('SERVER_EMAIL_ADDR', 'noreply@businessos.ca');

/** @const from email name for transactional emails */
define('SERVER_EMAIL_NAME', 'No Reply');

/** @const does registering require a confirmation (if false password will be required on registration form) */
define('REGISTER_REQUIRES_CONFIRMATION', false);

/** @const does registering require a confirmation code (this is for quick email/phone verification within a registration form) */
define('REGISTER_REQUIRES_CODE_CONFIRMATION', true);

/** @const does the site allow users to register themselves */
define('REGISTRATION_ENABLED', false);

/*****Mandrill Constants*****/
    define('MANDRILL_ENABLED', true);
    
    define('MANDRILL_HOST', 'smtp.mandrillapp.com');

    define('MANDRILL_PORT', '587');

    define('MANDRILL_SMTP_USERNAME', 'General Internet Inc.');
    
    define('MANDRILL_SUBACCOUNT', 'civil-real-estate');
    
    define('MANDRILL_DEFAULT_TAG', SESSION_NAME);
/**********/

define('DEFAULT_CONTENT_BLOCK_TITLE_TAG', 'h3');

define('DEFAULT_CURRENCY_REF', 'cad');

define('DEFAULT_PRICING_REGION_REF', 'global');

define('IS_FRANCHISED_SYSTEM', false);
//define('IS_FRANCHISED_SYSTEM', true);

/** @const whether the system using multiple currencies or not */
define('MULTI_CURRENCY', true);

define('MULTI_COUNTRY', false);

/** @const the number of minutes a user confirm code is valid for */
define('USER_CONFIRM_TTL', '90');

define('DEFAULT_FISCAL_YEAR_START', '06-01');

define('DEFAULT_REGION', 'BC');

define('DEFAULT_COUNTRY', 'CAN');

define('DEFAULT_TAX_REFS', serialize(array(
    //'gst'
)));

define('USE_COLOUR_PICKER', true);

define('USE_WYSIWYG', true);

define('USE_GI_WIZARD', true);

define('USE_GI_TABS', true);

define('USE_FULLSCREEN_TOGGLE', true);

define('BYPASS_EMAIL_CONFIMATION', false);

define('SYSTEM_LIVE_DATE', '2019-01-01');

define('MULTI_ACCOUNTING_LOCATIONS', true);

define('DEFAULT_ACCOUNTING_LOCATION_REF', 'canada');

define('DEFAULT_FOB_SHIPPING_TYPE_REF', 'dest');

define('DEFAULT_TAX_COLLECTION_TYPE_REF', 'dest');

define('FOB_DETERMINES_TAX_COLLECTION', true);

define('DEFAULT_ECO_FEE_COUNTRY_CODES', 'CAN');

define('DEFAULT_AR_INVOICE_EXPORT_DAY_RANGE_BREAKS_STRING', '0,30,60');

define('DEFAULT_ROUND_PRECISION', 5);

define('FIXER_IO_API_KEY', '511c8a9fd15cd28d50667a0345eba6fb');

define('USE_CONTACT_CAT_INDEX', true);

define('DEFAULT_TI_START_TIME', '9:00');

define('DEFAULT_TI_END_TIME', '17:30');

/*****SMS MESSAGING SERVICE*****/
    define('TWILIO_ENABLED', true);
    
    define('TWILIO_TEST_SID', 'AC2d3708e983ac0f3534dfd87218e803d8');

    define('TWILIO_TEST_AUTH_TOKEN', 'd148b5e74590466c1f453075ea9246a5');

    define('TWILIO_PHONE_NUMBER', '+1 778 949 3111');
/**********/

/*****SYNTAX_HIGHLIGHTER*****/
    define('USE_SYNTAXHIGHLIGHTER', true);

    define('SYNTAXHIGHLIGHTER_STYLE', 'default');
/**********/

/*****INVENTORY*****/
    /** @const how this system handles outgoing stock (ex. FIFO first in first out, LIFO last in first out) */
    define('STOCK_METHOD', 'FIFO');

    /** @const set to true if there is no need to keep track of specific containers in the inventory module */
    define('COMMODITY_BASED', false);
    
    /** @const set to true if you are an inventory system with large quantities of stock */
    define('USE_STOCK_GROUPS', true);
    
    /** @const set to true if you are an inventory system that has bulk items */
    define('USE_BULK_ITEMS', true);
    
    /** @const specify precision for inv_stock  */
    define('STOCK_UNIT_PRECISION', 3);
    
    /** @const specify percentage filled accuracy for item package (ex. 10 would allow for item packages give or take 10%)  */
    define('STOCK_SELECT_WITHIN', 10);
    
    /** @const can non full item package containers be consolidated  */
    define('ALLOW_LOOSE_STOCK_CONSOLIDATION', true);
/* * ******* */

/* * ***Quickbooks Integration**** */

/** @const set to true if system is integrated w/ Quickbooks */
define('QB_INTEGRATED', false);

define('USES_QB_AST', false); //Set to true for US-based companies (non-franchise systems only!)

define('QB_SCOPE', 'com.intuit.quickbooks.accounting');

if (DEV_MODE) {
    define('QB_CLIENT_ID', 'Q0G0GcNIiAs266OQdcZzD4c8j9I58pYL4PnYhqqlk5Ua6e9bTI');
    define('QB_CLIENT_SECRET', 'zvgIWHBAQtCATrrnfXlvPukCqYwzuQORcBB5zMMp');
    define('QB_LOG_PATH', '/Users/GI_DT_7/Desktop/newFolderForLog');
    define('QB_WEBHOOKS_TOKEN', '');
    define('QB_REDIRECT_URL', 'http://localhost/GI-Framework-V4/index.php?controller=accounting&action=handleQBOAuth2');
    define('QB_PRODUCTION_MODE', false); //Always false for DEV MODE
} else {
    define('QB_CLIENT_ID', 'Q0G0GcNIiAs266OQdcZzD4c8j9I58pYL4PnYhqqlk5Ua6e9bTI'); //GI DEV
    define('QB_CLIENT_SECRET', 'zvgIWHBAQtCATrrnfXlvPukCqYwzuQORcBB5zMMp'); //GI DEV
    define('QB_LOG_PATH', '/');
    define('QB_WEBHOOKS_TOKEN', ''); //TODO - production mode only
    define('QB_REDIRECT_URL', 'http://test.generalinternet.ca/index.php?controller=accounting&action=handleQBOAuth2');
    define('QB_PRODUCTION_MODE', false); //Set to true ONLY when connected to live QB Account
}

define('APCU_TTL', 2419200); //28 Days

define('USE_ADVANCED_SESSION_FUNCTIONS', true);

/* * ****** */

/*****Agreement Definitions*****/
    define('APP_NAME', 'GI Framework Inc.');

    define('LEGAL_NAME', 'General Internet');
/*********/

/***Search Definitions***/
    define('USE_SHADOW_BOX_SEARCH', true);
    define('USE_BASIC_SEARCH', true);
    define('DEFAULT_SEARCH_TYPE', 'basic');
/*********/
    
/***Password Validation***/
    define('PASS_REQ_UPPER', true);
    
    define('PASS_REQ_LOWER', true);
    
    define('PASS_REQ_SYMBOL', true);
    
    define('PASS_REQ_NUM', true);
    
    define('PASS_MIN_LENGTH', 8);
    /* * ****** */

if (DEV_MODE || STAGING_MODE) {
    define('BYPASS_LIVE_OTP', true);
} else {
    define('BYPASS_LIVE_OTP', false);
}

/* * **** Contact Options * */

define('CONTACT_USE_FULLY_QUALIFIED_NAME', false);


