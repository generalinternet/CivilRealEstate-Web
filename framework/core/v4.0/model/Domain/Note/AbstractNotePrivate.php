<?php
/**
 * Description of AbstractNotePrivate
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractNotePrivate extends AbstractNote {
    
    public function getIsAddable() {
        if(Permission::verifyByRef('add_private_notes')){
            return true;
        }
        return false;
    }
    
    public function getIsViewable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('view_private_notes')){
            return true;
        }
        return false;
    }

    public function getIsEditable() {
        if (!$this->wasPostedRecentlyEnoughToEdit()) {
            return false;
        }
        if ($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('edit_private_notes')) {
            return true;
        }
        return false;
    }

    public function getIsDeleteable(){
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('delete_private_notes')){
            return true;
        }
        return false;
    }
    
}
