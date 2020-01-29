<?php
/**
 * Description of GI_DBConfig
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class GI_DBConfig {

    protected static $itemsPerPage = 25;
    
    protected static $bosToColTypes = array(
        'onoff' => 'TINYINT( 1 )',
        'date' => 'DATE',
        'datetime' => 'DATETIME',
        'decimal' => 'DOUBLE( 20,10 )',
        'id' => 'INT( 11 ) UNSIGNED',
        'integer' => 'INT( 11 )',
        'integer_large' => 'BIGINT( 19 )',
        'money' => 'FLOAT( 10,2 )',
        'sha512' => 'VARCHAR( 128 )',
        'text' => 'VARCHAR( 255 )',
        'textarea' => 'TEXT',
        'time' => 'TIME'
    );
    
    protected static $bosColTypesAndCleanNames = array(
        'onoff' => 'On/Off',
        'date' => 'Date',
        'datetime' => 'Date & Time',
        'decimal' => 'Decimal',
        'id' => 'ID',
        'integer' => 'Integer',
        'integer_large' => 'Large Integer',
        'money' => 'Money',
        'sha512' => 'Password',
        'text' => 'Text',
        'textarea' => 'Textarea',
        'time' => 'Time'
    );
    
    protected static $commonColNames = array(
        'id' => array(
            'type' => 'id',
            'not_null' => 1,
            'indexed' => 1
        ),
        'inception' => array(
            'type' => 'datetime',
            'not_null' => 1,
            'indexed' => 0
        ),
        'status' => array(
            'type' => 'onoff',
            'not_null' => 1,
            'indexed' => 0
        ),
        'uid' => array(
            'type' => 'id',
            'not_null' => 1,
            'indexed' => 0
        ),
        'last_mod' => array(
            'type' => 'datetime',
            'not_null' => 1,
            'indexed' => 0
        ),
        'last_mod_by' => array(
            'type' => 'id',
            'not_null' => 1,
            'indexed' => 0
        )
    );
    
    //These arrays are intended to be input for a dropdown menu - hence the key=>value pairing
    protected static $dbColTypesCompOps = array(
        'TINYINT( 1 )' => array(
            '=' => '=',
            '<>' => '<>'
        ),
        'DATE' => array(
            'BETWEEN' => 'BETWEEN',
            'NOT BETWEEN' => 'NOT BETWEEN',
            '=' => '=',
            '>=' => '>=',
            '>' => '>',
            '<=' => '<=',
            '<' => '<',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL'
        ),
        'DATETIME' => array(
            'BETWEEN' => 'BETWEEN',
            'NOT BETWEEN' => 'NOT BETWEEN',
            '=' => '=',
            '>=' => '>=',
            '>' => '>',
            '<=' => '<=',
            '<' => '<',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL'
        ),
        'DOUBLE( 20,10 )' => array(
            'BETWEEN' => 'BETWEEN',
            'NOT BETWEEN' => 'NOT BETWEEN',
            '=' => '=',
            '>=' => '>=',
            '>' => '>',
            '<=' => '<=',
            '<' => '<',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL'
        ),
        'INT( 11 ) UNSIGNED' => array(
            'BETWEEN' => 'BETWEEN',
            'NOT BETWEEN' => 'NOT BETWEEN',
            '=' => '=',
            '>=' => '>=',
            '>' => '>',
            '<=' => '<=',
            '<' => '<',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL'
        ),
        'INT( 11 )' => array(
            'BETWEEN' => 'BETWEEN',
            'NOT BETWEEN' => 'NOT BETWEEN',
            '=' => '=',
            '>=' => '>=',
            '>' => '>',
            '<=' => '<=',
            '<' => '<',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL'
        ),
        'BIGINT( 19 )' => array(
            'BETWEEN' => 'BETWEEN',
            'NOT BETWEEN' => 'NOT BETWEEN',
            '=' => '=',
            '>=' => '>=',
            '>' => '>',
            '<=' => '<=',
            '<' => '<',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL'
        ),
        'FLOAT( 10,2 )' => array(
            'BETWEEN' => 'BETWEEN',
            'NOT BETWEEN' => 'NOT BETWEEN',
            '=' => '=',
            '>=' => '>=',
            '>' => '>',
            '<=' => '<=',
            '<' => '<',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL'
        ),
        'VARCHAR( 128 )' => array(
            '=' => '=',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL',
            'LIKE' => 'LIKE',
            'NOT LIKE' => 'NOT LIKE',
            'SOUNDS LIKE' => 'SOUNDS LIKE'
        ),
        'VARCHAR( 255 )' => array(
            '=' => '=',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL',
            'LIKE' => 'LIKE',
            'NOT LIKE' => 'NOT LIKE',
            'SOUNDS LIKE' => 'SOUNDS LIKE'
        ),
        'TEXT' => array(
            '=' => '=',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL',
            'LIKE' => 'LIKE',
            'NOT LIKE' => 'NOT LIKE',
            'SOUNDS LIKE' => 'SOUNDS LIKE'
        ),
        'TIME' => array(
            'BETWEEN' => 'BETWEEN',
            'NOT BETWEEN' => 'NOT BETWEEN',
            '=' => '=',
            '>=' => '>=',
            '>' => '>',
            '<=' => '<=',
            '<' => '<',
            '<>' => '<>',
            'IS NULL' => 'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL'
        )
    );
    
    protected static $bosColTypeFormFieldType = array(
        'text' => array(
            'text' => 'Text',
            'email' => 'Email',
            'phone' => 'Phone',
            'url' => 'URL',
            'radio' => 'Radio',
            'dropdown' => 'Dropdown'
        ),
        'textarea' => array(
            'textarea' => 'Textarea',
            'checkbox' => 'Checkbox',
            'select' => 'Select'
        ),
        'integer' => array(
            'integer' => 'Integer'
        ),
        'integer_large' => array(
            'integer_large' => 'Large Integer'
        ),
        'money' => array(
            'money' => 'Money'
        ),
        'decimal' => array(
            'decimal' => 'Decimal'
        ),
        'onoff' => array(
            'onoff' => 'On/Off'
        ),
        'date' => array(
            'date' => 'Date'
        ),
        'time' => array(
            'time' => 'Time'
        ),
        'datetime' => array(
            'datetime' => 'Date & Time'
        ),
        'id' => array(
            'id' => 'ID',
            'autocomplete' => 'Auto Complete'
        ),
        'sha512' => array(
            'sha512' => 'Password'
        )
    );
    
    protected static $formFieldTypeBosColTypes = array(
        'text' => 'text',
        'email' => 'text',
        'phone' => 'text',
        'url' => 'text',
        'radio' => 'text',
        'dropdown' => 'text',
        'textarea' => 'textarea',
        'checkbox' => 'textarea',
        'wysiwyg' => 'textarea',
        'select' => 'textarea',
        'integer' => 'integer',
        'integer_large' => 'integer_large',
        'money' => 'money',
        'decimal' => 'decimal',
        'percentage' => 'decimal',
        'onoff' => 'onoff',
        'date' => 'date',
        'time' => 'time',
        'datetime' => 'datetime',
        'id' => 'id',
        'autocomplete' => 'id',
        'sha512' => 'sha512'
    );
    
    protected static $bosColTypeFormInclusions = array(
        'date' => array(
            'min_date' => array(
                'display_name' => 'Min. Date',
                'type' => 'date'
            ),
            'max_date' => array(
                'display_name' => 'Max. Date',
                'type' => 'date'
            ),
            'default_date' => array(
                'display_name' => 'Default Date',
                'type' => 'date'
            )
        ),
        'datetime' => array(
            'min_date' => array(
                'display_name' => 'Min. Date',
                'type' => 'date'
            ),
            'max_date' => array(
                'display_name' => 'Max. Date',
                'type' => 'date'
            ),
            'default_date' => array(
                'display_name' => 'Default Date',
                'type' => 'date'
            )
        ),
        'autocomplete' => array(
            'autocomp_url' => array(
                'display_name' => 'Autocomplete URL',
                'type' => 'text'
            )
        ),
        'money' => array(
            'currency' => array(
                'display_name' => 'Currency',
                'type' => 'text'
            )
        ),
        'dropdown' => array(
            'hide_null' => array(
                'display_name' => 'Hide NULL Value',
                'type' => 'onoff'
            ),
            'null_text' => array(
                'display_name' => 'NULL Text',
                'type' => 'text'
            )
        ),
        'text' => array(
            'max_length' => array(
                'display_name' => 'Max length',
                'type' => 'integer'
            )
        ),
        'time' => array(
            'step_minute' => array(
                'display_name' => 'Step Minute',
                'type' => 'integer'
            )
        )
    );

    static function getDbHostURI($type = 'client') {
        switch ($type) {
            case 'rets':
                if (defined('RETS_DB_HOST')) {
                    return RETS_DB_HOST;
                }
                $config = KeyService::getDBConfigByAppRef('gi', 'rets');
                if (!empty($config) && isset($config['host'])) {
                    $host = $config['host'];
                    if (isset($config['port'])) {
                        $host .= ':' . $config['port'];
                    }
                    define('RETS_DB_HOST', $host);
                    return $host;
                }
                break;
            case 'client';
            default:
                if (defined('DB_HOST')) {
                    return DB_HOST;
                }
                $appRef = ProjectConfig::getAppRef();
                if (!empty($appRef)) {
                    $config = KeyService::getDBConfigByAppRef($appRef, 'client');
                    if (!empty($config) && isset($config['host'])) {
                        $host = $config['host'];
                        if (isset($config['port'])) {
                            $host .= ':' . $config['port'];
                        }
                        define('DB_HOST', $host);
                        return $host;
                    }
                }
                break;
        }
        return NULL;
    }

    static function getDbUsername($type = 'client') {
        switch ($type) {
            case 'rets':
                if (defined('RETS_DB_USER')) {
                    return RETS_DB_USER;
                }
                $config = KeyService::getDBConfigByAppRef('gi', 'rets');
                if (!empty($config) && isset($config['username'])) {
                    $user = $config['username'];
                    define('RETS_DB_USER', $user);
                    return $user;
                }
                break;
            case 'client':
            default:
                if (defined('DB_USER')) {
                    return DB_USER;
                }
                $appRef = ProjectConfig::getAppRef();
                if (!empty($appRef)) {
                    $config = KeyService::getDBConfigByAppRef($appRef, 'client');
                    if (!empty($config) && isset($config['username'])) {
                        $username = $config['username'];
                        define('DB_USER', $username);
                        return $username;
                    }
                }
                break;
        }
        return NULL;
    }

    static function getDbPassword($type = 'client') {
        switch ($type) {
            case 'rets':
                if (defined('RETS_DB_PASS')) {
                    return RETS_DB_PASS;
                }
                $config = KeyService::getDBConfigByAppRef('gi', 'rets');
                if (!empty($config) && isset($config['password'])) {
                    $password = $config['password'];
                    define('RETS_DB_PASS', $password);
                    return $password;
                }
                break;
            case 'client':
            default:
                if (defined('DB_PASS')) {
                    return DB_PASS;
                }
                $appRef = ProjectConfig::getAppRef();
                if (!empty($appRef)) {
                    $config = KeyService::getDBConfigByAppRef($appRef, 'client');
                    if (!empty($config) && isset($config['password'])) {
                        $password = $config['password'];
                        define('DB_PASS', $password);
                        return $password;
                    }
                }
                break;
        }
        return NULL;
    }

    static function getDbName($type = 'client') {
        switch ($type) {
            case 'rets':
                if (defined('RETS_DB_NAME')) {
                    return RETS_DB_NAME;
                }
                $config = KeyService::getDBConfigByAppRef('gi', 'rets');
                if (!empty($config) && isset($config['dbname'])) {
                    $dbName = $config['dbname'];
                    define('RETS_DB_NAME', $dbName);
                    return $dbName;
                }
                break;
            case 'client':
            default:
                if (defined('DB_NAME')) {
                    return DB_NAME;
                }
                $appRef = ProjectConfig::getAppRef();
                if (!empty($appRef)) {
                    $config = KeyService::getDBConfigByAppRef($appRef, 'client');
                    if (!empty($config) && isset($config['dbname'])) {
                        $dbname = $config['dbname'];
                        define('DB_NAME', $dbname);
                        return $dbname;
                    }
                }
                break;
        }
        return NULL;
    }

    static function getDbPrefix($type = 'client') {
        switch ($type) {
            case 'rets':
                $dbPrefix = RETS_DB_PREFIX;
                break;
            default:
                $dbPrefix = DB_PREFIX;
                break;
        }
        return $dbPrefix;
    }

    static function getBOSToColTypes() {
        return static::$bosToColTypes;
    }

    static function getBOSToCol($bosType) {
        return static::$bosToColTypes[$bosType];
    }

    static function getCommonColNames($type='full', AbstractTable $table = NULL) {
        $commonColNames = static::$commonColNames;
        if ($type === 'full') {
            if (ProjectConfig::getIsFranchisedSystem() && !empty($table) && !empty($table->getProperty('filter_franchise'))) {
                $commonColNames['franchise_id'] = array(
                    'type'=>'id',
                    'not_null'=>0,
                    'indexed'=>1
                );
            }
            return $commonColNames;
        } else if ($type === 'basic') {
            return array(
                'id'=>$commonColNames['id'],
                'status'=>$commonColNames['status']
            );
        }
        
    }

    static function getBosColTypesAndCleanNames() {
        return static::$bosColTypesAndCleanNames;
    }

    static function getDbColTypesCompOps() {
        return static::$dbColTypesCompOps;
    }

    static function getDbColTypeCompOps($dbColType) {
        return static::$dbColTypesCompOps[$dbColType];
    }

    static function getFormFieldTypes($bosColType = '') {
        if (!empty($bosColType) && isset(static::$bosColTypeFormFieldType[$bosColType])) {
            $fieldTypes = static::$bosColTypeFormFieldType[$bosColType];
        } else {
            $fieldTypes = array();
            foreach (static::$bosColTypeFormFieldType as $bosColType => $bosFieldTypes) {
                $fieldTypes = array_merge($fieldTypes, $bosFieldTypes);
            }
        }
        return $fieldTypes;
    }

    static function getBosColType($formFieldType) {
        if (isset(static::$formFieldTypeBosColTypes[$formFieldType])) {
            return static::$formFieldTypeBosColTypes[$formFieldType];
        }
    }

    static function getFormFieldInclusions($formFieldType) {
        $baseInclusions = array(
            'display_name' => array(
                'display_name' => 'Display Name',
                'type' => 'text'
            ),
            'place_holder' => array(
                'display_name' => 'Place Holder',
                'type' => 'text'
            ),
            'auto_complete' => array(
                'display_name' => 'Auto Complete',
                'type' => 'onoff'
            ),
            'auto_focus' => array(
                'display_name' => 'Auto Focus',
                'type' => 'onoff'
            ),
            'disabled' => array(
                'display_name' => 'Disabled',
                'type' => 'onoff'
            ),
            'read_only' => array(
                'display_name' => 'Read Only',
                'type' => 'onoff'
            ),
            'clear_value' => array(
                'display_name' => 'Clear Value',
                'type' => 'onoff'
            ),
            'show_label' => array(
                'display_name' => 'Show Label',
                'type' => 'onoff'
            ),
            'description' => array(
                'display_name' => 'Description',
                'type' => 'text'
            ),
            'show_description' => array(
                'display_name' => 'Show Description',
                'type' => 'onoff'
            ),
            'show_error' => array(
                'display_name' => 'Show Errors',
                'type' => 'onoff'
            ),
            'hide_desc_on_error' => array(
                'display_name' => 'Hide Description on Error',
                'type' => 'onoff'
            )
        );
        if (isset(static::$bosColTypeFormInclusions[$formFieldType])) {
            $extraInclusions = static::$bosColTypeFormInclusions[$formFieldType];
            $inclusions = array_merge($baseInclusions, $extraInclusions);
        } else {
            $inclusions = $baseInclusions;
        }
        return $inclusions;
    }

    public static function getItemsPerPage() {
        return static::$itemsPerPage;
    }

}
