<?php
/**
 * Description of GI_Sanitize
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
class GI_Sanitize{
    
    public static function htmlAttribute($string){
        $stringNoSVGs = static::stripSVGs($string);
        $stringNoTags = strip_tags($stringNoSVGs);
        $stringEntities = htmlentities($stringNoTags);
        return $stringEntities;
    }
    
    public static function stripSVGs($string){
        return static::stripBetweenTag('svg', $string);
    }
    
    public static function stripBetweenTag($htmlTag, $string){
        $tagStart = '\<' . $htmlTag;
        $tagEnd = '\<\/' . $htmlTag . '\>';
        return preg_replace('/' . $tagStart . '[\s\S]+?' . $tagEnd . '/', '', $string);
    }
    
    public static function ref($string, &$reasonCleaned = NULL){
        $reasons = array();
        $stringNoSpaces = static::spaces($string);
        if($string != $stringNoSpaces){
            $reasons[] = 'contains spaces';
        }
        
        $stringNoAccents = static::accents($stringNoSpaces);
        if($stringNoSpaces != $stringNoAccents){
            $reasons[] = 'contains accented characters';
        }
        
        $stringNoSpecialChars = static::specialChars($stringNoAccents);
        if($stringNoAccents != $stringNoSpecialChars){
            $reasons[] = 'contains special characters';
        }
        
        $stringNoCaps = strtolower($stringNoSpecialChars);
        if($stringNoSpecialChars != $stringNoCaps){
            $reasons[] = 'contains capital letters';
        }
        
        $reasonCount = count($reasons);
        
        for($i=0; $i<$reasonCount; $i++){
            $reason = $reasons[$i];
            if(!empty($reasonCleaned)){
                $reasonCleaned .= ', ';
            }
            if($reasonCount > 1 && $i+1 == $reasonCount){
                $reasonCleaned .= 'and ';
            }
            $reasonCleaned .= $reason;
        }
        return $stringNoCaps;
    }
    
    public static function specialChars($string){
        return str_replace(str_split(preg_replace('/([[:alnum:]+\-_\ ]*)/','',$string)),'',$string);
    }
    
    public static function filename($string){
        $stringNoSpaces = static::spaces($string);
        $stringNoAccents = static::accents($stringNoSpaces);
        return str_replace(str_split(preg_replace('/([[:alnum:]+_\.-]*)/','',$stringNoAccents)),'',$stringNoAccents);
    }
    
    public static function spaces($string){
        return str_replace(array(
            ' ',
            "\xA0"
        ),'_',$string);
    }
    
    public static function addBackticksToColumnsInQueryString($string){
        //@todo this currently doesn't ignore strings surrounded by ""
        if($string === '*'){
            return $string;
        }
        if(strpos($string, '.') === false && strpos($string, ' ') === false){
            return '`' . $string . '`';
        }
        $backtickedString = preg_replace('/(\.)([^\*.]+?)(\s|\)|$)/', '$1`$2`$3', $string);
        return $backtickedString;
    }
    
    public static function accents($string){
        $accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
        $replacements = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
        return str_replace($accents, $replacements, $string);
    }
    
    public static function escapeForICS($string){
        return preg_replace('/([\,;])/','\\\$1', $string);
    }
    
    public static function spaceImposters($string){
        $utf8String = utf8_encode($string);
        return preg_replace('~\xc2\xa0~', '', $utf8String);
    }
    
}
