<?php
/**
 * Description of AbstractFOBShippingType
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractFOBShippingType extends GI_Model {
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
    public function getRef(){
        return $this->getProperty('ref');
    }
    
    public function isActive(){
        if($this->getProperty('active')){
            return true;
        }
        return false;
    }
    
}
