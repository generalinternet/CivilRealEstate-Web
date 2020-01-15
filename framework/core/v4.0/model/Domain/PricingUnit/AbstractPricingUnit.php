<?php
/**
 * Description of AbstractPricingUnit
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.4
 */
abstract class AbstractPricingUnit extends GI_Model {
    
    protected $useAbbrAsTerm = false;
    
    public function getTitle($plural = false, $lowercase = false){
        $column = 'title';
        if($plural){
            $column = 'pl_title';
        }
        $unitTitle = $this->getProperty($column);
        if($lowercase){
            $unitTitle = strtolower($unitTitle);
        }
        return $unitTitle;
    }
    
    public function getAbbr($long = true){
        $column = 'long_ref';
        if(!$long){
            $column = 'ref';
        }
        return $this->getProperty($column);
    }
    
    public function getUnitType(){
        return $this->getTypeRef();
    }
    
    public function convertFromThousandUnit($val, $withUnits = true, &$realUnit = NULL){
        $ref = $this->getProperty('ref');
        if (substr($ref, 0, 1) === 'm') {
            $realRef = substr($ref, 1);
            $realUnitResult = PricingUnitFactory::search()
                    ->filter('ref', $realRef)
                    ->select();
            if($realUnitResult){
                $realUnit = $realUnitResult[0];
                $realVal = $val * 1000;
            }
        }
        
        if(empty($realUnit)){
            $realUnit = $this;
            $realVal = $val;
        }
        
        if($withUnits){
            $realVal = $realVal . $realUnit->getProperty('ref');
        }
        return $realVal;
    }
    
    public function getQtyWithUnitsString($qty, $lowercase = true){
        $string = $qty;
        $column = 'pl_title';
        if($qty == 1){
            $column = 'title';
        }
        $unitString = $this->getProperty($column);
        if($lowercase){
            $unitString = strtolower($unitString);
        }
        
        $string .= ' ' . $unitString;
        return $string;
    }

    public function convertQtyToThis($currentQty, AbstractPricingUnit $currentPricingUnit) {
        if (!($currentPricingUnit->getTypeModel()->getProperty('id') == $this->getTypeModel()->getProperty('id'))) {
            return NULL;
        }
        if ($this->getProperty('ref') === $currentPricingUnit->getProperty('ref')) {
            return $currentQty;
        }
        return NULL;
    }
    
    public function getTerm($plural = false, $lowercase = false){
        $title = $this->getTitle($plural, $lowercase);
        if($this->useAbbrAsTerm){
            $term = $this->getAbbr();
            return $term;
        }
        return $title;
    }
    
    /**
     * @param float $count
     * @param AbstractPricingUnit $newPricingUnit
     * @return float
     */
    public function getSmallestUnitCount($count, AbstractPricingUnit &$newPricingUnit = NULL){
        $newUnitCount = GI_Measurement::getSmallestUnitCount($count, $this, $newPricingUnit);
        
        return $newUnitCount;
    }
    
    /**
     * @param float $count
     * @return string
     */
    public function getSmallestUnitCountForDisplay($count){
        $newPricingUnit = NULL;
        $newUnitCount = $this->getSmallestUnitCount($count, $newPricingUnit);
        
        if(!$newPricingUnit){
            $newPricingUnit = $this;
        }

        $plural = true;
        if($newUnitCount == 1){
            $plural = false;
        }

        return $newUnitCount . ' ' . $newPricingUnit->getTitle($plural);
    }

}
