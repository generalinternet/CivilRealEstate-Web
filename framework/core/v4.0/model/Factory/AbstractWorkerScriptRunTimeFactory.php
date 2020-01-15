<?php
/**
 * Description of AbstractWorkerScriptRunTimeFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.3
 */
class AbstractWorkerScriptRunTimeFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 'worker_script_run_time';
    protected static $models = array();
    
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new WorkerScriptRunTime($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * 
     * @param type $typeRef - can be empty string
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * 
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return WorkerScriptRunTime
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param string $scriptName
     * @param boolean $adjustAttemptCount
     * @param string $lockedTimeFrame
     * @return WorkerScriptRunTime|boolean
     */
    public static function attemptToRunScript($scriptName, $adjustAttemptCount = true, $lockedTimeFrame = '12 hours', $forceReturnModel = false){
        if(empty($scriptName)){
            return false;
        }
        $lockedTimeObj = new DateTime();
        $lockedTimeObj->sub(DateInterval::createFromDateString($lockedTimeFrame));
        $lockedTime = GI_Time::formatDateTime($lockedTimeObj);
        $results = static::search()
                ->filter('script', $scriptName)
                ->filterGreaterOrEqualTo('start_time', $lockedTime)
                ->orderBy('start_time', 'DESC')
                ->select();
        
        if($results){
            if($adjustAttemptCount){
                $workerScriptRunTime = $results[0];
                $attemptedRunCount = $workerScriptRunTime->getProperty('attempted_run_count');
                $newAttemptedRunCount = $attemptedRunCount + 1;
                $workerScriptRunTime->setProperty('attempted_run_count', $newAttemptedRunCount);
                $workerScriptRunTime->save();
            }
            if(!$forceReturnModel){
                return false;
            } else {
                return $workerScriptRunTime;
            }
        } else {
            $workerScriptRunTime = WorkerScriptRunTimeFactory::buildNewModel();
            $workerScriptRunTime->setProperty('script', $scriptName);
            $nowObj = new DateTime();
            $now = GI_Time::formatDateTime($nowObj);
            $workerScriptRunTime->setProperty('start_time', $now);
            $workerScriptRunTime->setProperty('attempted_run_count', 1);
            if($workerScriptRunTime->save()){
                return $workerScriptRunTime;
            }
        }
        return false;
    }
    
    /**
     * @param string $scriptName
     * @return WorkerScriptRunTime
     */
    public static function getLastRunTime($scriptName){
        $results = static::search()
                ->filter('script', $scriptName)
                ->setItemsPerPage(1)
                ->orderBy('end_time', 'DESC')
                ->select();
        if($results){
            return $results[0];
        }
        return NULL;
    }

}
