<?php
/**
 * Description of AbstractTimeIntervalFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractTimeIntervalFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'time_interval';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractTimeInterval
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'project':
                $model = new TimeIntervalProject($map);
                break;
            case 'interval':
            default:
                $model = new TimeInterval($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'interval':
                $typeRefs = array('interval');
                break;
            case 'project':
                $typeRefs = array('project');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return AbstractTimeInterval
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    public static function getPTypeRef($typeRef) {
        $typeRefsArray = static::getTypeRefArray($typeRef);
        $numberOfRefs = sizeof($typeRefsArray);
        if ($numberOfRefs > 1) {
            $pTypeRef = $typeRefsArray[$numberOfRefs - 2];
            return $pTypeRef;
        } else {
            $pTypeRef = 'interval';
        }
        return $pTypeRef;
    }
/**
 * 
 * @param GI_Model $model
 * @param type $startDate
 * @param type $endDate
 * @param type $intervalTypeRef
 * @return AbstractTimeInterval[]
 */
    public static function getTimeIntervalsByLinkedModel(GI_Model $model, $startDate = NULL, $endDate = NULL, $tags = array(), $intervalTypeRef = 'interval') {
        $itemId = $model->getProperty('id');
        $tableName = $model->getTableName();
        $intervalTableName = dbConfig::getDbPrefix() . 'time_interval';
        $intervalSearch = TimeIntervalFactory::search()
                ->join('item_link_to_time_interval', 'time_interval_id', $intervalTableName, 'id', 'til')
                ->filter('til.table_name', $tableName)
                ->filter('til.item_id', $itemId);

        if (!empty($tags)) {
            $intervalSearch->join('item_link_to_tag', 'item_id', $intervalTableName, 'id', 'iltt')
                    ->filter('iltt.table_name', 'time_interval');
            $tagsCount = count($tags);
            if ($tagsCount == 1) {
                $intervalSearch->filter('iltt.tag_id', $tags[0]->getProperty('id'));
            } else {
                $intervalSearch->andIf();
                $intervalSearch->filterGroup();
                for ($i = 0; $i < $tagsCount; $i++) {
                    if ($i != 0) {
                        $intervalSearch->orIf();
                    }
                    $intervalSearch->filter('iltt.tag_id', $tags[$i]->getProperty('id'));
                }
                $intervalSearch->closeGroup();
                $intervalSearch->andIf();
            }
        }
        if (!empty($startDate) && !empty($endDate)) {
            $startDateObject = new DateTime($startDate);
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
            $endDateObject = new DateTime($endDate);
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
            $intervalSearch->andIf();
            $intervalSearch->filterGroup();
            $intervalSearch->filterGreaterThan('end_date', $startDateSearchable);
            $intervalSearch->orIf();
            $intervalSearch->filterLessThan('start_date', $endDateSearchable);
            $intervalSearch->closeGroup();
            $intervalSearch->andIf();
        }
        if (!empty($intervalTypeRef)) {
            $intervalSearch->filterByTypeRef($intervalTypeRef);
        }
        
        return $intervalSearch->select();
    }
    
    public static function linkTimeIntervalToModel(GI_Model $model, TimeInterval $timeInterval) {
        $itemId = $model->getProperty('id');
        $tableName = $model->getTableName();
        $timeIntervalId = $timeInterval->getProperty('id');
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $searchArray = array(
            'item_id'=>$itemId,
            'table_name'=>$tableName,
            'time_interval_id'=>$timeIntervalId
        );
        $existingLinkArray = $defaultDAOClass::getByProperties('item_link_to_time_interval', $searchArray);
        if (!empty($existingLinkArray)) {
            return true;
        }
        $searchArray['status'] = '0';
        $softDeletedLinkArray = $defaultDAOClass::getByProperties('item_link_to_time_interval', $searchArray);
        if (!empty($softDeletedLinkArray)) {
            $softDeletedLink = $softDeletedLinkArray[0];
            $softDeletedLink->setProperty('status', 1);
            if (!$softDeletedLink->save()) {
                return true;
            }
        }
        $newLink = new $defaultDAOClass('item_link_to_time_interval');
        $newLink->setProperty('item_id', $itemId);
        $newLink->setProperty('table_name', $tableName);
        $newLink->setProperty('time_interval_id', $timeIntervalId);
        if ($newLink->save()) {
            return true;
        }
        return false;
    }
    
    public static function unlinkTimeIntervalFromModel(GI_Model $model, TimeInterval $timeInterval) {
        $itemId = $model->getProperty('id');
        $tableName = $model->getTableName();
        $timeIntervalId = $timeInterval->getProperty('id');
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $searchArray = array(
            'item_id' => $itemId,
            'table_name' => $tableName,
            'time_interval_id' => $timeIntervalId
        );
        $existingLinkArray = $defaultDAOClass::getByProperties('item_link_to_time_interval', $searchArray);
        if (empty($existingLinkArray)) {
            return true;
        }
        foreach ($existingLinkArray as $link) {
            if (!$link->softDelete()) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 
     * @param AbstractTimeInterval[] $timeIntervals
     * @param array $mergeEventArray
     * @return type
     */
    public static function buildCalendarEvents($timeIntervals, $mergeEventArray = array()){
        if (empty($timeIntervals)) {
            return $mergeEventArray;
        }
        $eventArray = $mergeEventArray;
        foreach ($timeIntervals as $timeInterval) {
            if ($timeInterval->isSingleDay()) {
                //Single-day TimeInterval
                $eventArray[] = $timeInterval->convertModelToCalendarEvent();
            } else {
                //Multi-day TimeInterval
                $eventArray = array_merge($eventArray, $timeInterval->convertModelToCalendarMuliDayEvent());
            } 
        }
        return $eventArray;
    }
    
    public static function addStartDateEndDateFiltersToDataSearch($dataSearch, $startDate = NULL, $endDate = NULL) {
        if (!empty($startDate) && !empty($endDate)) {
            $startDateTime = GI_Time::formatDateForDisplay($startDate, 'Y-m-d 00:00:00');
            $endDateTime = GI_Time::formatDateForDisplay($endDate, 'Y-m-d 12:59:59');
            $dataSearch->filterGroup()
                    //start <= Search start & end >= Search start
                    ->orIf()
                    ->filterGroup()
                    ->filterLessOrEqualTo('start_date_time', $startDateTime)  
                    ->andIf()
                    ->filterGreaterOrEqualTo('end_date_time', $startDateTime) 
                    ->closeGroup()
                    ->orIf()
                    
                    //start <= Search end & end >= Search end
                    ->filterGroup()
                    ->filterLessOrEqualTo('start_date_time', $endDateTime)    
                    ->andIf()
                    ->filterGreaterOrEqualTo('end_date_time', $endDateTime)
                    ->closeGroup()
                    ->orIf()
                    
                    //start <= Search start & end >= Search end
                    ->filterGroup()
                    ->filterLessOrEqualTo('start_date_time', $startDateTime)  
                    ->andIf()
                    ->filterGreaterOrEqualTo('end_date_time', $endDateTime)
                    ->closeGroup()
                    ->orIf()
                    
                    //start >= Search start & end <= Search end
                    ->filterGroup()
                    ->filterGreaterOrEqualTo('start_date_time', $startDateTime)  
                    ->andIf()
                    ->filterLessOrEqualTo('end_date_time', $endDateTime)
                    ->closeGroup()
                    
                    ->closeGroup();
        } else if (!empty($startDate) && empty($endDate)) {
            $startDateTime = GI_Time::formatDateForDisplay($startDate, 'Y-m-d 00:00:00');
            $dataSearch->filterGreaterOrEqualTo('end_date_time', $startDateTime);
        } else if (empty($startDate) && !empty($endDate)) {
            $endDateTime = GI_Time::formatDateForDisplay($endDate, 'Y-m-d 12:59:59');
            $dataSearch->filterLessOrEqualTo('start_date_time', $endDateTime);
        }
        
        return $dataSearch;
    }
    
    /**
     * @param string $startDate
     * @param string $endDate
     * @param AbstractContact[] $contactModels
     * @return TimeIterval[]
     */
    public static function getScheduledTimeItervalsFromStartDateEndDateContacts($startDate, $endDate, $contactModels){
        $contactIdArray = array();
        foreach ($contactModels as $contactModel){
            $contactIdArray[] = $contactModel->getId();
        }
        $dataSearch = static::search()
                ->join('contact_scheduled', 'time_interval_id', static::getDbPrefix() . static::getTableName(), 'id', 'CS')
                ->filterIn('CS.contact_id', $contactIdArray);
        static::addStartDateEndDateFiltersToDataSearch($dataSearch, $startDate, $endDate);
                
        return $dataSearch->select();      
    }
    
    /**
     * @param AbstractTimeInterval $timeInterval
     * @param boolean $uniqueAssignees
     * @return GI_DataSearch
     */
    public static function getFullRangeSearch(AbstractTimeInterval $timeInterval, $uniqueAssignees = true){
        $sourceId = $timeInterval->getProperty('source_ti_id');
        $assignees = $timeInterval->getScheduledContacts();
        if(empty($sourceId)){
            $sourceId = $timeInterval->getId();
        }
        $search = static::search()
                ->filterGroup()
                    ->filter('source_ti_id', $sourceId)
                    ->orIf()
                    ->filter('id', $sourceId)
                ->closeGroup()
                ->andIf();
        if($uniqueAssignees){
            $timeIntervalTable = $search->prefixTableName('time_interval');
            $search->join('contact_scheduled', 'time_interval_id', $timeIntervalTable, 'id', 'CS');
            $assigneeIds = array();
            foreach($assignees as $assignee){
                $assigneeIds[] = $assignee->getId();
                $search->filterIn('CS.contact_id', $assigneeIds);
            }
        }
        return $search;
    }
    
    public static function getConflicts(AbstractTimeInterval $timeInterval){
        $assignees = $timeInterval->getScheduledContacts();
        if(empty($assignees)){
            return NULL;
        }
        $search = static::search();
        $timeIntervalTable = $search->prefixTableName('time_interval');
        $search->join('contact_scheduled', 'time_interval_id', $timeIntervalTable, 'id', 'CS');
        $assigneeIds = array();
        foreach($assignees as $assignee){
            $assigneeIds[] = $assignee->getId();
            $search->filterIn('CS.contact_id', $assigneeIds);
        }
        $tiId = $timeInterval->getId();
        if($tiId){
            $search->filterNotEqualTo('id', $tiId);
        }
        
        $start = $timeInterval->getStartDateTime();
        $end = $timeInterval->getEndDateTime();
        
        $selectedDays = $timeInterval->getDaysArray(true);
        $search->filterGroup()
                ->orIf();
        foreach($selectedDays as $selectedDay){
            $search->filter('day_' . $selectedDay, 1);
        }
        
        $search->closeGroup()
                ->andIf();
        
        $search->filterGroup()
                    ->filterGroup()
                        ->filterGreaterThan('end_date_time', $start)
                        ->andIf()
                        ->filterLessOrEqualTo('end_date_time', $end)
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                        ->filterLessThan('start_date_time', $end)
                        ->andIf()
                        ->filterGreaterOrEqualTo('start_date_time', $start)
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                        ->filterLessThan('start_date_time', $start)
                        ->andIf()
                        ->filterGreaterThan('end_date_time', $end)
                    ->closeGroup()
                    ->orIf()
                ->closeGroup()
                ->andIf()
                ->groupBy('id')
                ->orderBy('start_date_time', 'ASC');
        
        return $search->select();
    }
    
}
