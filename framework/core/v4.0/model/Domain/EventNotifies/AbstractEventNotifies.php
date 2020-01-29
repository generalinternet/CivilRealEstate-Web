<?php
/**
 * Description of AbstractEventNotifies
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractEventNotifies extends GI_Model {
    
    public function setPropertiesFromOtherModel(AbstractEventNotifies $otherModel) {
        $this->setProperty('event_id', $otherModel->getProperty('event_id'));
        $this->setProperty('role_id', $otherModel->getProperty('role_id'));
        $this->setProperty('context_role_id', $otherModel->getProperty('context_role_id'));
        $this->setProperty('user_id', $otherModel->getProperty('user_id'));
        $this->setProperty('table_name', $otherModel->getProperty('table_name'));
        $this->setProperty('item_id', $otherModel->getProperty('item_id'));
        return true;
    }
    
}