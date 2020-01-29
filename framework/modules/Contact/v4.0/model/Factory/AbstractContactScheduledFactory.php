<?php
/**
 * Description of AbstractContactScheduledFactory
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractContactScheduledFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_scheduled';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractContactScheduled
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new ContactScheduled($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    /**
     * @param string $typeRef
     * @return string[]
     */
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * 
     * @param integer $id
     * @param boolean $force
     * @return AbstractContactScheduled
     */
    public static function getModelById($id, $force = false) {
       return parent::getModelById($id, $force);
    }
    
    /**
     * @param string $typeRef
     * @return AbstractContactScheduled
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param AbstractContact $contact
     * @param AbstractTimeInterval $timeInterval
     * @return boolean
     */
    public static function scheduleContact(AbstractContact $contact, AbstractTimeInterval $timeInterval) {
        $contactId = $contact->getId();
        $timeIntervalId = $timeInterval->getId();
        
        $linkSearch = static::search();
        $linkResult = $linkSearch->filter('contact_id', $contactId)
                ->filter('time_interval_id', $timeIntervalId)
                ->filterNotNull('status')
                ->select();
        if($linkResult){
            $link = $linkResult[0];
            if($link->getProperty('status')){
                return true;
            } else {
                $link->setProperty('status', 1);
            }
        } else {
            $link = static::buildNewModel();
        }
        $link->setProperty('contact_id', $contactId);
        $link->setProperty('time_interval_id', $timeIntervalId);
        if (!$link->save()) {
            return false;
        }
        return true;
    }
    
    /**
     * @param AbstractContact $contact
     * @param AbstractTimeInterval $timeInterval
     * @return boolean
     */
    public static function unscheduleContact(AbstractContact $contact, AbstractTimeInterval $timeInterval) {
        $contactId = $contact->getId();
        $timeIntervalId = $timeInterval->getId();
        
        $linkSearch = static::search();
        $links = $linkSearch->filter('contact_id', $contactId)
                ->filter('time_interval_id', $timeIntervalId)
                ->select();
        if($links){
            foreach($links as $link){
                if(!$link->softDelete()){
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * @param AbstractContact $contact
     * @param AbstractTimeInterval $timeInterval
     * @param boolean $returnIfNull
     * @return AbstractContactScheduled
     */
    public static function getByContactAndInterval(AbstractContact $contact, AbstractTimeInterval $timeInterval, $returnIfNull = false){
        $contactId = $contact->getId();
        $timeIntervalId = $timeInterval->getId();
        
        $linkSearch = static::search();
        $linkResult = $linkSearch->filter('contact_id', $contactId)
                ->filter('time_interval_id', $timeIntervalId)
                ->filterNotNull('status')
                ->select();
        $link = NULL;
        if($linkResult){
            $link = $linkResult[0];
            if($link->getProperty('status')){
                return $link;
            } else {
                $link->setProperty('status', 1);
            }
        } elseif($returnIfNull) {
            $link = static::buildNewModel();
            $link->setProperty('contact_id', $contactId);
            $link->setProperty('time_interval_id', $timeIntervalId);
        }
        
        return $link;
    }
    
    /**
     * @param type $timeInterval
     * @return AbstractContactScheduled[]
     */
    public static function getByInterval(AbstractTimeInterval $timeInterval){
        $timeIntervalId = $timeInterval->getId();
        $linkSearch = static::search();
        $links = $linkSearch->filter('time_interval_id', $timeIntervalId)
                ->select();
        return $links;
    }
    
    /**
     * @param AbstractTimeInterval $timeInterval
     * @param AbstractContact[] $contacts
     * @return boolean
     */
    public static function adjustSchedule(AbstractTimeInterval $timeInterval, $contacts = array()){
        $existingLinks = static::getByInterval($timeInterval);
        if (empty($existingLinks)) {
            $existingLinks = array();
        }
        $toUnlink = array();
        foreach ($existingLinks as $existingLink) {
            $unlinkId = $existingLink->getProperty('contact_id');
            $toUnlink[$unlinkId] = $existingLink;
        }
        foreach ($contacts as $contact) {
            $contactId = $contact->getId();
            if (isset($toUnlink[$contactId])) {
                unset($toUnlink[$contactId]);
            } else {
                $linkResult = static::scheduleContact($contact, $timeInterval);
                if (!$linkResult) {
                    return false;
                }
            }
        }
        foreach ($toUnlink as $unlink) {
            $unlinkContact = $unlink->getContact();
            $unlinkTimeInterval = $unlink->getTimeInterval();
            $unlinkResult = static::unscheduleContact($unlinkContact, $unlinkTimeInterval);
            if (!$unlinkResult) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @param AbstractContact $contact
     * @param AbstractTimeInterval $timeInterval
     * @return boolean
     */
    public static function isContactScheduled(AbstractContact $contact, AbstractTimeInterval $timeInterval){
        $contactScheduled = static::getByContactAndInterval($contact, $timeInterval);
        if($contactScheduled && $contactScheduled->getProperty('status')){
            return true;
        }
        return false;
    }
    
    /**
     * Determine the contact is available 
     * @param string Y-m-d $date
     * @param AbstractTimeInterval[] $scheduledTimeIntervals : TimeInterval Models with a contact scheduled record
     * @return boolean
     */
//    public static function isDateScheduledInTimeIntervals($date, $scheduledTimeIntervals) {
//        foreach ($scheduledTimeIntervals as $scheduledTimeInterval) {
//            if($scheduledTimeInterval->isDateInInterval($date)){
//                return true;
//            }
//        }
//        return false;
//    }

    
    /**
     * @param string Y-m-d $startDate 
     * @param string Y-m-d $endDate 
     * @param AbstractContact[] $contactModels
     * @return array : calendar event array
     */
    public static function getScheduledCalendarEventsFromStartDateEndDateContacts($startDate, $endDate, $contactModels = NULL, $orderBy = 'id'){
        $eventArray = array();

        if (!empty($contactModels)) {
            //Get scheduled TimeIntervals
            $scheduledTimeIntervals = TimeIntervalFactory::getScheduledTimeItervalsFromStartDateEndDateContacts($startDate, $endDate, $contactModels);
            
            if (!empty($scheduledTimeIntervals)) {
                foreach ($scheduledTimeIntervals as $scheduledTimeInterval) {
                    //Break down scheduled contacts 
                    $scheduledContactEventArray = $scheduledTimeInterval->convertModelToScheduledContactEvents();
                                
                    //Break down each date between start and end dete
                    $startDateObj = new DateTime($startDate);
                    $endDateObj =  new DateTime($endDate);
                    if($endDateObj->format('H:i:s') == '00:00:00') {
                        $endDateObj->modify('-1 day'); //subtract 1 day because passed parameter end date is 1 day
                    }
                    
                    //Get scheduled time
                    $startTime = $scheduledTimeInterval->getStartTime();
                    $endTime = $scheduledTimeInterval->getEndTime();
                    $recurringDayArray = $scheduledTimeInterval->convertRecurringDayToArray();
                    
                    do {
                        $eventDate = $startDateObj->format('Y-m-d');
                        $weekDayNum = date("w", $startDateObj->getTimestamp());

                        $copiedEvent = array();
                        if ($scheduledTimeInterval->isDateInInterval($eventDate) && in_array($weekDayNum, $recurringDayArray)) {
                            $newStartDateTimeObj = '';
                            $newEndDateTimeObj = '';
                            foreach ($scheduledContactEventArray as $scheduledContactEvent) {
                                $copiedEvent = $scheduledContactEvent;
                                $copiedStartDateObj = new DateTime($eventDate);
                                $copiedEvent['dow'] =  ''; //It should be clear. Otherwise converting in JS is wrong
                                $newStartDateTimeObj = new DateTime($eventDate . ' ' . $startTime);
                                $copiedEvent['start'] =  $newStartDateTimeObj->format('Y-m-d H:i:s');
                                if ($scheduledTimeInterval->isCrossMidnight()) {
                                    $newEndDateTimeObj = new DateTime($copiedStartDateObj->modify('1 day')->format('Y-m-d') . ' ' . $endTime);
                                } else {
                                    $newEndDateTimeObj = new DateTime($eventDate . ' ' . $endTime);
                                }
                                
                                $copiedEvent['end'] =  $newEndDateTimeObj->format('Y-m-d H:i:s');
                                $eventArray[] = $copiedEvent;
                            }
                        }
                        $startDateObj->modify('1 day');
                    } while ($endDateObj >= $startDateObj);
                }
            }
        }
        return $eventArray;
    }
}
