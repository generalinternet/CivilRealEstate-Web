<?php
/**
 * Description of GeoDefinitions
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.3
 */
class GeoDefinitions {
    
    //variables used to generate countires variable
    protected static $preferredCountryCodes = array(
        'CAN',
        'USA'
    );
    
    //list of other countries, leave blank for all
    protected static $otherCountryCodes = array();
    
    protected static $ignoreCountryCodes = array();
    
    protected function __construct() {
        
    }
    
    protected function __clone() {
       
    }
    
    protected static $countries = array(
        'CAN' => 'Canada',
        'USA' => 'United States of America',
        'AFG' => 'Afghanistan',
        'ALA' => 'Åland Islands',
        'ALB' => 'Albania',
        'DZA' => 'Algeria',
        'ASM' => 'American Samoa',
        'AND' => 'Andorra',
        'AGO' => 'Angola',
        'AIA' => 'Anguilla',
        'ATA' => 'Antarctica',
        'ATG' => 'Antigua and Barbuda',
        'ARG' => 'Argentina',
        'ARM' => 'Armenia',
        'ABW' => 'Aruba',
        'AUS' => 'Australia',
        'AUT' => 'Austria',
        'AZE' => 'Azerbaijan',
        'BHS' => 'Bahamas',
        'BHR' => 'Bahrain',
        'BGD' => 'Bangladesh',
        'BRB' => 'Barbados',
        'BLR' => 'Belarus',
        'BEL' => 'Belgium',
        'BLZ' => 'Belize',
        'BEN' => 'Benin',
        'BMU' => 'Bermuda',
        'BTN' => 'Bhutan',
        'BOL' => 'Bolivia',
        'BES' => 'Bonaire',
        'BIH' => 'Bosnia and Herzegovina',
        'BWA' => 'Botswana',
        'BVT' => 'Bouvet Island',
        'BRA' => 'Brazil',
        'IOT' => 'British Indian Ocean Territory',
        'BRN' => 'Brunei Darussalam',
        'BGR' => 'Bulgaria',
        'BFA' => 'Burkina Faso',
        'BDI' => 'Burundi',
        'KHM' => 'Cambodia',
        'CMR' => 'Cameroon',
        'CPV' => 'Cape Verde',
        'CYM' => 'Cayman Islands',
        'CAF' => 'Central African Republic',
        'TCD' => 'Chad',
        'CHL' => 'Chile',
        'CHN' => 'China',
        'CXR' => 'Christmas Island',
        'CCK' => 'Cocos (Keeling) Islands',
        'COL' => 'Colombia',
        'COM' => 'Comoros',
        'COG' => 'Congo',
        'COD' => 'Congo (Republic)',
        'COK' => 'Cook Islands',
        'CRI' => 'Costa Rica',
        'CIV' => 'Côte d’Ivoire',
        'HRV' => 'Croatia',
        'CUB' => 'Cuba',
        'CUW' => 'Curaçao',
        'CYP' => 'Cyprus',
        'CZE' => 'Czech Republic',
        'DNK' => 'Denmark',
        'DJI' => 'Djibouti',
        'DMA' => 'Dominica',
        'DOM' => 'Dominican Republic',
        'ECU' => 'Ecuador',
        'EGY' => 'Egypt',
        'SLV' => 'El Salvador',
        'GNQ' => 'Equatorial Guinea',
        'ERI' => 'Eritrea',
        'EST' => 'Estonia',
        'ETH' => 'Ethiopia',
        'FLK' => 'Falkland Islands (Malvinas)',
        'FRO' => 'Faroe Islands',
        'FJI' => 'Fiji',
        'FIN' => 'Finland',
        'FRA' => 'France',
        'GUF' => 'French Guiana',
        'PYF' => 'French Polynesia',
        'ATF' => 'French Southern Territories',
        'GAB' => 'Gabon',
        'GMB' => 'Gambia',
        'GEO' => 'Georgia',
        'DEU' => 'Germany',
        'GHA' => 'Ghana',
        'GIB' => 'Gibraltar',
        'GRC' => 'Greece',
        'GRL' => 'Greenland',
        'GRD' => 'Grenada',
        'GLP' => 'Guadeloupe',
        'GUM' => 'Guam',
        'GTM' => 'Guatemala',
        'GGY' => 'Guernsey',
        'GIN' => 'Guinea',
        'GNB' => 'Guinea-Bissau',
        'GUY' => 'Guyana',
        'HTI' => 'Haiti',
        'HMD' => 'Heard Island and McDonald Islands',
        'VAT' => 'Holy See',
        'HND' => 'Honduras',
        'HKG' => 'Hong Kong',
        'HUN' => 'Hungary',
        'ISL' => 'Iceland',
        'IND' => 'India',
        'IDN' => 'Indonesia',
        'IRN' => 'Iran',
        'IRQ' => 'Iraq',
        'IRL' => 'Ireland',
        'IMN' => 'Isle of Man',
        'ISR' => 'Israel',
        'ITA' => 'Italy',
        'JAM' => 'Jamaica',
        'JPN' => 'Japan',
        'JEY' => 'Jersey',
        'JOR' => 'Jordan',
        'KAZ' => 'Kazakhstan',
        'KEN' => 'Kenya',
        'KIR' => 'Kiribati',
        'PRK' => 'Korea (North)',
        'KOR' => 'Korea (South)',
        'KWT' => 'Kuwait',
        'KGZ' => 'Kyrgyzstan',
        'LAO' => 'Laos',
        'LVA' => 'Latvia',
        'LBN' => 'Lebanon',
        'LSO' => 'Lesotho',
        'LBR' => 'Liberia',
        'LBY' => 'Libya',
        'LIE' => 'Liechtenstein',
        'LTU' => 'Lithuania',
        'LUX' => 'Luxembourg',
        'MAC' => 'Macao',
        'MKD' => 'Macedonia',
        'MDG' => 'Madagascar',
        'MWI' => 'Malawi',
        'MYS' => 'Malaysia',
        'MDV' => 'Maldives',
        'MLI' => 'Mali',
        'MLT' => 'Malta',
        'MHL' => 'Marshall Islands',
        'MTQ' => 'Martinique',
        'MRT' => 'Mauritania',
        'MUS' => 'Mauritius',
        'MYT' => 'Mayotte',
        'MEX' => 'Mexico',
        'FSM' => 'Micronesia',
        'MDA' => 'Moldova',
        'MCO' => 'Monaco',
        'MNG' => 'Mongolia',
        'MNE' => 'Montenegro',
        'MSR' => 'Montserrat',
        'MAR' => 'Morocco',
        'MOZ' => 'Mozambique',
        'MMR' => 'Myanmar',
        'NAM' => 'Namibia',
        'NRU' => 'Nauru',
        'NPL' => 'Nepal',
        'NLD' => 'Netherlands',
        'NCL' => 'New Caledonia',
        'NZL' => 'New Zealand',
        'NIC' => 'Nicaragua',
        'NER' => 'Niger',
        'NGA' => 'Nigeria',
        'NIU' => 'Niue',
        'NFK' => 'Norfolk Island',
        'MNP' => 'Northern Mariana Islands',
        'NOR' => 'Norway',
        'OMN' => 'Oman',
        'PAK' => 'Pakistan',
        'PLW' => 'Palau',
        'PSE' => 'Palestine',
        'PAN' => 'Panama',
        'PNG' => 'Papua New Guinea',
        'PRY' => 'Paraguay',
        'PER' => 'Peru',
        'PHL' => 'Philippines',
        'PCN' => 'Pitcairn',
        'POL' => 'Poland',
        'PRT' => 'Portugal',
        'PRI' => 'Puerto Rico',
        'QAT' => 'Qatar',
        'REU' => 'Réunion',
        'ROU' => 'Romania',
        'RUS' => 'Russian Federation',
        'RWA' => 'Rwanda',
        'BLM' => 'Saint Barthélemy',
        'SHN' => 'Saint Helena',
        'KNA' => 'Saint Kitts and Nevis',
        'LCA' => 'Saint Lucia',
        'MAF' => 'Saint Martin',
        'SPM' => 'Saint Pierre and Miquelon',
        'VCT' => 'Saint Vincent and the Grenadines',
        'WSM' => 'Samoa',
        'SMR' => 'San Marino',
        'STP' => 'Sao Tome and Principe',
        'SAU' => 'Saudi Arabia',
        'SEN' => 'Senegal',
        'SRB' => 'Serbia',
        'SYC' => 'Seychelles',
        'SLE' => 'Sierra Leone',
        'SGP' => 'Singapore',
        'SXM' => 'Sint Maarten (Dutch part)',
        'SVK' => 'Slovakia',
        'SVN' => 'Slovenia',
        'SLB' => 'Solomon Islands',
        'SOM' => 'Somalia',
        'ZAF' => 'South Africa',
        'SGS' => 'South Georgia & South Sandwich Islands',
        'SSD' => 'South Sudan',
        'ESP' => 'Spain',
        'LKA' => 'Sri Lanka',
        'SDN' => 'Sudan',
        'SUR' => 'Suriname',
        'SJM' => 'Svalbard and Jan Mayen',
        'SWZ' => 'Swaziland',
        'SWE' => 'Sweden',
        'CHE' => 'Switzerland',
        'SYR' => 'Syria',
        'TWN' => 'Taiwan',
        'TJK' => 'Tajikistan',
        'TZA' => 'Tanzania',
        'THA' => 'Thailand',
        'TLS' => 'Timor-Leste',
        'TGO' => 'Togo',
        'TKL' => 'Tokelau',
        'TON' => 'Tonga',
        'TTO' => 'Trinidad and Tobago',
        'TUN' => 'Tunisia',
        'TUR' => 'Turkey',
        'TKM' => 'Turkmenistan',
        'TCA' => 'Turks and Caicos Islands',
        'TUV' => 'Tuvalu',
        'UGA' => 'Uganda',
        'UKR' => 'Ukraine',
        'ARE' => 'United Arab Emirates',
        'GBR' => 'United Kingdom',
        'UMI' => 'United States Minor Outlying Islands',
        'URY' => 'Uruguay',
        'UZB' => 'Uzbekistan',
        'VUT' => 'Vanuatu',
        'VEN' => 'Venezuela (Bolivarian Republic of)',
        'VNM' => 'Viet Nam',
        'VGB' => 'Virgin Islands (British)',
        'VIR' => 'Virgin Islands (U.S.)',
        'WLF' => 'Wallis and Futuna',
        'ESH' => 'Western Sahara',
        'YEM' => 'Yemen',
        'ZMB' => 'Zambia',
        'ZWE' => 'Zimbabwe',
    );
    protected static $styledCountries = NULL;
    protected static $countryOptionData = NULL;
    
    protected static $countriesAndRegions = array(
        'CAN' => array(
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'MB' => 'Manitoba',
            'NB' => 'New Brunswick',
            'NL' => 'Newfoundland and Labrador',
            'NS' => 'Nova Scotia',
            'NT' => 'Northwest Territories',
            'NU' => 'Nunavut',
            'ON' => 'Ontario',
            'PE' => 'Prince Edward Island',
            'QC' => 'Quebec',
            'SK' => 'Saskatchewan',
            'YT' => 'Yukon'
        ),
        /*'CHN'=>array(
            'BJ' => 'Beijing Municipality',
            'TJ' => 'Tianjin Municipality',
            'HE' => 'Hebei Province',
            'SX' => 'Shanxi Province',
            'NM' => 'Inner Mongolia Autonomous Region',
            'LN' => 'Liaoning Province',
            'JL' => 'Jilin Province',
            'HL' => 'Heilongjiang Province',
            'SH' => 'Shanghai Municipality',
            'JS' => 'Jiangsu Province',
            'ZJ' => 'Zhejiang Province',
            'AH' => 'Anhui Province',
            'FJ' => 'Fujian Province',
            'JX' => 'Jiangxi Province',
            'SD' => 'Shandong Province',
            'HA' => 'Henan Province',
            'HB' => 'Hubei Province',
            'GD' => 'Guangdong Province',
            'GX' => 'Guangxi Zhuang Autonomous Region',
            'HI' => 'Hainan Province',
            'CQ' => 'Chongqing Municipality',
            'SC' => 'Sichaun Province',
            'GZ' => 'Guizhou Province',
            'YN' => 'Yunnan Province',
            'XZ' => 'Tibet Autonomous Region',
            'SN' => 'Shaanxi Province',
            'GS' => 'Gansu Province',
            'QH' => 'Qinghai Province',
            'NX' => 'Ningxia Hui Autonomous Region',
            'XJ' => 'Xinjiang Uyghur Autonomous Region',
            'HK' => 'Hong Kong Special Administrative Region',
            'MC' => 'Macau Special Administrative Region'
        ),*/
        'USA' => array(
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'IOWA',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming'
        )
    );

    public static function getRegionsByCountry($countryCode) {
        if(isset(static::$countriesAndRegions[$countryCode])){
            return static::$countriesAndRegions[$countryCode];
        }
        return array();
    }

    public static function getRegionCodesByCountry($countryCode) {
        if (isset(static::$countriesAndRegions[$countryCode])) {
            return array_keys(static::$countriesAndRegions[$countryCode]);
        }
        return array();
    }

    public static function dumpCountryPHPArray(){
        $data = json_decode(file_get_contents('config/definitions/countries.json'));
        echo '<pre>';
        echo 'array(' . "\n";
        $countryCount = count($data);
        foreach($data as $key => $countryData){
            echo "\t" . '\'' . $countryData->alpha3 . '\' => array(' . "\n";
            echo "\t\t" . '\'name\' => \'' . $countryData->name . '\',' . "\n";
            echo "\t\t" . '\'alpha2\' => \'' . $countryData->alpha2 . '\',' . "\n";
            echo "\t\t" . '\'alpha3\' => \'' . $countryData->alpha3 . '\',' . "\n";
            echo "\t\t" . '\'numeric\' => \'' . $countryData->numeric . '\',' . "\n";
            echo "\t\t" . '\'other\' => \'' . $countryData->other . '\'' . "\n";
            echo "\t" . ')';
            if($key != $countryCount-1){
                echo ',';
            }
            echo "\n";
        }
        echo ');';
        echo '</pre>';
    }
    
    public static function dumpCountiesVariable(){
        $data = include('config/definitions/country_definitions.php');
        echo '<pre>';
        echo 'array(' . "\n";
        foreach(static::$preferredCountryCodes as $code){
            $countryData = $data[$code];
            unset($data[$code]);
            if(static::dumpCountryOption($countryData)){
                echo ',';
                echo "\n";
            }
        }
        
        $ignoreCountryCodes = static::$ignoreCountryCodes;
        foreach($ignoreCountryCodes as $code){
            unset($data[$code]);
        }
        $otherCountryCodes = static::$otherCountryCodes;
        if(empty($otherCountryCodes)){
            foreach($data as $code => $countryData){
                if(static::dumpCountryOption($countryData)){
                    echo ',';
                    echo "\n";
                }
            }
        } else {
            foreach($otherCountryCodes as $code){
                $countryData = $data[$code];
                if(static::dumpCountryOption($countryData)){
                    echo ',';
                    echo "\n";
                }
            }
        }
        
        echo ');';
        echo '</pre>';
        die();
    }
    
    public static function dumpCountryOption($countryData){
        $code = $countryData['alpha3'];
        if(empty($code)){
            return false;
        }
        echo '\''. $code . '\' => \'' . $countryData['name'] . '\'';
        return true;
    }
    
    public static function forceRegionForCountryCode($countryCode){
        if($countryCode == 'CAN' || $countryCode == 'USA'){
            return true;
        }
        return false;
    }
    
    public static function getCountries() {
        return static::$countries;
    }
    
    public static function getCountryNameFromCode($countryCode) {
        if(isset(static::$countries[$countryCode])){
            return static::$countries[$countryCode];
        }
        return NULL;
    }
    
    public static function getCountryCodeFromName($countryName) {
        return array_search($countryName, static::$countries);
    }
    
    public static function getRegions($prefixRegions = true){
        $countriesAndRegions = static::$countriesAndRegions;
        if($prefixRegions){
            foreach($countriesAndRegions as $countryCode => $regions){
                foreach($regions as $regionCode => $region){
                    $countriesAndRegions[$countryCode][$countryCode . '_' . $regionCode] = $region;
                    unset($countriesAndRegions[$countryCode][$regionCode]);
                }
            }
        }
        return $countriesAndRegions;
    }
    
    public static function getRegionNameFromCode($countryCode, $regionCode, $returnNullIfNotFound = false) {
        if(isset(static::$countriesAndRegions[$countryCode])){
            $regions = static::$countriesAndRegions[$countryCode];
            if(isset($regions[$regionCode])){
                return $regions[$regionCode];
            }
        }
        if (!$returnNullIfNotFound) {
            return $regionCode;
        }
        return NULL;
    }
    
    public static function getRegionCodeFromName($countryCode, $regionName, $returnNullIfNotFound = false) {
        if (isset(static::$countriesAndRegions[$countryCode])) {
            $regions = static::$countriesAndRegions[$countryCode];
            if (!empty($regions)) {
                return array_search($regionName, $regions);
            }
        }
        if (!$returnNullIfNotFound) {
            return $regionName;
        }
        return NULL;
    }

    public static function determineDefaultRegionCode($countryCode = 'CAN') {
        switch ($countryCode) {
            case 'CHN':
                $region = 'BJ';
                break;
            case 'USA':
                $region = 'TX';
                break;
            case 'CAN':
            default:
                $region = 'BC';
                break;
        }
        return $region;
    }

    /**
     * 
     * @param AbstractPricingRegion $pricingRegion
     * @return String[] - an array of country names included in the pricing region, each with an optional nested array of region names included. An empty nested
     * array implies the entire country is included in the pricing region.
     */
    public static function getCountryAndRegionNameArrayByPricingRegion(AbstractPricingRegion $pricingRegion) {
        $results = array();
        $countryRefs = $pricingRegion->getCountryRefs();
        if (!empty($countryRefs)) {
            foreach ($countryRefs as $countryRef) {
                $countryName = static::getCountryNameFromCode($countryRef);
                $regionNames = array();
                $regionSearch = PricingRegionInclFactory::search()
                        ->filter('pricing_region_id', $pricingRegion->getProperty('id'))
                        ->filter('country_code', $countryRef)
                        ->filterNotNull('region_code')
                        ->groupBy('region_code');
                $regionsIncluded = $regionSearch->select();
               
                if (!empty($regionsIncluded)) {
 
                    foreach ($regionsIncluded as $regionIncluded) {
                        $regionRef = $regionIncluded->getProperty('region_code');
                        $regionNames[] = static::getRegionNameFromCode($regionIncluded->getProperty('country_code'), $regionRef);
                    }
                    $results[$countryName] = $regionNames;
                } else {
                    $results[$countryName] = NULL;
                }
            }
        }
        
        return $results;
    }
    
    public static function getCountryOptions($includeStyle = false) {
        $countries = static::getCountries();
        
        if(!$includeStyle){
            return $countries;
        }
        if(is_null(static::$styledCountries)){
            //@todo add flags?
            static::$styledCountries = $countries;
        }
        return static::$styledCountries;
    }
    
    public static function getCountryOptionData(){
        if(is_null(static::$countryOptionData)){
            $countries = static::getCountries();
            $countryOptionData = array();
            $previousWasPreferred = false;
            $previousCode = '';
            foreach($countries as $code => $name){
                $class = '';
                if(in_array($code, static::$preferredCountryCodes)){
                    $class = 'preferred';
                    $previousWasPreferred = true;
                } else {
                    if($previousWasPreferred && !empty($previousCode)){
                        $countryOptionData[$previousCode]['optionClass'] .= ' last';
                    }
                    $previousWasPreferred = false;
                }
                
                $forceRegion = 0;
                if(static::forceRegionForCountryCode($code)){
                    $forceRegion = 1;
                }
                $countryOptionData[$code] = array(
                    'optionClass' => $class,
                    'forceRegion' => $forceRegion
                );
                $previousCode = $code;
            }
            static::$countryOptionData = $countryOptionData;
        }
        return static::$countryOptionData;
    }
    
    public static function getRegionGroupOptions() {
        $regionsAndCountries = static::getRegions();
        $regions = array();
        foreach($regionsAndCountries as $countryCode => $countryRegions){
            $regions[static::getCountryNameFromCode($countryCode)] = $countryRegions;
        }
        return $regions;
    }
    
    public static function getRegionOptions($countryCode = NULL) {
        if (empty($countryCode)) {
            $regionsAndCountries = static::getRegions();
            $regions = array();
            foreach ($regionsAndCountries as $country => $regionsArray) {
                foreach ($regionsArray as $regionCode => $region) {
                    $regions[$regionCode] = $region;
                }
            }
        } else {
            $regions = static::getRegionsByCountry($countryCode);
        }
        return $regions;
    }

    public static function cleanRegionCode($regionCode){
        if (strpos($regionCode, '_') !== false) {
            $regionArray = explode('_', $regionCode);
            return end($regionArray);
        }
        
        return $regionCode;
    }
    
}
