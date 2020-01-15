<?php
/**
 * Description of AbstractNoteSystem
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractNoteSystem extends AbstractNote {
    
    public function getIsAddable() {
        return false;
    }
    
    public function getIsViewable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('view_system_notes')){
            return true;
        }
        return false;
    }
    
    public function getIsEditable() {
        return false;
    }
    
    public function getIsDeleteable(){
        return false;
    }
    
}
