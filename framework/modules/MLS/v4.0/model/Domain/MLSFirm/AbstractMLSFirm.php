<?php
/**
 * Description of AbstractMLSFirm
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSFirm extends GI_Model {
    
    public function save() {
        if(RETS_MODIFY_ROWS){
            return parent::save();
        }
        return false;
    }
    
    public function softDelete(){
        if(RETS_MODIFY_ROWS){
            return parent::softDelete();
        }
        return false;
    }
    
    public function getName(){
        return $this->getProperty('name');
    }
    
    public function getShortName(){
        return $this->getProperty('short_name');
    }
    
    public function getPhone(){
        return $this->getProperty('phone');
    }
    
    public function getEmail(){
        return $this->getProperty('email');
    }
    
    public function getURL(){
        return $this->getProperty('url');
    }
    
    public function getCode(){
        return $this->getProperty('code');
    }
    
}
