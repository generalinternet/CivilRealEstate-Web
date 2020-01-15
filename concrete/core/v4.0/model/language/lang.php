<?php

class Lang {
    
    protected static $languages = array(
        'english' => 'English',
        //'french' => 'French'
    );
    
    protected function __construct() { }
    
    protected function __clone() { }
    
    public static function getString($string, $ucFirst = true) {
        $strings = unserialize(STRINGS);
        if ($ucFirst) {
            if(isset($strings[$string])){
                $string = $strings[$string];
            } else {
                $string = 'Missing Definition : [' . $string . ']';
                //$string = str_replace('_', ' ', $string);
            }
            $pieces = explode(' ', $string);
            $returnString = ucfirst($pieces[0]);
            if (sizeof($pieces) > 1) {
                for ($i=1;$i<sizeof($pieces);$i++) {
                    $returnString .= ' ' . ucfirst($pieces[$i]);
                }
            }
            return $returnString;
        } else {
            return $strings[$string];
        }
    }
    
    public static function getError($errorCode){
        $errorStrings = unserialize(ERROR_CODES);
        if(isset($errorStrings[$errorCode])){
            return $errorStrings[$errorCode];
        } else {
            return $errorStrings[528491];
        }        
    }
    
    public static function getErrorCodes(){
        $errorStrings = unserialize(ERROR_CODES);
        return $errorStrings;
    }
    
    public static function getLanguageTitle($systemTitle) {
        return static::$languages[$systemTitle];
    }
    
    public static function getLanguages() {
        return static::$languages;
    }
}