<?php
/**
 * Description of AbstractWorkerScriptRunTime
 * Place methods here that will be part of the module, and used for all applications
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0
 */
abstract class AbstractWorkerScriptRunTime extends GI_Model {
    
    public function endScript($message = NULL){
        $nowObj = new DateTime();
        $now = GI_Time::formatDateTime($nowObj);
        $this->setProperty('end_time', $now);
        $this->setProperty('message', $message);
        return $this->save();
    }
    
}
