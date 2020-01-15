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
        if(!isset($_SESSION['pending_alerts'])){
            $_SESSION['pending_alerts'] = array();
        }
        $alertId = $alert->getId();
        $_SESSION['pending_alerts'][$alertId] = array(
            'colour' => $alert->getColour(),
            'message' => $alert->getMessage()
        );
        return true;
    }
    
    public static function getNextAlertId(){
        if(is_null(static::$lastAlertId)){
            $largestAlertId = 0;
            if(isset($_SESSION['pending_alerts']) && !empty($_SESSION['pending_alerts'])){
                $largestAlertId = max(array_keys($_SESSION['pending_alerts']));
            }
            static::$lastAlertId = $largestAlertId;
        } else {
            static::$lastAlertId++;
        }
        return static::$lastAlertId;
    }
    
    public static function removeAlert($id){
        if(isset($_SESSION['pending_alerts']) && isset($_SESSION['pending_alerts'][$id])){
            unset($_SESSION['pending_alerts'][$id]);
            return true;
        }
        return false;
    }
    
    /**
     * @param type $id
     * @return \AbstractAlert
     */
    public static function getAlert($id){
        if(isset($_SESSION['pending_alerts']) && isset($_SESSION['pending_alerts'][$id])){
            $alertInfo = $_SESSION['pending_alerts'][$id];
            $alert = new Alert($alertInfo['message'], $alertInfo['colour']);
            $alert->setId($id);
            return $alert;
        }
        return NULL;
    }
    
    public static function getPendingAlerts(){
        $pendingAlerts = array();
        if(isset($_SESSION['pending_alerts'])){
            foreach($_SESSION['pending_alerts'] as $id => $info){
                $pendingAlert = static::getAlert($id);
                if($pendingAlert){
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
