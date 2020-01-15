<?php

class CodeLangDefinitions {
    
    protected function __construct() {
        
    }
    
    protected function __clone() {
       
    }
    
    protected static $codeLanguages = array(
        'Common' => array(
            'php' => 'PHP',
            'css' => 'CSS',
            'html' => 'HTML',
            'javascript' => 'Javascript',
            'sql' => 'SQL',
            'xml' => 'XML',
        ),
        'Other' => array(
            'csharp' => 'CSharp',
            'java' => 'Java',
            'javafx' => 'JavaFX',
            'perl' => 'Perl',
            'plain' => 'Plain',
            'powershell' => 'Powershell',
            'python' => 'Python',
            'ruby' => 'Ruby',
            'sass' => 'SASS',
            'swift' => 'Swift',
            'tap' => 'Tap',
            'typescript' => 'Typescript',
            'vb' => 'VB'
        )
    );
    
    public static function getCommonCodeLanguages(){
        $codeLanguages = static::$codeLanguages['Common'];
        return $codeLanguages;
    }
    
    public static function getCommonOtherLanguages(){
        $codeLanguages = static::$codeLanguages['Other'];
        return $codeLanguages;
    }
    
    public static function getCodeLanguages(){
        $codeLanguages = static::$codeLanguages;
        return $codeLanguages;
    }
    
}
