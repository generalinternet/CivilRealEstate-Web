<?php
/**
 * Description of GI_ProjectConfig
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class GI_ProjectConfig {

    protected function __construct() {
        
    }

    protected function __clone() {
        
    }
    
    public static function __callStatic($name, $arguments) {
        if(Permission::verifyByRef('super_admin')){
            trigger_error('GI_ProjectConfig method [' . $name . '] does not exist in framework ' . FRMWK_VERSION . '.');
        }
        return NULL;
    }
    
    public static function getSessionName(){
        return SESSION_NAME;
    }
    
    public static function getSiteTitle() {
        return SITE_TITLE;
    }
    
    public static function getHTMLProtocol(){
        if(DEV_MODE){
            return 'http';
        }
        return HTML_PROTOCOL;
    }

    public static function getSiteBase() {
        return SITE_BASE;
    }

    public static function getProjectBase() {
        return PROJECT_BASE;
    }
    
    /** @return boolean */
    public static function cleanURLs() {
        return CLEAN_URLS;
    }
    
    public static function reverseSiteTitle(){
        return REVERSE_SITE_TITLE;
    }
    
    /** @return boolean */
    public static function storeModels(){
        return STORE_MODELS;
    }
    
    public static function getAWSBucket(){
        return AWS_BUCKET;
    }
    
    public static function getAWSURL(){
        /* NEW URL
        return 'https://' . self::getAWSBucket() . '.s3-' . self::getAWSRegion() . '.amazonaws.com/';
         */
        return static::getHTMLProtocol() . '://' . self::getAWSBucket() . '.s3.amazonaws.com/';
    }
    
    public static function getAWSRegion(){
        return AWS_REGION;
    }

    public static function getAWSKey() {
        if (DEV_MODE) {
            if (defined('AWS_KEY')) {
                return AWS_KEY;
            }
            return NULL;
        } else {
            return KeyService::getKey('app_aws_key');
        }
    }

    public static function getAWSSecret(){
        if (DEV_MODE) {
            if (defined('AWS_SECRET')) {
                return AWS_SECRET;
            }
            return NULL;
        } else {
            return KeyService::getKey('app_aws_secret');
        }
    }
    
    public static function getAWSAccountId() {
        if (defined('AWS_ACCOUNT_ID')) {
            return AWS_ACCOUNT_ID;
        }
        return NULL;
    }
    
    public static function getAWSSQSQueueName() {
        if (defined('SQS_QUEUE_NAME')) {
            return SQS_QUEUE_NAME;
        }
        return NULL;
    }
    
    public static function getAWSSQSQueueURL() {
        if (defined('SQS_QUEUE_URL')) {
            return SQS_QUEUE_URL;
        }
        return NULL;
    }
    
    public static function getServerEmailAddr(){
        return SERVER_EMAIL_ADDR;
    }
    
    public static function getServerEmailName(){
        return SERVER_EMAIL_NAME;
    }

    /** @return boolean */
    public static function openSocket(){
        return OPEN_SOCKET;
    }
    
    public static function getLocalhostIP(){
        if(defined('LOCALHOST_IP')){
            return LOCALHOST_IP;
        }
        return '192.168.0.28';
    }

    public static function getSocketServerURLWithPort() {
        if(DEV_MODE){
            return '//' . static::getLocalhostIP() . ':8001';
        }
        $httpProtocol = ProjectConfig::getHTMLProtocol();
        if (!empty($httpProtocol && $httpProtocol === 'https')) {
            return 'https://' . SOCKET_SERVER_URL . ':' . SOCKET_SERVER_PORT;
        }
        return '//' . SOCKET_SERVER_URL . ':' . SOCKET_SERVER_PORT;
    }

    /** @return boolean */
    public static function enableBrowserNotifications(){
        return ENABLE_BROWSER_NOTIFICATIONS;
    }
    
    /** @return boolean */
    public static function fileUploads(){
        return FILE_UPLOADS;
    }
    
    /** @return boolean */
    public static function useBootstrap(){
        return USE_BOOTSTRAP;
    }
    
    public static function getBootstrapVersion(){
        if(defined('BOOTSTRAP_VERSION')){
            return BOOTSTRAP_VERSION;
        }
        return '3.3.7';
    }
    
    /** @return boolean */
    public static function useFontAwesome(){
        return USE_FONT_AWESOME;
    }
    
    /** @return boolean */
    public static function useColourPicker(){
        return USE_COLOUR_PICKER;
    }
    
    /** @return boolean */
    public static function useWYSIWYG(){
        return USE_WYSIWYG;
    }
    
    
    /** @return boolean */
    public static function useTabs(){
        return USE_GI_TABS;
    }
    
    
    /** @return boolean */
    public static function useWizard(){
        return USE_GI_WIZARD;
    }
    
    /** @return boolean */
    public static function useFullscreenToggle(){
        return USE_FULLSCREEN_TOGGLE;
    }
    
    /** @return boolean */
    public static function poweredByGI(){
        return POWERED_BY_GI;
    }
    
    /** @return string */
    public static function getPoweredByGI(){
        if(static::poweredByGI()){
            return '<a href="' . static::getHTMLProtocol() . '://generalinternet.ca" target="_blank" class="powered_by_gi" title="Managed by General Internet &mdash; Powered by the Business Operating System&trade;"></a>';
        }
    }
    
    /** @return integer */
    public static function getUITableItemsPerPage(){
        $itemsPerPage = (int) filter_input(INPUT_COOKIE, 'ui_table_items_per_page');
        if(!empty($itemsPerPage) && $itemsPerPage > 0){
            return $itemsPerPage;
        }
        return UITABLE_ITEMS_PER_PAGE;
    }
    
    public static function showResultsPerPagePicker(){
        if(defined('SHOW_RESULTS_PER_PAGE_PICKER')){
            return SHOW_RESULTS_PER_PAGE_PICKER;
        }
        return false;
    }
    
    /** @return integer */
    public static function getAutocompleteItemLimit(){
        return AUTOCOMPLETE_ITEM_LIMIT;
    }
    
    /** @return string */
    public static function getMandrillHost(){
        return MANDRILL_HOST;
    }
    
    /** @return integer */
    public static function getMandrillPort(){
        return MANDRILL_PORT;
    }
    
    /** @return string */
    public static function getMandrillUserName(){
        return MANDRILL_SMTP_USERNAME;
    }
    
    /** @return string */
    public static function getMandrillAPIKey() {
        if (DEV_MODE) {
            if (defined('MANDRILL_API_KEY')) {
                return MANDRILL_API_KEY;
            }
            return NULL;
        } else {
            return KeyService::getKey('mandrill_api_key');
        }
    }
    
    /** @return string */
    public static function getMandrillSubaccount(){
        if(defined('MANDRILL_SUBACCOUNT')){
            return MANDRILL_SUBACCOUNT;
        }
        return NULL;
    }
    
    /** @return string */
    public static function getMandrillDefaultTag(){
        if(defined('MANDRILL_DEFAULT_TAG')){
            return MANDRILL_DEFAULT_TAG;
        }
        return NULL;
    }
    
    public static function isMandrillEnabled(){
        if(defined('MANDRILL_ENABLED')){
            return MANDRILL_ENABLED;
        }
        return false;
    }
    
    public static function isTwilioEnabled(){
        if(defined('TWILIO_ENABLED')){
            return TWILIO_ENABLED;
        }
        return false;
    }

    /** @return string */
    public static function getDefaultCurrencyRef() {
        if (!static::getIsFranchisedSystem()) {
            return static::getPrimaryOrganizationDefaultCurrencyRef();
        } else {
            $franchise = Login::getCurrentFranchise();
            if (empty($franchise)) {
                return static::getPrimaryOrganizationDefaultCurrencyRef();
            }
            $currency = $franchise->getDefaultCurrency();
            if (empty($currency)) {
                return static::getPrimaryOrganizationDefaultCurrencyRef();
            }
            return $currency->getProperty('ref');
        }
    }

    /** @return string */
    public static function getDefaultPricingRegionRef(){
        return DEFAULT_PRICING_REGION_REF;
    }
   
    /** @return integer */
    public static function getDefaultCurrencyId() {
        if (!static::getIsFranchisedSystem()) {
            return static::getPrimaryOrganizationDefaultCurrencyId();
        } else {
            $franchise = Login::getCurrentFranchise();
            if (empty($franchise)) {
                return static::getPrimaryOrganizationDefaultCurrencyId();
            }
            return $franchise->getProperty('default_currency_id');
        }
    }

    protected static function getPrimaryOrganizationDefaultCurrencyId() {
        $defaultCurrencyRef = static::getPrimaryOrganizationDefaultCurrencyRef();
        if (!empty($defaultCurrencyRef)) {
            $defaultCurrency = CurrencyFactory::getModelByRef($defaultCurrencyRef);
            if (!empty($defaultCurrency)) {
                return $defaultCurrency->getId();
            }
        }
        return NULL;
    }

    protected static function getPrimaryOrganizationDefaultCurrencyRef() {
        return DEFAULT_CURRENCY_REF;
    }

    /** @return string */
    public static function getDefaultFOBShippingTypeRef() {
        return DEFAULT_FOB_SHIPPING_TYPE_REF;
    }

    /** @return integer */
    public static function getDefaultFOBShippingTypeId() {
        $fobShippingTypeRef = static::getDefaultFOBShippingTypeRef();
        if (!empty($fobShippingTypeRef)) {
            $fobShippingType = FOBShippingTypeFactory::getModelByRef($fobShippingTypeRef);
            if (!empty($fobShippingType)) {
                return $fobShippingType->getId();
            }
        }
        return NULL;
    }
    
    /** @return string */
    public static function FOBDeterminesTaxCollection(){
        return FOB_DETERMINES_TAX_COLLECTION;
    }
    
    /** @return string */
    public static function getDefaultTaxCollectionTypeRef(){
        return DEFAULT_TAX_COLLECTION_TYPE_REF;
    }
    
    /** @return integer */
    public static function getDefaultTaxCollectionTypeId() {
        $taxCollectionTypeRef = static::getDefaultTaxCollectionTypeRef();
        if (!empty($taxCollectionTypeRef)) {
            $taxCollectionType = TaxCollectionTypeFactory::getModelByRef($taxCollectionTypeRef);
            if (!empty($taxCollectionType)) {
                return $taxCollectionType->getId();
            }
        }
        return NULL;
    }
    
    public static function getHasMultipleCurrencies() {
        if (defined('MULTI_CURRENCY')) {
            return MULTI_CURRENCY;
        }
        return true;
    }
    
    public static function getDefaultRegionCode() {
        return DEFAULT_REGION;
    }
    
    public static function getDefaultCountryCode() {
        if (ProjectConfig::getIsFranchisedSystem()) {
            $currentFranchise = Login::getCurrentFranchise();
            if (!empty($currentFranchise)) {
                $addresses = $currentFranchise->getContactInfoAddresses('billing_address');
                if (!empty($addresses)) {
                    $address = $addresses[0];
                    if (!empty($address)) {
                        $countryCode = $address->getProperty('contact_info_address.addr_country');
                        if (!empty($countryCode)) {
                            return $countryCode;
                        }
                    }
                }
            }
        }
        return DEFAULT_COUNTRY;
    }
    
    public static function bypassEmailConfirmation(){
        return BYPASS_EMAIL_CONFIMATION;
    }
    
    public static function getDefaultTaxRefs(){
        return unserialize(DEFAULT_TAX_REFS);
    }
    
    public static function getTwilioSID(){
        if (DEV_MODE) {
            if (defined('TWILIO_SID')) {
                return TWILIO_SID;
            }
            return NULL;
        } else {
            return KeyService::getKey('twilio_sid');
        }
    }
    
    public static function getTwilioAuthToken(){
        if (DEV_MODE) {
            if (defined('TWILIO_AUTH_TOKEN')) {
                return TWILIO_AUTH_TOKEN;
            }
            return NULL;
        } else {
            return KeyService::getKey('twilio_auth_token');
        }
    }
    
    public static function getTwilioTestSID(){
        if (defined('TWILIO_TEST_SID')) {
            return TWILIO_TEST_SID;
        }
        return NULL;
    }
    
    public static function getTwilioTestAuthToken(){
        if (defined('TWILIO_TEST_AUTH_TOKEN')) {
            return TWILIO_TEST_AUTH_TOKEN;
        }
        return NULL;
    }
    
    public static function getTwilioPhoneNumber(){
        if (defined('TWILIO_PHONE_NUMBER')) {
            return TWILIO_PHONE_NUMBER;
        }
        return NULL;
    }
    
    public static function getTwilioAPIKey(){
        if (defined('TWILIO_API_KEY')) {
            return TWILIO_API_KEY;
        }
        return NULL;
    }
    
    public static function getTwilioSecret(){
        if (defined('TWILIO_SECRET')) {
            return TWILIO_SECRET;
        }
        return NULL;
    }
    
    public static function useSyntaxHighlighter(){
        return USE_SYNTAXHIGHLIGHTER;
    }
    
    public static function getSyntaxHighlighterStyle(){
        $style = SYNTAXHIGHLIGHTER_STYLE;
        if (empty($style)) {
            $style = 'default';
        }
        return $style;
    }

    /**
     * 
     * @return \DateTime
     */
    public static function getSystemLiveDate() {
        return new DateTime(SYSTEM_LIVE_DATE . ' 00:00:00');
    }
    
    public static function getFiscalYearStartMonthAndDay() {
        return DEFAULT_FISCAL_YEAR_START;
    }
    
    public static function getMultiAccoutingLocations() {
        return MULTI_ACCOUNTING_LOCATIONS;
    }
    
    public static function getDefaultAccoutingLocationTag() {
        $defaultAccountingLocationTagRef = DEFAULT_ACCOUNTING_LOCATION_REF;
        $tag = TagFactory::getModelByRefAndTypeRef($defaultAccountingLocationTagRef, 'accounting_loc');
        return $tag;
    }
    
    public static function getDefaultEcoFeeCountryCodes() {
        return DEFAULT_ECO_FEE_COUNTRY_CODES;
    }
    
    public static function getDefaultARInvoiceExportDayRangeBreaks() {
        $string = DEFAULT_AR_INVOICE_EXPORT_DAY_RANGE_BREAKS_STRING;
        return explode(',', $string);
    }

    public static function isCommodityBased() {
        return COMMODITY_BASED;
    }

    public static function useStockGroups() {
        return USE_STOCK_GROUPS;
    }

    public static function useBulkItems() {
        return USE_BULK_ITEMS;
    }

    public static function getStockMethod() {
        return STOCK_METHOD;
    }
    
    public static function getStockUnitPrecision(){
        return STOCK_UNIT_PRECISION;
    }
    
    public static function getStockSelectWithin(){
        return STOCK_SELECT_WITHIN;
    }
    
    public static function allowLooseStockConsolidation(){
        return ALLOW_LOOSE_STOCK_CONSOLIDATION;
    }

    public static function getIsMultiCountrySystem() {
        return MULTI_COUNTRY;
    }
    
    public static function getDefaultRoundPrecision(){
        if(defined('DEFAULT_ROUND_PRECISION')){
            return DEFAULT_ROUND_PRECISION;
        }
        return 5;
    }

    public static function getIsFranchisedSystem() {
        if (defined('IS_FRANCHISED_SYSTEM')) {
            return IS_FRANCHISED_SYSTEM;
        }
        return false;
    }
    
    public static function getIsQuickbooksIntegrated() {
        if (defined('QB_INTEGRATED')) {
            return QB_INTEGRATED;
        }
        return NULL;
    }

    public static function getQBClientId() {
        if (defined('QB_CLIENT_ID')) {
            return QB_CLIENT_ID;
        }
        return NULL;
    }

    public static function getQBClientSecret() {
        if (defined('QB_CLIENT_SECRET')) {
            return QB_CLIENT_SECRET;
        }
        return NULL;
    }

    public static function getQBScope() {
        if (defined('QB_SCOPE')) {
            return QB_SCOPE;
        }
        return NULL;
    }

    public static function getQBRedirectURL() {
        if (defined('QB_REDIRECT_URL')) {
            return QB_REDIRECT_URL;
        }
        return NULL;
    }
    

    public static function getQBAccountURL() {
        if (static::isQBInProductionMode()) {
            $url = 'https://qbo.intuit.com/login?deeplinkcompanyid=';
        } else {
            $url = 'https://sandbox.qbo.intuit.com/login?deeplinkcompanyid=';
        }
        $franchiseId = NULL;
        $franchise = Login::getCurrentFranchise();
        if (!empty($franchise)) {
            $franchiseId = $franchise->getId();
        }
        $search = SettingsFactory::search()
                ->filterByTypeRef('qb')
                ->ignoreFranchise('settings');
        if (!empty($franchiseId)) {
            $search->filter('franchise_id', $franchiseId);
        } else if (ProjectConfig::getIsFranchisedSystem ()){
            $search->filterNull('franchise_id');
        }
        $results = $search->select();
        $realmId = NULL;
        if (!empty($results)) {
            $settings = $results[0];
            if (!empty($settings)) {
                $realmId = $settings->getProperty('settings_qb.realm_id');
            }
        }
        if (empty($realmId)) {
            return NULL;
        }
        $url .= $realmId;
        return $url;
    }
    
    public static function getQBWebhooksToken() {
        if (defined('QB_WEBHOOKS_TOKEN')) {
            return QB_WEBHOOKS_TOKEN;
        }
        return NULL;
    }
    
    public static function getQBLogPath() {
        if (defined('QB_LOG_PATH')) {
            return QB_LOG_PATH;
        }
        return '';
    }
    
    public static function isQBInProductionMode() {
        if (defined('QB_PRODUCTION_MODE')) {
            return QB_PRODUCTION_MODE;
        }
        return false;
    }

    public static function getQBBaseURL() {
        if (!DEV_MODE) {
            if (static::isQBInProductionMode()) {
                return 'https://quickbooks.api.intuit.com';
            }
        }
        return 'Development';
    }

    public static function useContactCatIndex() {
        if (defined('USE_CONTACT_CAT_INDEX')) {
            return USE_CONTACT_CAT_INDEX;
        }
        return false;
    }
    
    public static function getAppName(){
        if(defined('APP_NAME')){
            return APP_NAME;
        }
        return '[NO APP NAME DEFINED]';
    }

    public static function getLegalName(){
        if(defined('LEGAL_NAME')){
            return LEGAL_NAME;
        }
        return '[NO LEGAL NAME DEFINED]';
    }
    
    public static function getDefaultTIStartTime(){
        if(defined('DEFAULT_TI_START_TIME')){
            return DEFAULT_TI_START_TIME;
        }
        return '9:30';
    }
    
    public static function getDefaultTIEndTime(){
        if(defined('DEFAULT_TI_END_TIME')){
            return DEFAULT_TI_END_TIME;
        }
        return '17:30';
    }
    
    public static function getApcuTTL() {
        if (defined('APCU_TTL')) {
            return APCU_TTL;
        }
        return 3600;
    }
    
    public static function useShadowBoxSearch(){
        if (defined('USE_SHADOW_BOX_SEARCH')) {
            return USE_SHADOW_BOX_SEARCH;
        }
        return true;
    }
    
    public static function useBasicSearch(){
        if (defined('USE_BASIC_SEARCH')) {
            return USE_BASIC_SEARCH;
        }
        return true;
    }
    
    public static function getDefaultSearchType(){
        if (defined('DEFAULT_SEARCH_TYPE')) {
            return DEFAULT_SEARCH_TYPE;
        }
        return 'basic';
    }
    
    public static function getPassReqUpper(){
        if (defined('PASS_REQ_UPPER')) {
            return PASS_REQ_UPPER;
        }
        return false;
    }
    
    public static function getPassReqLower(){
        if (defined('PASS_REQ_LOWER')) {
            return PASS_REQ_LOWER;
        }
        return false;
    }
    
    public static function getPassReqSymbol(){
        if (defined('PASS_REQ_SYMBOL')) {
            return PASS_REQ_SYMBOL;
        }
        return false;
    }
    
    public static function getPassReqNum(){
        if (defined('PASS_REQ_NUM')) {
            return PASS_REQ_NUM;
        }
        return false;
    }
    
    public static function getPassMinLength(){
        if (defined('PASS_MIN_LENGTH')) {
            return PASS_MIN_LENGTH;
        }
        return 1;
    }
    
    public static function getPrimaryInternalOrgUsesQBAst() {
        if (defined('USES_QB_AST')) {
            return USES_QB_AST;
        }
        return false;
    }
    
    public static function getContactUseFullyQualifiedName() {
        if (defined('CONTACT_USE_FULLY_QUALIFIED_NAME')) {
            return CONTACT_USE_FULLY_QUALIFIED_NAME;
        }
        return false;
    }
    
    public static function publicViewActive(){
        if(defined('PUBLIC_VIEW_ACTIVE')){
            return PUBLIC_VIEW_ACTIVE;
        }
        return false;
    }
    

//    public static function getDefaultController() {
//        $interfacePerspectiveRef = Login::getCurrentInterfacePerspectiveRef();
//        if(static::publicViewActive() && (!empty($interfacePerspectiveRef) && $interfacePerspectiveRef !== 'admin')) {
//            return DEFAULT_PUBLIC_CONTROLLER;
//        } else {
//            return DEFAULT_CONTROLLER;
//        }
//    }
//    
//    public static function getDefaultAction() {
//        $interfacePerspectiveRef = Login::getCurrentInterfacePerspectiveRef();
//        if (static::publicViewActive() && (!empty($interfacePerspectiveRef) && $interfacePerspectiveRef !== 'admin')) {
//            return DEFAULT_PUBLIC_ACTION;
//        } else {
//            return DEFAULT_ACTION;
//        }
//    }

    public static function getDefaultController() {
        $interfacePerspectiveRef = Login::getCurrentInterfacePerspectiveRef();
        if (static::publicViewActive() || (!empty($interfacePerspectiveRef) && $interfacePerspectiveRef !== 'admin')) {
            return DEFAULT_PUBLIC_CONTROLLER;
        } else {
            return DEFAULT_CONTROLLER;
        }
    }

    public static function getDefaultAction() {
        $interfacePerspectiveRef = Login::getCurrentInterfacePerspectiveRef();
        if (static::publicViewActive() || (!empty($interfacePerspectiveRef) && $interfacePerspectiveRef !== 'admin')) {
            return DEFAULT_PUBLIC_ACTION;
        } else {
            return DEFAULT_ACTION;
        }
    }

    public static function getIsWorkerServer() {
        if (defined('IS_WORKER_SERVER')) {
            return IS_WORKER_SERVER;
        }
        return false;
    }
    
    public static function registerRequiresConfirmation(){
        if(defined('REGISTER_REQUIRES_CONFIRMATION')){
            return REGISTER_REQUIRES_CONFIRMATION;
        }
        return true;
    }
    
    public static function registerRequiresCodeConfirmation(){
        if(defined('REGISTER_REQUIRES_CODE_CONFIRMATION')){
            return REGISTER_REQUIRES_CODE_CONFIRMATION;
        }
        return true;
    }
    
    public static function isRegistrationEnabled(){
        if(defined('REGISTRATION_ENABLED')){
            return REGISTRATION_ENABLED;
        }
        return false;
    }
    
    public static function isChatEnabled(){
        if(defined('CHAT_ENABLED')){
            return CHAT_ENABLED;
        }
        return false;
    }

    public static function getEncryptionKey() {
        openssl_digest(php_uname(), 'SHA256', TRUE);
        $key = NULL;
        if (DEV_MODE) {
            if (defined('ENCRYPTION_KEY')) {
                $key = ENCRYPTION_KEY;
            }
        } else {
            $key = KeyService::getKey('app_encryption_key');
        }
        if (empty($key)) {
            $key = openssl_digest(php_uname(), 'SHA256', TRUE);
        }
        return $key;
    }

    public static function getEncryptionCipherMethod() {
        return 'aes-256-ctr';
    }
    
    public static function getUseAdvancedSessionFunctions() {
        if (defined('USE_ADVANCED_SESSION_FUNCTIONS')) {
            return USE_ADVANCED_SESSION_FUNCTIONS;
        }
        return false;
    }
    
    public static function getAppRef(){
//        SessionService::setValue('CUR_APP_REF', 'sub-app');
        $curAppRef = SessionService::getValue('CUR_APP_REF');
        if(!empty($curAppRef)){
            return $curAppRef;
        }
        if (defined('APP_REF')){
            return APP_REF;
        }
        return NULL;
    }

    public static function getStripeSecretKey() {
        if (DEV_MODE || STAGING_MODE) {
            if (defined('STRIPE_SECRET_KEY')) {
                return STRIPE_SECRET_KEY;
            }
            return NULL;
        } else {
            return KeyService::getKey('stripe_secret_key');
        }
    }

    public static function getStripePublishableKey() {
        if (DEV_MODE || STAGING_MODE) {
            if (defined('STRIPE_PUBLISHABLE_KEY')) {
                return STRIPE_PUBLISHABLE_KEY;
            }
            return NULL;
        } else {
            return KeyService::getKey('stripe_publishable_key');
        }
    }
    
    public static function getGoogleAPIKey(){
        if (defined('GOOGLE_API_KEY')) {
            return GOOGLE_API_KEY;
        }
        if (DEV_MODE) {
            return NULL;
        } else {
            return KeyService::getKey('google_api_key');
        }
    }
    
    public static function getReCaptchaKey(){
        if (defined('RE_CAPTCHA_KEY')) {
            return RE_CAPTCHA_KEY;
        }
        if (DEV_MODE) {
            return NULL;
        } else {
            return KeyService::getKey('re_captcha_key');
        }
    }
    
    public static function getReCaptchaSecret(){
        if (defined('RE_CAPTCHA_SECRET')) {
            return RE_CAPTCHA_SECRET;
        }
        if (DEV_MODE) {
            return NULL;
        } else {
            return KeyService::getKey('re_captcha_secret');
        }
    }
    
    public static function getSupportEmail() {
        if (defined('SUPPORT_EMAIL')) {
            return SUPPORT_EMAIL;
        }
        if (defined('ROOT_USER_EMAIL')) {
            return ROOT_USER_EMAIL;
        }
        return NULL;
    }

    public static function getBypassLiveOTP() {
        if (defined('BYPASS_LIVE_OTP')) {
            return BYPASS_LIVE_OTP;
        }
        return false;
    }

}
