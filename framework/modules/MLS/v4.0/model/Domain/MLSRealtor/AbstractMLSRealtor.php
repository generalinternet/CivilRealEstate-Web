<?php
/**
 * Description of AbstractMLSRealtor
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSRealtor extends GI_Model {
    
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
    
    public function getLogin(){
        return $this->getProperty('login');
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
    
    public function getOfficeId(){
        return $this->getProperty('office_id');
    }
    
    public function getRealtorId(){
        return $this->getProperty('realtor_id');
    }
    
}
