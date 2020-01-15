<?php
/**
 * Description of AbstractTagInventory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractTagInventory extends AbstractTag {

    public function getIsIndexViewable() {
        if (!Permission::verifyByRef('view_inventory_tag_index')) {
            return false;
        }
        return true;
    }
    
    public function getViewTitle($plural = false) {
        $typeTitle = $this->getTypeTitle();
        $title = $typeTitle . ' Tag';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    public function getFormView(GI_Form $form) {
        return new TagInventoryFormView($form, $this);
    }

    public function getIsAddable() {
        if (!Permission::verifyByRef('add_inventory_tags')) {
            return false;
        }
        return true;
    }

    public function getIsEditable() {
        if (!Permission::verifyByRef('edit_inventory_tags')) {
            return false;
        }
        return true;
    }

}
