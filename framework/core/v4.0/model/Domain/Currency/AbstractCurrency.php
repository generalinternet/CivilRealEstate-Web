<?php
/**
 * Description of AbstractCurrency
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.1
 */
abstract class AbstractCurrency extends GI_Model {

    /** Form submit handler
     *
     * @param GI_Form $form form Class
     * @return boolean false if not submitted or failed to save
     */
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $currencyId = $this->getProperty('id');
            $sysExRateToUSD = filter_input(INPUT_POST, 'sys_ex_rate_to_usd_' . $currencyId);
            $this->setProperty('sys_ex_rate_to_usd', $sysExRateToUSD);
            return $this->save();
        }
        return false;
    }

    /** Converts $currency AbstractCurrency value to USD value
     *
     * @param float $value value of AbstractCurrency
     * @param AbstractCurrency $currency currency interface converted from  
     * @return float
     * @deprecated since 2.1 - use Currency->convertToThis() instead
     */
    public static function convertValueToUSD($value, AbstractCurrency $currency) {
        $sysExRateToUSD = (float) $currency->getProperty('currency.sys_ex_rate_to_usd');
        $exchangedValue = $value * $sysExRateToUSD;
        return $exchangedValue;
    }

    /** Converts USD value to AbstractCurrency value
     *
     * @param float $value value of USD
     * @param AbstractCurrency $currency currency interface converted to  
     * @return float
     * @deprecated since 2.1 - use Currency->convertToThis() instead
     */
    public static function convertValueFromUSD($value, AbstractCurrency $currency) {
        $sysExchangeRateToUSD = $currency->getProperty('currency.sys_ex_rate_to_usd');
        $exchangeRate = (1 / $sysExchangeRateToUSD);
        return $value * $exchangeRate;
    }

    public function getAmountWithSymbols($amount, $includeCurrencyName = false){
        $currencyName = $this->getProperty('currency.name');
        $currencySymbol = $this->getProperty('currency.symbol');
        $amountWithSymbols = $currencySymbol . GI_StringUtils::formatMoney($amount);
        if($includeCurrencyName){
            $amountWithSymbols .= ' ' . $currencyName;
        }
        return $amountWithSymbols;
    }
    
    /**
     * @param float $value
     * @param AbstractCurrency $sourceCurrency
     * @return float
     */
    public function convertToThis($value, AbstractCurrency $sourceCurrency) {
        $exchangeRate = (float) CurrencyFactory::determineConversionRate($sourceCurrency, $this);
        $value = (float) $value;
        return $value * $exchangeRate;
    }
    
    public function formatAmountForDisplay($amount, $showCurrency = false, $withBrackets = false){
        $total = $this->getProperty('symbol');
        $total .= GI_StringUtils::formatMoney($amount);
        if($showCurrency && !GI_CSV::csvExporting()){
            $total .= ' ';
            if($withBrackets){
                $total .= ' (';
            }
            $total .= $this->getProperty('name');
            if($withBrackets){
                $total .= ')';
            }
        }
        return $total;
    }
    
    public function getLongName($plural = true) {
        if ($plural) {
            return $this->getProperty('long_name_pl');
        }
        return $this->getProperty('long_name');
    }
    
}
