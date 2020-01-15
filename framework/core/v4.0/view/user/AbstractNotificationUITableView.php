<?php
/**
 * Description of AbstractNotificationUITableView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
class AbstractNotificationUITableView extends AbstractUITableView {
    
    protected function getRowClass($model) {
        /*@var $model AbstractNotification*/
        $rowClass = parent::getRowClass($model);
        if($model->getProperty('viewed')){
            $rowClass .= ' viewed';
        }
        return $rowClass;
    }
    
}
