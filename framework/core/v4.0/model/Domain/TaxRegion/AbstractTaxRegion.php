<?php
/**
 * Description of AbstractTaxRegion
 * Place methods here that will be part of the module, and used for all applications
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.1
 * @deprecated - since Oct. 2018
 */
abstract class AbstractTaxRegion extends GI_Model {
    
    /** @var AbstractTax */
    protected $tax = NULL;
    /** @var AbstractRegion */
    protected $region = NULL;
    
    public static function getFormOptionsByCodes($countryCode, $regionCode) {
        $taxRegionArray = TaxRegionFactory::getTaxRegionsByCodes($countryCode, $regionCode);
        if ($taxRegionArray) {
            $returnArray = array();
            foreach ($taxRegionArray as $taxRegion) {
                $taxRegionId = $taxRegion->getProperty('id');
                $taxRate = $taxRegion->getProperty('rate');
                $taxId = $taxRegion->getProperty('tax_id');
                $taxModel = TaxFactory::getModelById($taxId);
                $taxTitle = $taxModel->getProperty('title');
                $taxRatePercentage = $taxRate * 100;
                $taxLabel = $taxTitle . ' (' . $taxRatePercentage . '%)';
                $returnArray[$taxRegionId] = $taxLabel;
            }
            return $returnArray;
        }
        return NULL;
    }

    /** @return AbstractTax */
    public function getTax(){
        if(empty($this->tax)){
            $taxId = $this->getProperty('tax_id');
            $this->tax = TaxFactory::getModelById($taxId);
        }
        return $this->tax;
    }
    
    public function getDefaultOn(){
        $tax = $this->getTax();
        if($tax){
            return $tax->getProperty('default_on');
        }
        return NULL;
    }
    
    /** @var AbstractRegion */
    public function getRegion() {
        if (empty($this->region)) {
            $regionId = $this->getProperty('region_id');
            $this->region = RegionFactory::getModelById($regionId);
        }
        return $this->region;
    }

    public function getTaxRef(){
        $tax = $this->getTax();
        if($tax){
            return $tax->getProperty('ref');
        }
        return NULL;
    }
    
    public function getRate($raw = false){
        $taxRate = $this->getProperty('rate');
        if($raw){
            return $taxRate;
        }
        $taxRatePercentage = $taxRate * 100;
        return $taxRatePercentage;
    }
    
    public function getTaxTitle($withRate = false) {
        $tax = $this->getTax();
        if($tax){
            $taxTitle = $tax->getProperty('title');
            if(!$withRate){
                return $taxTitle;
            } else {
                $taxRatePercentage = $this->getRate();
                $taxLabel = $taxTitle . ' (' . $taxRatePercentage . '%)';
                return $taxLabel;
            }
        }
        
        return NULL;
    }

    public function calculateTaxAmount($amount) {
        $rate = $this->getProperty('tax_link_to_region.rate');
        return $amount * $rate;
    }

    public function getRegionAndTaxTitle($withRate = false) {
        $taxId = $this->getProperty('tax_id');
        $taxModel = TaxFactory::getModelById($taxId);
        $regionId = $this->getProperty('region_id');
        $regionModel = RegionFactory::getModelById($regionId);
        $title = '';
        if (!empty($taxModel) && !empty($regionModel)) {
            
            $taxTitle = $taxModel->getProperty('title');
            $countryCode = $regionModel->getProperty('country_code');
            $regionTitle = $regionModel->getProperty('region_code');
            $title .= $taxTitle . ' (' . $countryCode . ' - ' . $regionTitle;
            if ($withRate) {
                $taxRate = $this->getProperty('rate');
                $title .= ', ' . $taxRate * 100 . '%';
            }
            $title .= ')';
        }
        return $title;
    }

}
