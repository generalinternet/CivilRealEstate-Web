<?php
/**
 * Description of AbstractAlertService
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAlertService extends GI_Service {
    
    protected static $lastAlertId = NULL;
    
    public static function buildNewAlert($message, $colour = NULL){
        $newAlert = new Alert($message, $colour);
        return static::addAlert($newAlert);
    }
    
    public static function addAlert(AbstractAlert $alert) {
        SessionService::setValue(array(
            'pending_alerts',
            $alert->getId(),
        ), array(
            'colour' => $alert->getColour(),
            'message' => $alert->getMessage()
        ));
        return true;
    }
    
    public static function getNextAlertId(){
        if(is_null(static::$lastAlertId)){
            $largestAlertId = 0;
            $pendingAlerts = SessionService::getValue(array(
                'pending_alerts'
            ));
            if (!empty($pendingAlerts)) {
                $largestAlertId = max(array_keys($pendingAlerts));
            }
            static::$lastAlertId = $largestAlertId;
        } else {
            static::$lastAlertId++;
        }
        return static::$lastAlertId;
    }
    
    public static function removeAlert($id){
        SessionService::unsetValue(array(
            'pending_alerts',
            $id,
        ));
        return false;
    }
    
    /**
     * @param type $id
     * @return \AbstractAlert
     */
    public static function getAlert($id){
        $alertInfo = SessionService::getValue(array(
                    'pending_alerts',
                    $id
        ));
        if (!empty($alertInfo)) {
            $alert = new Alert($alertInfo['message'], $alertInfo['colour']);
            $alert->setId($id);
            return $alert;
        }
        return NULL;
    }

    public static function getPendingAlerts() {
        $pendingAlerts = array();
        $pendingAlertIds = SessionService::getValue('pending_alerts');
        if (!empty($pendingAlertIds)) {
            foreach ($pendingAlertIds as $id => $info) {
                $pendingAlert = static::getAlert($id);
                if ($pendingAlert) {
                    $pendingAlerts[$id] = $pendingAlert;
                }
            }
        }
        return $pendingAlerts;
    }

    public static function getMessageFromCode($code){
        $msg = '';
        switch($code){
            default:
                //@todo add default messages here
                break;
        }
        return $msg;
    }
    
}
