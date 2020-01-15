<?php
/**
 * Description of GI_StringUtils
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.5
 */
class GI_StringUtils{
    
    public static function summarize($text, $limit, $lastWord = false){
        $text = trim(strip_tags(str_replace(array('<br>','<br/>'),' ',$text)));
        $text_len = strlen($text);
        if($text_len>$limit){
            if($lastWord){
                $finalText = substr($text, 0, strrpos(substr($text, 0, $limit), ' ')).'&hellip;';
            } else {
                $finalText = substr($text, 0, $limit).'&hellip;';
            }	
        } else {
            $finalText = $text;
        }
        return($finalText);
    }
    
    public static function embedYouTube($string){
        $embededString = preg_replace('~
        # Match non-linked youtube URL in the wild. (Rev:20130823)
        https?://         # Required scheme. Either http or https.
        (?:[0-9A-Z-]+\.)? # Optional subdomain.
        (?:               # Group host alternatives.
          youtu\.be/      # Either youtu.be,
        | youtube\.com    # or youtube.com followed by
          \S*             # Allow anything up to VIDEO_ID,
          [^\w\-\s]       # but char before ID is non-ID char.
        )                 # End host alternatives.
        ([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
        (?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
        (?!               # Assert URL is not pre-linked.
          [?=&+%\w.-]*    # Allow URL (query) remainder.
          (?:             # Group pre-linked alternatives.
            [\'"][^<>]*>  # Either inside a start tag,
          | </a>          # or inside <a> element text contents.
          )               # End recognized pre-linked alts.
        )                 # End negative lookahead assertion.
        [?=&amp;+%\w.-]*        # Consume any URL (query) remainder.
        ~ix', 
        '<iframe class="youtube_vid" src="http://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
        $string);
        return $embededString;
    }
    
    public static function convertURLs($string, $embedYoutube = false){
        if($embedYoutube){
            $string = GI_StringUtils::embedYoutube($string);
        }
        $convertedWithProtocol = preg_replace('$(https?://[a-z0-9_,./?=&#-&amp;\-\+:]+)(?![^<>]*>)$i', ' <a href="$1" target="_blank">$1</a> ', $string." ");
        
        $convertedWithoutProtocol = preg_replace('$(www\.[a-z0-9_,./?=&#-&amp;\-\+:]+)(?![^<>]*>)$i', '<a target="_blank" href="http://$1"  target="_blank">$1</a> ', $convertedWithProtocol." ");
        
        return $convertedWithoutProtocol;
    }
    
    public static function fixLink($link){
        if  ($url = parse_url($link)){
            if (!isset($url["scheme"])){
               $link = "http://{$link}";
            }
        }
        return $link;
    }
    
    public static function nl2brHTML($string){
        $string = str_replace("\t",'&nbsp;&nbsp;&nbsp;',$string);
        $string = nl2br($string);
        $string = str_replace("\n", '', $string);
        $string = str_replace("\r", '', $string);

        if(preg_match_all('/\<pre\>(.*?)\<\/pre\>/', $string, $match)){
            foreach($match as $a){
                foreach($a as $b){
                $string = str_replace('<pre>'.$b.'</pre>', "<pre>".str_replace("<br />", PHP_EOL, $b)."</pre>", $string);
                }
            }
        }
        
        $stringSearches = array(
            '<br /><br /><br /><pre>',
            '</pre><br /><br />',
            '<br /><p>',
            '</p><br />',
            '<p><br />',
            '<br /></p>',
            '<br /><ul>',
            '</ul><br />',
            '<ul><br />',
            '<br /></ul>',
            '<br /><ol>',
            '</ol><br />',
            '<ol><br />',
            '<br /></ol>',
            '<br /><li>',
            '</li><br />'
        );
        $stringReplaces = array(
            '<br /><br /><pre>',
            '</pre><br />',
            '<p>',
            '</p>',
            '<p>',
            '</p>',
            '<ul>',
            '</ul>',
            '<ul>',
            '</ul>',
            '<ol>',
            '</ol>',
            '<ol>',
            '</ol>',
            '<li>',
            '</li>'
        );
        $string = str_replace($stringSearches, $stringReplaces, $string);
        return $string;
    }
    
    public static function buildAddrString($addrStreet = '', $addrCity = '', $addrRegion = '', $addrCode = '', $addrCountry = '', $breakLines = true, $addrStreetTwo = '', $forceIncludeCountry = true) {
        $addrArray = [];
        if ($breakLines) {
            $lineBreaker = '<br/>';
        } else {
            $lineBreaker = ', ';
        }

        if (!empty($addrStreet)) {
            $addrArray[] = $addrStreet;
        }

        if (!empty($addrStreetTwo)) {
            $addrArray[] = $addrStreetTwo;
        }

        if (!empty($addrCity)) {
            $addrArray[] = $addrCity;
        }

        if (!empty($addrRegion)) {
            $addrArray[] = $addrRegion;
        }

        if (
            (!empty($addrCountry)) &&
            ($forceIncludeCountry || ($addrCountry != ProjectConfig::getDefaultCountryCode()))
        ) {
            $addrArray[] = GeoDefinitions::getCountryNameFromCode($addrCountry);
        }

        $addr = implode($lineBreaker, $addrArray);

        if (!empty($addrCode)) {
            $addr .= ' ' . $addrCode;
        }
        return $addr;
    }
    
    public static function buildAddrString2Lines($addrStreet = '', $addrCity = '', $addrRegion = '', $addrCode = ''){
        $addr = '';
        if (!empty($addrStreet)) {
            $addr .= $addrStreet;
            if (!empty($addrCity) || !empty($addrRegion) || !empty($addrCode)) {
                $addr .= '<br/>';
            }
        }
        if (!empty($addrCity)) {
            $addr .= $addrCity;
            if (!empty($addrRegion)) {
                $addr .= ', ';
            }
        }
        if (!empty($addrRegion)) {
            $addr .= $addrRegion;
            if (!empty($addrCode)) {
                $addr .= ' ';
            }
        }
        if (!empty($addrCode)) {
            $addr .= $addrCode;
        }
        return $addr;
    }
    
    public static function convertNumberToWords($number, $capitalize = false, $hyphen = '-', $conjunction = ' and ', $separator = ', ', $negative = 'negative ', $decimal = ' point ', $numberIsMoney = false, $centsAsWords = false, $cents = ' cents') {
        $lowercaseRefs = array(
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );
        $uppercaseRefs = array(
            0 => 'Zero',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Fourty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety',
            100 => 'Hundred',
            1000 => 'Thousand',
            1000000 => 'Million',
            1000000000 => 'Billion',
            1000000000000 => 'Trillion',
            1000000000000000 => 'Quadrillion',
            1000000000000000000 => 'Quintillion'
        );
        
        if($capitalize){
            $numberRefs = $uppercaseRefs;
        } else {
            $numberRefs = $lowercaseRefs;
        }
        
        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'GI_StringUtils::convertNumberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . static::convertNumberToWords(abs($number), $capitalize, $hyphen, $conjunction, $separator);
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $numberRefs[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $numberRefs[$tens];
                if ($units) {
                    $string .= $hyphen . $numberRefs[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $numberRefs[$hundreds] . ' ' . $numberRefs[100];
                if ($remainder) {
                    $string .= $conjunction . static::convertNumberToWords($remainder, $capitalize, $hyphen, $conjunction, $separator);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = static::convertNumberToWords($numBaseUnits, $capitalize, $hyphen, $conjunction, $separator) . ' ' . $numberRefs[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= static::convertNumberToWords($remainder, $capitalize, $hyphen, $conjunction, $separator);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            if ($numberIsMoney) {
                $centRound = round('.' . $fraction, 2);
                $centNumber = $centRound * 100;
                if (!$centsAsWords) {
                    $string .= sprintf("%02d", $centNumber) . '/100';
                } else {
                    $string .= static::convertNumberToWords(abs($centNumber), $capitalize, $hyphen, $conjunction, $separator);
                    $string .= $cents;
                }
            } else {
                $words = array();
                foreach (str_split((string) $fraction) as $number) {
                    $words[] = $numberRefs[$number];
                }
                $string .= implode(' ', $words);
            }
        }

        return $string;
    }

    public static function convertNumberForCheque($number, $capitalize = true, $centsAsWords = false) {
        $decimal = ' and ';
        $cents = ' cents';
        return static::convertNumberToWords($number, $capitalize, '-', ' ', ' ', 'negative ', $decimal, true, $centsAsWords, $cents);
    }

    public static function formatMoney($amount, $withCommas = true, $precision = 2) {
        if (GI_Math::floatEquals($amount, 0)) {
            $amount = 0;
        }
        if ($withCommas) {
            return number_format($amount, $precision);
        } else {
            return number_format($amount, $precision, '.', '');
        }
    }
    
    public static function formatMoneyRate($amount, $withCommas = true, $precision = 7) {
        if (GI_Math::floatEquals($amount, 0)) {
            $amount = 0;
        }
        if ($withCommas) {
            $amountString = number_format($amount, $precision);
        } else {
            $amountString = number_format($amount, $precision, '.', '');
        }
        $pieces = explode('.', $amountString);
        $wholePiece = $pieces[0];
        if (isset($pieces[1])) {
            $decimalPiece = $pieces[1];
            $decimalChars = str_split($decimalPiece);
            $count = count($decimalChars);
            for ($i = $count - 1; $i > 1; $i--) {
                if ($decimalChars[$i] !== '0') {
                    break;
                } else {
                    unset($decimalChars[$i]);
                }
            }
            $newDecimalPiece = implode('', $decimalChars);
            $amountString = $wholePiece . '.' . $newDecimalPiece;
        }
        return $amountString;
    }

    public static function formatFloat($float, $withCommas = true){
        $precision = ProjectConfig::getDefaultRoundPrecision();
        if ($withCommas) {
            $formattedFloat = number_format($float, $precision);
        } else {
            $formattedFloat = number_format($float, $precision, '.', '');
        }
        
        if (false !== strpos($formattedFloat, '.')){
            return rtrim(rtrim($formattedFloat, '0'), '.');
        }
        
        return $formattedFloat;
    }
    
    public static function formatMoneyForField($amount, $precision = 2){
        return static::formatMoney($amount, false, $precision);
    }

    public static function formatStringForRef($string) {
        $returnString = preg_replace('/\s+/', '_', strtolower($string));
        return $returnString;
    }

    public static function sanitizeControllerClassName($controllerClassName) {
        return substr($controllerClassName, 0, -10);
    }
    
    public static function generateRandomString($length = 8, $strict = false, $lowercase = true, $uppercase = true, $numbers = true, $special = true, $limitNumbers = 2, $limitSpecial = 2) {
        if ($strict) {
            return static::generateRandomStringStrict($length, $lowercase, $uppercase, $numbers, $special, $limitNumbers, $limitSpecial);
        } else {
            return static::generateRandomStringNonStrict($length, $lowercase, $uppercase, $numbers, $special, $limitNumbers, $limitSpecial);
        }
    }

    protected static function generateRandomStringNonStrict($length = 8, $lowercase = true, $uppercase = true, $numbers = true, $special = true, $limitNumbers = 2, $limitSpecial = 2) {
        if (!$lowercase && !$uppercase && !$numbers && !$special) {
            return '';
        } else if (!$lowercase && !$uppercase) {
            if ($numbers && $special) {
                $limitSum = $limitNumbers + $limitSpecial;
                $limitNumRatio = $limitNumbers / $limitSum;
                $limitSpecRatio = $limitSpecial / $limitSum;
                $limitSpecial = floor($length * $limitSpecRatio);
                $limitNumbers = ceil($length * $limitNumRatio);
            } else if (!$numbers && $special) {
                if ($limitSpecial < $length) {
                    $limitSpecial = $length;
                }
            } else {
                if ($limitNumbers < $length) {
                    $limitNumbers = $length;
                }
            }
        }
        $uppercaseIndex = 0;
        $lowercaseIndex = 1;
        $numberIndex = 2;
        $specialIndex = 3;
        $chars = array(
            $uppercaseIndex => array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'),
            $lowercaseIndex => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'),
            $numberIndex => array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'),
            $specialIndex => array('!', '#', '$', '%', '^', '&', '*', '?'),
        );
        $upperLimits = array(
            $uppercaseIndex => 25,
            $lowercaseIndex => 25,
            $numberIndex => 9,
            $specialIndex => 7
        );
        $indexes = array();
        if ($uppercase) {
            $indexes[] = $uppercaseIndex;
        }
        if ($lowercase) {
            $indexes[] = $lowercaseIndex;
        }

        if ($numbers) {
            $indexes[] = $numberIndex;
        }
        if ($special) {
            $indexes[] = $specialIndex;
        }
        $indexes = array_values($indexes);
        $indexCount = sizeof($indexes);
        $string = '';
        $numbersLeft = $limitNumbers;
        $specialLeft = $limitSpecial;
        for ($i = 0; $i < $length; $i++) {
            $validIndex = false;
            while (!$validIndex) {
                $index = NULL;
                while (is_null($index)) {
                    $randIndex = mt_rand(0, $indexCount);
                    if(isset($indexes[$randIndex])){
                        $index = $indexes[$randIndex];
                    }
                }
                    if ($index == $lowercaseIndex && $lowercase) {
                        $validIndex = true;
                    } else if ($index == $uppercaseIndex && $uppercase) {
                        $validIndex = true;
                    } else if (($index == $numberIndex) && $numbersLeft > 0) {
                        $validIndex = true;
                        $numbersLeft--;
                    } else if (($index == $specialIndex) && $specialLeft > 0) {
                        $validIndex = true;
                        $specialLeft--;
                    }
            }
            $charArray = $chars[$index];
            $charUpperLimit = $upperLimits[$index];
            $charIndex = NULL;
            while (is_null($charIndex)) {
                $charIndex = mt_rand(0, $charUpperLimit);
            }
            $char = $charArray[$charIndex];
            $string .= $char;
        }
        return $string;
    }

    protected static function generateRandomStringStrict($length = 8, $lowercase = true, $uppercase = true, $numbers = true, $special = true, $limit_numbers = 2, $limit_special = 2) {
        $return_val = '';
        $uppercase_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase_chars = strtolower($uppercase_chars);
        $numbers_chars = '1234567890';
        $special_chars = '!#$%^&*?';
        $char_types = array();
        if ($lowercase) {
            array_push($char_types, 'lowercase_chars');
        }
        if ($uppercase) {
            array_push($char_types, 'uppercase_chars');
        }
        if ($numbers) {
            array_push($char_types, 'numbers_chars');
        }
        if ($special) {
            array_push($char_types, 'special_chars');
        }
        $avail_types = $char_types;
        $used_types = array();
        $number_count = 0;
        $special_count = 0;
        $i = 0;
        while ($i < $length) {
            $chars_left = $length - $i;
            $type_count = count($char_types);
            $used_count = count($used_types);
            $types_left = $type_count - $used_count;
            $rand_type = $avail_types[array_rand($avail_types)];
            $char = substr(${$rand_type}, mt_rand(0, strlen(${$rand_type}) - 1), 1);
            if ($i == 0 && ($rand_type == 'special_chars' || $rand_type == 'numbers_chars')) {
                continue;
            }
            if (!strstr($return_val, $char)) {
                $return_val .= $char;
                $i++;
                if (!in_array($rand_type, $used_types)) {
                    array_push($used_types, $rand_type);
                }
                if ($rand_type == 'numbers_chars') {
                    $number_count++;
                }
                if ($rand_type == 'special_chars') {
                    $special_count++;
                }
                if ($number_count == $limit_numbers && $limit_numbers != 0 && in_array('numbers_chars', $avail_types)) {
                    unset($avail_types[array_search('numbers_chars', $avail_types)]);
                }
                if ($special_count === $limit_special && $limit_special != 0 && in_array('special_chars', $avail_types)) {
                    unset($avail_types[array_search('special_chars', $avail_types)]);
                }
                if ($length - $i == $types_left && $char_types != $used_types) {
                    $avail_types = array_diff($char_types, $used_types);
                }
            }
        }
        return $return_val;
    }
    
    /**
     * Appends a NON empty string to another string
     * 
     * @param string $append
     * @param string $appendTo
     * @return string
     */
    public static function appendToString($append, &$appendTo, $suffix = NULL){
        if(!empty($append)){
            $appendTo .= $append;
            if(!empty($suffix)){
                $appendTo .= $suffix;
            }
        }
        return $appendTo;
    }
    
    /**
     * Surrounds [$term] with <mark> tags
     * 
     * @param string $term
     * @param string $string
     * @return string
     */
    public static function markTerm($term, $string) {
        if (!empty($term)) {
            $term = str_replace('/', '\/', $term);
            return preg_replace('/' . $term . '(?!([^<]+)?>)/i', "<mark>\$0</mark>", $string);
        }
        return $string;
    }
    
    /**
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceFirst($search, $replace, $subject){
        $pattern = '/' . preg_quote($search, '/') . '/';
        
        return preg_replace($pattern, $replace, $subject, 1);
    }
    
    /**
     * @param string $string
     * @return string
     */
    public static function removeTicks($string){
        return str_replace('`', '', $string);
    }
    
    /**
     * @param string $string string to surround
     * @param string $tag html tag (ex. 'span', 'h1', etc.)
     * @param array $htmlAttributes array of html attributes (ex. array('id' => 'header', 'class' => 'red', 'data-ref' => '234'))
     * @return string
     */
    public static function surroundWithTag($string, $tag, $htmlAttributes = array()){
        $finalString = '<' . $tag;
        foreach($htmlAttributes as $attr => $val){
            $finalString .= ' ' . $attr . '="' . $val . '"';
        }
        $finalString .= '>';
        $finalString .= $string;
        $finalString .= '</' . $tag . '>';
        return $finalString;
    }
    
    public static function formatQuery($queryString, $wrap = true){
        $finalString = '';
        if($wrap){
            $finalString .= '<pre>';
        }
        $finalString .= str_replace(array(
            ' FROM',
            ' INNER JOIN',
            ' RIGHT JOIN',
            ' LEFT JOIN',
            ' WHERE',
            ' AND',
            ' OR ',
            ' GROUP BY',
            ' HAVING',
            ' ORDER BY',
            'CASE'
        ), array(
            "\n" . 'FROM',
            "\n" . 'INNER JOIN',
            "\n" . 'RIGHT JOIN',
            "\n" . 'LEFT JOIN',
            "\n" . 'WHERE',
            "\n\t" . 'AND',
            "\n\t" . 'OR ',
            "\n" . 'GROUP BY',
            "\n" . 'HAVING',
            "\n" . 'ORDER BY',
            "\n" . 'CASE'
        ), $queryString);
        if($wrap){
            $finalString .= '</pre>';
        }
        return $finalString;
    }
    
    public static function formatException(Exception $ex){
        $string = 'Caught Exception ("' . $ex->getMessage() . '")';
        return $string;
    }

    public static function isValidJSON($string) {
        if (!isset($string) || trim($string) === '') {
            return false;
        }
        json_decode($string);
        if (json_last_error() != JSON_ERROR_NONE) {
            return false;
        }
        return true;
    }
    
    /**
     * EnbedSVG icon's path from svg file
     * @param type $fileName
     * @param type $width
     * @param type $height
     * @param type $classNames
     * @return type
     */
    public static function getSVGIcon($fileName, $width = '50px', $height = '50px', $classNames = '', $customPath = false){
        //Find SVG file
        if($customPath){
            $svgFilePath = $fileName;
        } else {
            $svgDirPath = 'framework/core/' . FRMWK_CORE_VER. '/resources/media/svgs/';
            $iconName = static::getSVGIconName($fileName);
            $svgFilePath = $svgDirPath.'icon_svg_'.$iconName.'.svg';
        }
        if(!file_exists($svgFilePath)){
            //Default
            $svgFilePath = $svgDirPath.'icon_svg_info.svg';
        }
        return '<span class="svg_icon '.$classNames.'" style="width:'.$width.';height:'.$height.';">'.file_get_contents($svgFilePath).'</span>';
    }
    
    public static function getIcon($icon, $withWrap = true, $colour = NULL){
        $iconView = new IconView($icon);
        $iconView->setAddIconWrap($withWrap);
        if(!empty($colour)){
            $iconView->setIconColour($colour);
        }
        return $iconView->getHTMLView();
    }
    
    public static function stringContainsUppercase($string){
        if(preg_match('/[A-Z]/', $string) === 0) {
            return false;
	}
	return true;
    }
    
    public static function stringContainsLowercase($string){
        if(preg_match('/[a-z]/', $string) === 0) {
            return false;
	}
	return true;
    }
    
    public static function stringContainsSymbol($string){
        if (preg_match('/\W/', $string) === 0){
            return false;
        }
	return true;
    }
    
    public static function stringContainsNumber($string){
        if (preg_match('/\d/', $string) === 0){
            return false;
        }
	return true;
    }
    
    public static function stringContainsWhiteSpace($string){
        if (preg_match('/\s/', $string) === 0){
            return false;
        }
	return true;
    }
    
    public static function validatePassword($password, &$reason = ''){
        $badPass = false;
        $minLength = ProjectConfig::getPassMinLength();
        if(strlen($password) < $minLength){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must be at least ' . $minLength . ' characters.';
            $badPass = true;
        }
        
        $forceUpper = ProjectConfig::getPassReqUpper();
        if($forceUpper && !GI_StringUtils::stringContainsUppercase($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must contain at least 1 uppercase letter.';
            $badPass = true;
        }
        
        $forceLower = ProjectConfig::getPassReqLower();
        if($forceLower && !GI_StringUtils::stringContainsLowercase($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must contain at least 1 lowercase letter.';
            $badPass = true;
        }
        
        $forceSymbol = ProjectConfig::getPassReqSymbol();
        if($forceSymbol && !GI_StringUtils::stringContainsSymbol($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must contain at least 1 symbol. (ex. #,@,!,?)';
            $badPass = true;
        }
        
        $forceNum = ProjectConfig::getPassReqNum();
        if($forceNum && !GI_StringUtils::stringContainsNumber($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Must contain at least 1 number.';
            $badPass = true;
        }
        
        if(GI_StringUtils::stringContainsWhiteSpace($password)){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Cannot contain any whitespace.';
            $badPass = true;
        }
        
        if($badPass){
            return false;
        }
        return true;
    }
    
    public static function getSVGIconName($icon) {
        $iconMap = array(
            'inventory' => 'barcode',
            'item' => 'barcode',
            'workorder' => 'clipboard_text',
            'order' => 'clipboard_text',
            'purchase_order' => 'clipboard_text',
            'purchase_orders' => 'clipboard_text',
            'sales_order' => 'clipboard_money',
            'sales_orders' => 'clipboard_money',
            'work_order' => 'clipboard_work_order',
            'head_office' => 'office',
            'admin' => 'gear',
            'notification' => 'bell',
            'client' => 'contacts',
            'vendor' => 'contacts',
            'contact_warehouse' => 'warehouse',
            'category' => 'contacts',
            'internal' => 'contacts',
            'shipper' => 'shipping',
            'users' => 'person',
        );
        if (isset($iconMap[$icon])) {
            return $iconMap[$icon];
        }
        return $icon;
    }
    
    public static function getLabelWithValue($label, $value = NULL, $forceShow = false, $emptyValue = '--'){
        if(empty($value) && !$forceShow){
            return NULL;
        }
        $finalValue =  $value;
        if(empty($finalValue)){
            $finalValue = $emptyValue;
        }
        $string = '<span class="label_with_value">';
            $string .= '<span class="label">' . $label . '</span>';
            $string .= '<span class="value">' . $finalValue . '</span>';
        $string .= '</span>';
        return $string;
    }

    public static function getSVGAvatar($modelNum, $width = '50px', $height = '50px', $classNames = '', $customPath = false){
        //Find SVG file
        if($customPath){
            $svgFilePath = $modelNum;
        } else {
            $svgDirPath = 'framework/core/' . FRMWK_CORE_VER. '/resources/media/avatars/';
            $svgFilePath = $svgDirPath. 'avatar_model_' . sprintf('%03d', $modelNum) . '.svg';
}
        if(!file_exists($svgFilePath)){
            return $svgFilePath;
            return;
        }
        return '<span class="svg_avatar ' . $classNames . '" style="width:'.$width.';height:'.$height.';">'.file_get_contents($svgFilePath).'</span>';
    }
    
    public static function getPasswordRules($field, $confField = NULL, $showCannotBeSame = false){
        $string = '<div class="validate_pass" data-field="' . $field . '" data-conf-field="' . $confField . '">';
        $string .= '<ul class="sml_text pass_check">';
        if($showCannotBeSame){
            $string .= '<li>Cannot be the same as your current password.</li>';
        }
        $minLength = ProjectConfig::getPassMinLength();
        if($minLength > 1){
            $string .= '<li data-rule="length" data-val="' . $minLength . '">Must be at least ' . $minLength . ' characters long.</li>';
        }
        
        $forceUpper = ProjectConfig::getPassReqUpper();
        if($forceUpper){
            $string .= '<li data-rule="upper" data-val="1">Must contain at least 1 uppercase letter.</li>';
        }
        
        $forceLower = ProjectConfig::getPassReqLower();
        if($forceLower){
            $string .= '<li data-rule="lower" data-val="1">Must contain at least 1 lowercase letter.</li>';
        }
        
        $forceSymbol = ProjectConfig::getPassReqSymbol();
        if($forceSymbol){
            $string .= '<li data-rule="symbol" data-val="1">Must contain at least 1 symbol. (ex. #,@,!,?)</li>';
        }
        
        $forceNum = ProjectConfig::getPassReqNum();
        if($forceNum){
            $string .= '<li data-rule="number" data-val="1">Must contain at least 1 number.</li>';
        }
        
        $string .= '<li data-rule="whitespace" data-val="0">Cannot contain any whitespace.</li>';
        
        $string .= '<li data-rule="match" data-val="1">Must be entered exactly the same twice.</li>';
        $string .= '</ul>';
        $string .= '</div>';
        return $string;
    }

}
