<?php
/**
 * Description of GI_Colour
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version 3.0.0
 */
class GI_Colour{
    
    protected static $lastUsedIndex = array(
        'default' => NULL,
        'pale' => NULL,
        'paler' => NULL
    );
    
    protected static $defaultColours = array(
        '821d1e',
        'f23f5b',
        'db634f',
        'bf447c',
        'f29a3f',
        'f2d53f',
        '1d823d',
        '53bf44',
        '44bfb0',
        '1d8281',
        '3f96f2',
        '1d6182'
    );
    
    protected static $paleColours = array(
        'c08e8e',
        'f89fad',
        'edb1a7',
        'dfa1bd',
        'f8cc9f',
        'f8ea9f',
        '8ec09e',
        'a9dfa1',
        'a1dfd7',
        '8ec0c0',
        '9fcaf8',
        '8eb0c0'
    );
    
    protected static $palerColours = array(
        'e0c6c7',
        'fccfd6',
        'f6d8d3',
        'efd0de',
        'fce6cf',
        'fcf4cf',
        'c6e0ce',
        'd4efd0',
        'd0efeb',
        'c6e0df',
        'cfe5fc',
        'c6d7e0'
    );
    
    public static function getRandomColour($type = 'default', $notThisColour = NULL, $refId = NULL){
        if(isset(static::${$type.'Colours'})){
            $colours = static::${$type.'Colours'};
            $refId = (int) $refId;
            if(!empty($refId)){
                return $colours[$refId%count($colours)];
            }
            if(!empty($notThisColour)){
                $randColour = $colours[rand(0,count($colours)-1)];
                while($randColour == $notThisColour){
                    $randColour = $colours[rand(0,count($colours)-1)];
                }
                return $randColour;
            } else {
                return $colours[rand(0,count($colours)-1)];
            }
        } else {
            return NULL;
        }
    }
    
    public static function getColours($type = 'default'){
        if(isset(static::${$type.'Colours'})){
            $colours = static::${$type.'Colours'};
            return $colours;
        } else {
            return array();
        }
    }
    
    public static function getNextColour($type = 'default'){
        $lastUsedIndex = static::$lastUsedIndex[$type];
        if(is_null($lastUsedIndex)){
            $useIndex = 0;
        } else {
            $useIndex = $lastUsedIndex+1;
        }
        $colours = static::${$type.'Colours'};
        if(isset($colours[$useIndex])){
            static::$lastUsedIndex[$type] = $useIndex;
            return $colours[$useIndex];
        }
        static::$lastUsedIndex[$type] = 0;
        return $colours[0];
    }
    
    public static function getRandomHexColourPart($min = 0, $max = 255){
        return str_pad(dechex(mt_rand($min, $max)), 2, '0', STR_PAD_LEFT);
    }
    
    public static function getRandomHexColour($rMin = 0, $rMax = 255, $gMin = 0, $gMax = 255, $bMin = 0, $bMax = 255){
        return static::getRandomHexColourPart($rMin, $rMax) . static::getRandomHexColourPart($gMin, $gMax) . static::getRandomHexColourPart($bMin, $bMax);
    }
    
    public static function convertHexToLuminosity($hexColour){
        $red = hexdec(substr($hexColour, 0, 1));
        $green = hexdec(substr($hexColour, 2, 3));
        $blue = hexdec(substr($hexColour, 4, 5));
        $luminosity = (0.299 * $red + 0.587 * $green + 0.114 * $blue);
        return (float) $luminosity;
    }
    
    /**
     * Sorts an array of HEX codes by Luminosity
     * 
     * @param array $hexColours array of HEX colour codes
     * @param boolean $darkToLight
     * @return array
     */
    public static function sortColoursByLuminosity(&$hexColours = array(), $darkToLight = true){
        usort($hexColours, function($hex1, $hex2) use ($darkToLight){
            $colour1 = $hex2;
            $colour2 = $hex1;
            if($darkToLight){
                $colour1 = $hex1;
                $colour2 = $hex2;
            }
            return static::convertHexToLuminosity($colour1) - static::convertHexToLuminosity($colour2);
        });
        return $hexColours;
    }
    
    public static function convertHexToHSV($hexColour){
        $red = hexdec(substr($hexColour, 0, 2));
        $green = hexdec(substr($hexColour, 2, 2));
        $blue = hexdec(substr($hexColour, 4, 2));
        
        $r = $red / 255;
        $g = $green / 255;
        $b = $blue / 255;
        
        $min = min($r, $g, $b);
        $max = max($r, $g, $b);
        $deltaMax = $max - $min;
        
        $v = $max;
        if($deltaMax == 0){
            $h = -1;
            $s = 0;
        } else {
            $s = $deltaMax / $max;
            if($r == $max){
                $h = ($g - $b) / $deltaMax;
                // between yellow & magenta 
            } elseif( $g == $max ){
                $h = 2 + ($b - $r) / $deltaMax;
                // between cyan & yellow
            } else {
                $h = 4 + ($r - $g) / $deltaMax;
                // between magenta & cyan
            }
            $h *= 60;
            if( $h < 0 ) {
                $h += 360;
            }
        }
        $hsv = array(
            'h' => (float) $h,
            's' => (float) $s,
            'v' => (float) $v
        );
        return $hsv;
    }
    
    /**
     * Sorts an array of HEX codes by HSV
     * 
     * @param type $hexColours array of HEX colour codes
     * @return array sorted
     */
    public static function sortColoursByHSV(&$hexColours = array()){
        usort($hexColours, function($hex1, $hex2){
            $hsv1 = static::convertHexToHSV($hex1);
            $hsv2 = static::convertHexToHSV($hex2);
            if ($hsv1['h'] < $hsv2['h']){
                return -1;
            }
            if ($hsv1['h'] > $hsv2['h']){
                return 1;
            }
            if ($hsv1['s'] < $hsv2['s']){
                return -1;
            }
            if ($hsv1['s'] > $hsv2['s']){
                return 1;
            }
            if ($hsv1['v'] < $hsv2['v']){
                return -1;
            }
            if ($hsv1['v'] > $hsv2['v']){
                return 1;
            }
            return 0;
        });
        
        return $hexColours;
    }
    
    /**
     * Sorts by HSV and Luminosity to create a step sort
     * 
     * @param array $hexColours array of HEX colour codes
     * @param integer $steps
     * @param boolean $darkToLight
     * @param boolean $blended
     * @return array sorted
     */
    public static function sortColoursByStep(&$hexColours = array(), $steps = 8, $darkToLight = true, $blended = false){
        usort($hexColours, function($hex1, $hex2) use($steps, $darkToLight, $blended){
            $lum1 = static::convertHexToLuminosity($hex1);
            $hsv1 = static::convertHexToHSV($hex1);
            $h1Percent = $hsv1['h'] / 360;
            $h1 = (int) ($h1Percent * $steps);
            $v1 = ($hsv1['v'] * $steps);
            
            if($blended && $h1 % 2 == 1){
                $v1 = $steps - $v1;
                $lum1 = $steps - $lum1;
            }
            
            $lum2 = static::convertHexToLuminosity($hex2);
            $hsv2 = static::convertHexToHSV($hex2);
            $h2Percent = $hsv2['h'] / 360;
            $h2 = (int) ($h2Percent * $steps);
            $v2 = ($hsv2['v'] * $steps);
            
            if($blended && $h2 % 2 == 1){
                $v2 = $steps - $v2;
                $lum2 = $steps - $lum2;
            }
            
            if ($h1 < $h2){
                return -1;
            }
            if ($h1 > $h2){
                return 1;
            }
            if ($lum1 < $lum2){
                if($darkToLight){
                    return -1;
                }
                return 1;
            }
            if ($lum1 > $lum2){
                if($darkToLight){
                    return 1;
                }
                return -1;
            }
            if ($v1 < $v2){
                return -1;
            }
            if ($v1 > $v2){
                return 1;
            }
            return 1;
        });
        
        return $hexColours;
    }
    
    public static function useLightFont($hexColour, $useLum = true){
        if($useLum){
            $lum = static::convertHexToLuminosity($hexColour);
            if($lum < 1500){
                return true;
            } else {
                return false;
            }
        }
        $red = hexdec(substr($hexColour, 0, 2));
        $green = hexdec(substr($hexColour, 2, 2));
        $blue = hexdec(substr($hexColour, 4, 2));
        
        if($red + $green + $blue > 382){
            return false;
        } else {
            return true;
        }
    }
    
}
