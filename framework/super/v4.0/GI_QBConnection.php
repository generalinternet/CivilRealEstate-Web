<?php

use QuickBooksOnline\API\DataService\DataService;

/**
 * Description of GI_QBConnection
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    2.2.4
 */
abstract class GI_QBConnection {

    protected static $instances = array();
    protected static $qbAccountsByName = array();
    protected static $franchiseId = NULL;
    protected static $qbSettingsModelsByFranchiseId = array();

    protected function __construct() {

    }

    protected function __clone() {

    }

    /**
     * @deprecated
     */
    public static function removeInstance(){
        
    }

    /**
     * 
     * @return QuickBooksOnline\API\DataService\DataService
     */
    public static function getInstance() {
        $franchiseId = static::getFranchiseId();
        if (!isset(static::$instances[$franchiseId])) {
            $tokenKeys = array();
            if (apcu_exists('qb_token_keys_' . $franchiseId)) {
                $tokenKeys = apcu_fetch('qb_token_keys_' . $franchiseId);
            } else {
                $qbSettings = static::getQBSettings($franchiseId);
                if (!empty($qbSettings)) {
                    $tokenKeyFromDB = $qbSettings->getProperty('settings_qb.token_key');
                    $refreshTokenKeyFromDB = $qbSettings->getProperty('settings_qb.refresh_token_key');
                    $realmIdFromDB = $qbSettings->getProperty('settings_qb.realm_id');
                    if (!empty($tokenKeyFromDB) && !empty($refreshTokenKeyFromDB) && !empty($realmIdFromDB)) {
                        $tokenKeys['token_key'] = $tokenKeyFromDB;
                        $tokenKeys['refresh_token_key'] = $refreshTokenKeyFromDB;
                        $tokenKeys['realm_id'] = $realmIdFromDB;
                    }
                }
            }
            if (empty($tokenKeys)) {
                return NULL;
            }
            $clientId = ProjectConfig::getQBClientId();
            $clientSecret = ProjectConfig::getQBClientSecret();
            $tokenKey = $tokenKeys['token_key'];
            $refreshTokenKey = $tokenKeys['refresh_token_key'];
            $qbRealmId = $tokenKeys['realm_id'];
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $clientId,
                        'ClientSecret' => $clientSecret,
                        'accessTokenKey' => $tokenKey,
                        'refreshTokenKey' => $refreshTokenKey,
                        'QBORealmID' => $qbRealmId,
                        'baseUrl' => ProjectConfig::getQBBaseURL(),
            ));

            $dataService->setLogLocation(ProjectConfig::getQBLogPath());
            $dataService->throwExceptionOnError(false);
        } else {
            $dataService = static::$instances[$franchiseId];
        }
        $loginHelper = $dataService->getOAuth2LoginHelper();
        $token = $loginHelper->getAccessToken();

        $expiresAtDateTime = NULL;
        $refreshRequired = false;
        try {
            $expiresAtDateTime = new DateTime($token->getAccessTokenExpiresAt());
            $token->getRefreshTokenExpiresAt();
        } catch (Exception $ex) {
            $refreshRequired = true;
        }
        $currentDateTime = new DateTime(Date('Y-m-d H:i:s'));
        if (!empty($expiresAtDateTime)) {
            $currentDateTime = new DateTime(Date('Y-m-d H:i:s'));
            if ($currentDateTime >= $expiresAtDateTime) {
                $refreshRequired = true;
            }
        }
        if ($refreshRequired) {
            $dataService = static::refreshTokens($dataService);
        }
        if(!$dataService){
            return NULL;
        }
        $dataService->throwExceptionOnError(false);
        
        static::$instances[$franchiseId] = $dataService;

        return static::$instances[$franchiseId];
    }

    protected static function refreshTokens($dataService) {
        $tokenKeys = array();
        $franchiseId = static::getFranchiseId();
        $qbSettings = static::getQBSettings($franchiseId);
        $loginHelper = $dataService->getOAuth2LoginHelper();
        try {
            $newToken = $loginHelper->refreshToken();
            if (empty($newToken)) {
                return NULL;
            }
        } catch (Exception $ex) {
            if (!empty($qbSettings)) {
                $qbSettings->setProperty('settings_qb.token_key', NULL);
                $qbSettings->setProperty('settings_qb.refresh_token_key', NULL);
                $qbSettings->save();
            }
            return NULL;
        }
        $dataService->updateOAuth2Token($newToken);

        $tokenKeys['token_key'] = $newToken->getAccessToken();
        $tokenKeys['refresh_token_key'] = $newToken->getRefreshToken();
        $tokenKeys['realm_id'] = $newToken->getRealmID();
        apcu_store('qb_token_keys_' . $franchiseId, $tokenKeys);
        if (!empty($qbSettings)) {
            $qbSettings->setProperty('settings_qb.token_key', $newToken->getAccessToken());
            $qbSettings->setProperty('settings_qb.refresh_token_key', $newToken->getRefreshToken());
            $qbSettings->setProperty('settings_qb.realm_id', $newToken->getRealmID());
            $qbSettings->save();
        }
        return $dataService;
    }

    protected static function getQBSettings($franchiseId) {
        if (isset(static::$qbSettingsModelsByFranchiseId[$franchiseId])) {
            return static::$qbSettingsModelsByFranchiseId[$franchiseId];
        }
        $settingsSearch = SettingsFactory::search();
        $settingsSearch->filterByTypeRef('qb')
                ->filter('ref', 'quickbooks')
                ->ignoreFranchise('settings');
        if (!empty($franchiseId)) {
            $settingsSearch->filter('franchise_id', $franchiseId);
        } else if (ProjectConfig::getIsFranchisedSystem()){
            $settingsSearch->filterNull('franchise_id');
        }
        $settingsArray = $settingsSearch->select();
        if (!empty($settingsArray)) {
            $settings = $settingsArray[0];
            static::$qbSettingsModelsByFranchiseId[$franchiseId] = $settings;
            return $settings;
        }
        return NULL;
    }

    public static function getRealmId() {
        $franchiseId = static::getFranchiseId();
        if (apcu_exists('qb_token_keys_' . $franchiseId)) {
            $tokenKeys = apcu_fetch('qb_token_keys_' . $franchiseId);
            if (!empty($tokenKeys) && isset($tokenKeys['realm_id'])) {
                return $tokenKeys['realm_id'];
            }
        }
        return NULL;
    }

    public static function getFranchiseId() {
        if (!ProjectConfig::getIsFranchisedSystem()) {
            return 0;
        }
        if (is_null(static::$franchiseId)) {
            $franchise = Login::getCurrentFranchise();
            if (!empty($franchise)) {
                static::$franchiseId = $franchise->getId();
            } else {
                static::$franchiseId = 0;
            }
        }
        return static::$franchiseId;
    }

    public static function addFranchiseFilterToDataSearch(GI_DataSearch $dataSearch) {
        if (ProjectConfig::getIsFranchisedSystem()) {
            $franchiseId = static::getFranchiseId();
            if (!empty($franchiseId)) {
                $dataSearch->filter('franchise_id', $franchiseId);
            } else {
                $dataSearch->filterNull('franchise_id');
            }
        }
    }

    public static function getConnectToQBURL() {
        if (Permission::verifyByRef('connect_to_quickbooks')) {
            $state = 'none';
            $franchiseId = static::getFranchiseId();
            if (!empty($franchiseId)) {
                $state = $franchiseId;
            }
            $loginHelper = new \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper(ProjectConfig::getQBClientId(), ProjectConfig::getQBClientSecret(), ProjectConfig::getQBRedirectURL(), ProjectConfig::getQBScope(), $state);
            $authCodeURL = $loginHelper->getAuthorizationCodeURL();
            if (!empty($authCodeURL)) {
                return $authCodeURL;
            }
        }
        return NULL;
    }

    public static function isConnectionValid() {
        $instance = static::getInstance();
        if (!empty($instance)) {
            return true;
        }
        return false;
    }

    public static function getConnectToQuickbooksButton() {
        $html = '';
        if (!ProjectConfig::getIsQuickbooksIntegrated() || !Permission::verifyByRef('connect_to_quickbooks')) {
            return $html;
        }
        if (!QBConnection::isConnectionValid()) {
            $connectToQBURL = static::getConnectToQBURL();
            $url = $connectToQBURL;
            return '<a href="' . $url . '" class="qb_connect_btn qb_btn" title="Connect To Quickbooks Online">Connect</a>';
        } 
        return $html;
    }

    /**
     * @return \AbstractQuickBooksBarView
     */
    public static function getQuickbooksBarView(){
        if (!ProjectConfig::getIsQuickbooksIntegrated()){
            return NULL;
        }
        $bar = new QuickBooksBarView();
        return $bar;
    }

    /**
     * @param string $accountName
     * @return QuickBooksOnline\API\Data\IPPAccount
     */
    //TODO - REVISE THIS - Use QBAccountFactory
    //TODO - this may be unneeded now
    public static function getQuickbooksAccountByName($accountName) {
        $franchiseId = static::getFranchiseId();
        if (isset(static::$qbAccountsByName[$franchiseId][$accountName])) {
            return static::$qbAccountsByName[$franchiseId][$accountName];
        }
        $dataService = static::getInstance();
        if (!empty($dataService)) {
            $query = "Select * from Account where Name='" . $accountName . "'";
            $resultingArray = $dataService->Query($query);
            if (!empty($resultingArray)) {
                $resultingObject = $resultingArray[0];
                if (!isset(static::$qbAccountsByName[$franchiseId])) {
                    static::$qbAccountsByName[$franchiseId] = array();
                 }
                static::$qbAccountsByName[$franchiseId][$accountName] = $resultingObject;
                return $resultingObject;
            }
        }
        return NULL;
    }

    public static function getQBSettingsModel() {
        $franchiseId = static::getFranchiseId();
        if (!isset(static::$qbSettingsModelsByFranchiseId[$franchiseId])) {
            static::getQBSettings($franchiseId);
        }
        if (isset(static::$qbSettingsModelsByFranchiseId[$franchiseId])) {
            return static::$qbSettingsModelsByFranchiseId[$franchiseId];
        }
        return NULL;
    }

}
