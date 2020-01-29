<?php
/**
 * Description of AbstractCurrencyFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    2.0.4
 */
abstract class AbstractCurrencyFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'currency';
    protected static $models = array();
    protected static $modelsRefKey = array();
    protected static $optionsArray = NULL;
    protected static $fallbackRates = array(
        'usd_cad' => 1.33,
        'cad_usd' => 0.75
    );
    protected static $cachedRates = array();
    protected static $allCurrencies = array();

    /**
     * @param type $typeRef
     * @param type $map
     * @return AbstractCurrency
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Currency($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    /**
     * @param type $typeRef
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $ref
     * @return AbstractCurrency
     */
    public static function getModelByRef($ref) {
        if(isset(static::$modelsRefKey[$ref])){
            return static::$modelsRefKey[$ref];
        }
        
        $result = static::search()
                ->filter('ref', $ref)
                ->select();
        
        if($result){
            static::$modelsRefKey[$ref] = $result[0];
            return static::$modelsRefKey[$ref];
        }
        return NULL;
    }

    public static function determineConversionRate(AbstractCurrency $sourceCurrency, AbstractCurrency $targetCurrency) {
        if ($sourceCurrency->getProperty('id') == $targetCurrency->getProperty('id')) {
            return 1;
        }
        $convRateKey = $sourceCurrency->getProperty('ref') . '_' . $targetCurrency->getProperty('ref');
        $cachedRate = static::getCachedRate($convRateKey);
        if (!empty($cachedRate)) {
            return $cachedRate;
        }
        
        
        $sourceCurrencyName = $sourceCurrency->getProperty('name');
        $targetCurrencyName = $targetCurrency->getProperty('name');
        //Get current exchange rate from external API (details @ http://fixer.io)
        $baseRef = strtoupper($sourceCurrencyName);
        $symbol = strtoupper($targetCurrencyName);
        $url = 'http://data.fixer.io/latest?access_key='.FIXER_IO_API_KEY.'&base=' . $baseRef . '&symbols=' . $symbol;
        $response = file_get_contents($url);
        if (!empty($response)) {
            $jsonArray = \GuzzleHttp\json_decode($response, true);
            if (isset($jsonArray['rates'][$symbol])) {
                $exchangeRate = $jsonArray['rates'][$symbol];
                static::cacheRate($convRateKey, $exchangeRate);
                return $exchangeRate;
            }
        }
        if(isset(static::$fallbackRates[$convRateKey])){
            return static::$fallbackRates[$convRateKey];
        }
        return NULL;
    }
    
    /** @return AbstractCurrency */
    public static function getDefaultCurrency(){
        $defaultCurrencyRef = ProjectConfig::getDefaultCurrencyRef();
        if (!empty($defaultCurrencyRef)) {
            $defaultCurrency = CurrencyFactory::getModelByRef($defaultCurrencyRef);
            if (!empty($defaultCurrency)) {
                return $defaultCurrency;
            }
        }
        return NULL;
    }
    
    protected static function getCachedRate($convRateKey) {
        if (apcu_exists($convRateKey)) {
            return apcu_fetch($convRateKey);
        }
        return NULL;
    }
    
    protected static function cacheRate($convRateKey, $value) {
        if (apcu_store($convRateKey, $value, 3600)) {
            return true;
        }
        return false;
    }

    /** @return AbstractCurrency[] */
    public static function &getAllCurrencies(){
        if(empty(static::$allCurrencies)){
            static::$allCurrencies = static::getAll();
        }
        return static::$allCurrencies;
    }

}
