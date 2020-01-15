<?php

use Aws\S3\S3Client;

abstract class GI_S3Connection {

    protected static $instance = NULL;

    protected function __construct() {
        
    }

    protected function __clone() {
        
    }

    public static function getInstance() {
        if (!isset(static::$instance)) {
            if (DEV_MODE) {
                $key = ProjectConfig::getAWSKey();
                $secret = ProjectConfig::getAWSSecret();
                if (empty($key) || empty ($secret)) {
                    return NULL;
                }
                $credentials = array(
                    'key' => $key,
                    'secret' => $secret
                );
            } else {
                $credentials = AWSService::getCredentialProvider();
            }
            static::$instance = $s3Client = S3Client::factory(array(
                        'credentials' => $credentials,
                        'region' => ProjectConfig::getAWSRegion(),
                        'version' => 'latest',
                        'scheme' => ProjectConfig::getHTMLProtocol()
            ));
        }
        return static::$instance;
    }

}
