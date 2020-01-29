<?php

abstract class AbstractTagContactSubcat extends AbstractTag {

    public function getIsIndexViewable() {
        if (Permission::verifyByRef('view_contact_subcats')) {
            return true;
        }
        return false;
    }
    
    public function getViewTitle($plural = true) {
        if (!$plural) {
            return 'Subcategory';
        }
        return 'Subcategories';
    }

}
