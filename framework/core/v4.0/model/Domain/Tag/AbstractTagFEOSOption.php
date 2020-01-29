<?php
/**
 * Description of AbstractTagFEOSOption
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractTagFEOSOption extends AbstractTag {

    public function getIsIndexViewable() {
        //if someone can view the feos index they should be able to also view the group index
        if (!Permission::verifyByRef('view_feos_index')) {
            return false;
        }
        return true;
    }
    
    public function getViewTitle($plural = false) {
        $typeTitle = $this->getTypeTitle();
        $title = $typeTitle . ' Group';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
//    public function getFormView(GI_Form $form) {
//        return new TagInventoryFormView($form, $this);
//    }

    public function getIsAddable() {
        if (!Permission::verifyByRef('add_feoses')) {
            return false;
        }
        return true;
    }

    public function getIsEditable() {
        if (!Permission::verifyByRef('edit_feoses')) {
            return false;
        }
        return true;
    }

}
