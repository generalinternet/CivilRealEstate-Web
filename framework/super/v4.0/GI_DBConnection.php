<?php
/**
 * Description of GI_DBConnection
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class GI_DBConnection {

    /**
     * @var mysqli[] 
     */
    protected static $instances = array(
        'client' => NULL,
        'rets' => NULL,
        'master' => NULL,
    );
    
    protected static $verifiedModules = array();

    protected function __construct() {
        
    }

    protected function __clone() {
        
    }

    public static function getInstance($type = 'client') {
        if(!isset(static::$instances[$type]) || is_null(static::$instances[$type])){
            static::$instances[$type] = new mysqli(dbConfig::getDbHostURI($type), dbConfig::getDbUsername($type), dbConfig::getDbPassword($type), dbConfig::getDbName($type));
            static::$instances[$type]->set_charset('utf8');
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        }
        if(dbConfig::getDbName($type) && !static::$instances[$type]->ping()){
            trigger_error('Could not connect to database.');
        }
        return static::$instances[$type];
    }

    public static function verifyTableExists($tableName, $dbType = 'client') {
        $dbConnection = static::getInstance($dbType);
        if(!$dbConnection){
            return false;
        }
        $verifyTableSQL = 'SHOW TABLES LIKE "' . dbConfig::getDbPrefix() . $tableName . '"';
        try {
            $req = $dbConnection->query($verifyTableSQL);
            $result = $req->fetch_all(MYSQLI_ASSOC);
            if (!$result || count($result) !== 1) {
                return false;
            }
            return true;
        } catch (Exception $ex) {
//            trigger_error(GI_StringUtils::formatException($ex));
            return false;
        }
    }
    
    public static function verifyColumnExists($tableName, $columnName, $dbType = 'client') {
        $dbConnection = static::getInstance($dbType);
        $verifyColumnSQL = 'SHOW COLUMNS FROM ' . dbConfig::getDbPrefix() . $tableName . ' LIKE "' . $columnName . '"';
        try {
            $req = $dbConnection->query($verifyColumnSQL);
            $result = $req->fetch_all(MYSQLI_ASSOC);
            if (!$result || count($result) !== 1) {
                return false;
            }
            return true;
        } catch (Exception $ex) {
//            trigger_error(GI_StringUtils::formatException($ex));
            return false;
        }
    }
    
    /**@todo update this method of verifying installed modules to an API call to the master DB**/
    protected static $modules = array(
        'contact' => array(
            'tables' => array(
                'contact',
                'contact_type'
            )
        ),
        'accounting' => array(
            'tables' => array(
                'expense',
                'income'
            )
        ),
        'invoice' => array(
            'tables' => array(
                'invoice'
            )
        ),
        'billing' => array(
            'tables' => array(
                'bill'
            )
        ),
        'inventory' => array(
            'tables' => array(
                'inv_item'
            )
        ),
        'content' => array(
            'tables' => array(
                'content'
            )
        ),
        'timesheet' => array(
            'tables' => array(
                'timesheet'
            )
        ),
        'mls' => array(
            'tables' => array(
                'mls_listing'
            )
        ),
        'order' => array(
            'tables' => array(
                'order'
            )
        ),
        'project' => array(
            'tables' => array(
                'project'
            )
        ),
        'forms' => array(
            'tables' => array(
                'form', //not sufficient for old systems (core used to have form table)
                'form_element'
            )
        ),
        'blog' => array(
            'tables' => array(
                'content_page_post'
            )
        ),
        'qna' => array(
            'tables' => array(
                'qna_question'
            )
        ),
        'realEstate' => array(
            'tables' => array(
                're_listing'
            )
        ),
        'chat' => array(
            'tables' => array(
                'chat_user'
            )
        )
    );
    
    public static function isModuleInstalled($module, $dbType = 'client'){
        $dbName = dbConfig::getDbName();
        if (empty($dbName) || empty($module)) {
            return false;
        }
        $key = 'vm_' . $dbType . '_' . $module;
        if (DEV_MODE) {
            $key = ProjectConfig::getSiteBase() . '_' . $key;
        }
        if (apcu_exists($key)) {
            $value = apcu_fetch($key);
            if ($value == 1) {
                return true;
            } else if ($value == 0) {
                return false;
            }
        }
        if (in_array($module, self::$verifiedModules)) {
            return true;
        }
        if(isset(static::$modules[$module]['tables'])){
            $checkTables = static::$modules[$module]['tables'];
            $verified = true;
            foreach($checkTables as $tableName){
                if(!static::verifyTableExists($tableName, $dbType)){
                    $verified = false;
                }
            }
            if ($verified) {
                self::$verifiedModules[] = $module;
                apcu_store($key, '1', APCU_TTL);
            } else {
                apcu_store($key, '0', APCU_TTL);
            }
            return $verified;
        } else {
            trigger_error('Cannot verify if the [' . $module . '] is installed.');
        }
        return false;
    }

}
