<?php
/**
 * Description of AbstractMLSSubArea
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSSubArea extends GI_Model {
    
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
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
    public function getRef(){
        return $this->getProperty('ref');
    }
    
}
