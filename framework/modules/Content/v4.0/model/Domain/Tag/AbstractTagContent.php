<?php
/**
 * Description of AbstractTagContent
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractTagContent extends AbstractTag {

    public function getIsIndexViewable() {
        if (!Permission::verifyByRef('view_content_tag_index')) {
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

    public function getIsAddable() {
        if (!Permission::verifyByRef('add_content_tags')) {
            return false;
        }
        return true;
    }

    public function getIsEditable() {
        if (!Permission::verifyByRef('edit_content_tags')) {
            return false;
        }
        return true;
    }

}
