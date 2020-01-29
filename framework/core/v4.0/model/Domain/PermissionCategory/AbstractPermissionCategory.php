<?php
/**
 * Description of AbstractPermissionCategory
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractPermissionCategory extends GI_Model {
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
}