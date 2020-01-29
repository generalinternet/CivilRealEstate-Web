<?php

/**
 * Description of AbstractKeyService
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */

use Aws\SecretsManager\SecretsManagerClient; 

abstract class AbstractKeyService extends GI_Service {
    
    protected static $secretsManager = NULL;

    public static function getSecretsManager() {
        if (!DEV_MODE && empty(static::$secretsManager)) {
            try {
                static::$secretsManager = new SecretsManagerClient([
                    'version' => '2017-10-17',
                    'region' => 'us-west-2',
                    'credentials' => AWSService::getCredentialProvider(),
                ]);
            } catch (Exception $ex) {
                return NULL;
            }
        }
        return static::$secretsManager;
    }
   
    public static function getKey($keyName) {
        if (apcu_exists($keyName)) {
            return apcu_fetch($keyName);
        }
        $secretsArray = static::getSecretArray($keyName);
        if (!empty($secretsArray) && isset($secretsArray[$keyName])) {
            $secret = $secretsArray[$keyName];
        } else {
            $secret = NULL;
        }
        $secret = $secretsArray[$keyName];
        apcu_store($keyName, $secret, ProjectConfig::getApcuTTL());
        return $secret;
    }
    
    public static function storeKey($keyName, $keyValue, $description = NULL) {
        $client = static::getSecretsManager();
        if (empty($client)) {
            return false;
        }
        try {
            $result = $client->createSecret([
                'Description' => $description,
                'Name' => $keyName,
                'SecretString' => $keyValue,
            ]);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    
    public static function getSecretArray($secretName) {
        $client = static::getSecretsManager();
        if (empty($client)) {
            return NULL;
        }
        try {
            $result = $client->getSecretValue([
                'SecretId' => $secretName,
            ]);
        } catch (Exception $e) {
            return NULL;
        }
        // Decrypts secret using the associated KMS CMK.
        // Depending on whether the secret is a string or binary, one of these fields will be populated.
        if (isset($result['SecretString'])) {
            $secretJson = $result['SecretString'];
        } else {
            $secretJson = base64_decode($result['SecretBinary']);
        }
        return GuzzleHttp\json_decode($secretJson, true);
    }

    public static function getDBConfigByAppRef($appRef, $dbType = 'client') {
        if(DEV_MODE){
            return static::getDevDBConfigByAppRef($appRef, $dbType);
        }
        switch ($dbType) {
            case 'rets':
            case 'client':
                $secretName = $appRef . '_' . $dbType . '_db';
            default:
                break;
        }
        
        if (apcu_exists($secretName)) {
            return apcu_fetch($secretName);
        }
        $config = static::getSecretArray($secretName);
        apcu_store($secretName, $config, ProjectConfig::getApcuTTL());
        return $config;
    }
    
    public static function getDevDBConfigByAppRef($appRef, $dbType = 'client'){
        switch($dbType){
            case 'rets':
                $hostAndPort = RETS_DB_HOST;
                $username = RETS_DB_USER;
                $password = RETS_DB_PASS;
                $dbName = RETS_DB_NAME;
                break;
            default:
                $hostAndPort = DEFAULT_DB_HOST;
                $username = DEFAULT_DB_USER;
                $password = DEFAULT_DB_PASS;
                if(defined($appRef . '_DB_NAME')){
                    $dbName = constant($appRef . '_DB_NAME');
                } else {
                    $dbName = DEFAULT_DB_NAME;
                }
                break;
        }
        $host = substr($hostAndPort, 0, strpos($hostAndPort, ':'));
        $port = substr($hostAndPort, strpos($hostAndPort, ':') + 1);
        
        $devDBConfig = array(
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'dbname' => $dbName
        );
        return $devDBConfig;
    }

}