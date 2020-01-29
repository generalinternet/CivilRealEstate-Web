<?php
/**
 * Description of AbstractTimeInterval
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.1
 */
abstract class AbstractTimeInterval extends GI_Model {
    
    protected $cssClassName = '';
    protected $draggable = true;
    /** @var AbstractContact[] */
    protected $contacts = NULL;
    protected $defaultWeekDays = array(
        'mon',
        'tue',
        'wed',
        'thu',
        'fri'
    );
    protected $specificDate = NULL;
    protected $defaultFirstDay = 'mon';
    /** @var AbstractTimeInterval */
    protected $fullRangeStartTI = NULL;
    /** @var AbstractTimeInterval */
    protected $fullRangeEndTI = NULL;
    protected $allowConflicts = false;
    
    public function getAddURLAttrs(){
        $attributes = array(
            'controller' => 'schedule',
            'action' => 'add',
            'type' => $this->getTypeRef()
        );
        return $attributes;
    }
    
    public function getAddURL(){
        $attributes = $this->getAddURLAttrs();
        return GI_URLUtils::buildURL($attributes);
    }
    
    public function getViewURLAttrs(){
        $attributes = array(
            'controller' => 'schedule',
            'action' => 'view',
            'id' => $this->getId()
        );
        return $attributes;
    }
    
    public function getEditURLAttrs(){
        $attributes = array(
            'controller' => 'schedule',
            'action' => 'edit',
            'id' => $this->getId()
        );
        $specificDate = $this->getSpecificDate();
        if(!empty($specificDate)){
            $attributes['date'] = $specificDate;
        }
        return $attributes;
    }
    
    public function getTooltipURL(){
        $attributes = $this->getViewURLAttrs();
        $attributes['viewType'] = 'tooltip';
        return GI_URLUtils::buildURL($attributes);
    }
    
    public function getEditURL(){
        $attributes = $this->getEditURLAttrs();
        return GI_URLUtils::buildURL($attributes);
    }
    
    public function getDeleteURLAttrs(){
        $attributes = array(
            'controller' => 'schedule',
            'action' => 'delete',
            'id' => $this->getId()
        );
        $specificDate = $this->getSpecificDate();
        if(!empty($specificDate)){
            $attributes['date'] = $specificDate;
        }
        return $attributes;
    }
    
    public function getDeleteURL(){
        $attributes = $this->getDeleteURLAttrs();
        return GI_URLUtils::buildURL($attributes);
    }
    
    public function getScheduleURLAttrs(){
        $attributes = array(
            'controller' => 'schedule',
            'action' => 'index',
            'type' => $this->getTypeRef()
        );
        return $attributes;
    }
    
    public function getScheduleURL(){
        $attributes = $this->getScheduleURLAttrs();
        return GI_URLUtils::buildURL($attributes);
    }
    
    public function getAvailabilityURLAttrs(){
        $attributes = array(
            'controller' => 'schedule',
            'action' => 'indexAvailability',
            'type' => $this->getTypeRef()
        );
        return $attributes;
    }
    
    public function getAvailabilityURL(){
        $attributes = $this->getAvailabilityURLAttrs();
        return GI_URLUtils::buildURL($attributes);
    }
    
    public function getTitle() {
        $title = 'Time Interval';
        if (!empty($this->getId())) {
            $title .= ' ' . $this->getId();
        }
        return $title;
    }
    
    public function getColour() {
        //@todo
        $colour = GI_Colour::getRandomColour();
        return $colour;
    }
    
    public function getStartDateTime() {
        return $this->getProperty('start_date_time');
    }
    
    public function getEndDateTime() {
        return $this->getProperty('end_date_time');
    }
    
    public function getDisplayStartDate(){
        $dateTime = $this->getProperty('start_date_time');
        if($dateTime){
            return GI_Time::formatDateForDisplay($dateTime);
        }
        return NULL;
    }
    
    public function getDisplayEndDate(){
        $dateTime = $this->getProperty('end_date_time');
        if($dateTime){
            return GI_Time::formatDateForDisplay($dateTime);
        }
        return NULL;
    }
    
    public function getDisplayStartDateTime() {
        $dateTime = $this->getProperty('start_date_time');
        if($dateTime){
            return GI_Time::formatDateTimeForDisplay($dateTime);
        }
        return NULL;
    }

    public function getDisplayEndDateTime() {
        $dateTime = $this->getProperty('end_date_time');
        if($dateTime){
            return GI_Time::formatDateTimeForDisplay($dateTime);
        }
        return NULL;
    }
    
    /**@return \DateTime */
    public function getStartDateObject(){
        $startObj = new DateTime($this->getProperty('start_date_time'));
        return $startObj;
    }
    
    /**@return \DateTime */
    public function getEndDateObject(){
        $endObj = new DateTime($this->getProperty('end_date_time'));
        return $endObj;
    }
    
    public function getStartDate($forDisplay = false){
        $startObj = $this->getStartDateObject();
        if($forDisplay){
            return GI_Time::formatDateForDisplay($startObj);
        }
        return GI_Time::formatDateTime($startObj, 'date');
    }
    
    public function getEndDate($forDisplay = false){
        $endObj = $this->getEndDateObject();
        if($forDisplay){
            return GI_Time::formatDateForDisplay($endObj);
        }
        return GI_Time::formatDateTime($endObj, 'date');
    }
    
    public function getStartTime($forDisplay = false){
        if(empty($this->getProperty('start_date_time'))){
            return ProjectConfig::getDefaultTIStartTime();
        }
        $startObj = $this->getStartDateObject();
        if($forDisplay){
            return GI_Time::formatTimeForDisplay($startObj);
        }
        return GI_Time::formatDateTime($startObj, 'time');
    }
    
    public function getEndTime($forDisplay = false){
        if(empty($this->getProperty('end_date_time'))){
            return ProjectConfig::getDefaultTIEndTime();
        }
        $endObj = $this->getEndDateObject();
        if($forDisplay){
            return GI_Time::formatTimeForDisplay($endObj);
        }
        return GI_Time::formatDateTime($endObj, 'time');
    }
    
    public function getStartDateTimeForCalendar() {
        $startObj = $this->getStartDateObject();
        //ISO8601 format
        return $startObj->format('c');
    }
    
    public function getEndDateTimeForCalendar() {
        $endObj = $this->getEndDateObject();
        //ISO8601 format
        return $endObj->format('c');
    }
    
//    public function getAllDayEndDateForCalendar(){
//        $endObj = $this->getEndDateObject();
//        //Because if "allDay" as true, the time-part of the end date string is ignored, so the end date needs to set the next day.
//        $endObj->modify('1 day');
//        return GI_Time::formatDateTime($endObj, 'date');
//    }
    
//    public function getAllDayEndDateTimeForCalendar(){
//        $endObj = $this->getEndDateObject();
//        //Because if "allDay" as true, the time-part of the end date string is ignored, so the end date needs to set the next day.
//        $endObj->modify('1 second');
//        return $endObj->format('c');
//    }
    
    public function getDisplayStartTimeAndEndTime() {
        $html = '';
        if ($this->isAllDay()) {
            $html .= 'All Day';
        } else {
            $html .= GI_Time::formatTimeForDisplay($this->getStartTime(), 'g:i a') . ' - ' . GI_Time::formatTimeForDisplay($this->getEndTime(), 'g:i a');
            if ($this->isCrossMidnight()) {
                $html .=' (+1 Day)';
            }
        }
        
        return $html;
    }
    
    public function getDaysArray($ignoreNoId = false){
        if(!$this->getId() && !$ignoreNoId){
            return $this->defaultWeekDays;
        }
        $days = array();
        $weekDays = array(
            'mon',
            'tue',
            'wed',
            'thu',
            'fri',
            'sat',
            'sun'
        );
        foreach($weekDays as $weekDay){
            if($this->getProperty('day_' . $weekDay)){
                $days[] = $weekDay;
            }
        }
        return $days;
    }
    
    public function getStatus() {
        return 'active';
    }

    public function getCSSClassName() {
        return $this->cssClassName;
    }

    public function getIsDraggable() {
        return $this->draggable;
    }
    
    public function isAllDay() {
        return $this->getProperty('all_day');
    }
    public function isEditable(){
        //@todo
        return true;
    }
    public function isSingleDay() {
        return $this->getProperty('single_day');
    }
    
    public function isCrossMidnight() {
        $startTime = $this->getStartTime();
        $endTime = $this->getEndTime();
        return (strtotime($startTime) >= strtotime($endTime));
    }
    /**
     * Define the event is more than 1 day
     * @return type
     */
//    public function isMoreThanOneDayInterval() {
//        $startDate = $this->getStartDate();
//        $endDate = $this->getEndDate();
//        if ($startDate != $endDate) {
//            return true;
//        }
//        return false;
//    }

    
    /**
     * Uses $date to set the start_date_time and end_date_time to cover the entire day
     * @param String $dateString
     */
    public function setAsAllDay($dateString = NULL) {
        if(empty($dateString)){
            $dateString = GI_Time::getDate();
        }
        $startDateTime = new DateTime($dateString . ' 00:00:00');
        $endDateTime = new DateTime($dateString . ' 23:59:59');
        $this->setProperty('start_date_time', GI_Time::formatDateTime($startDateTime));
        $this->setProperty('end_date_time', GI_Time::formatDateTime($endDateTime));
        $this->setProperty('all_day', 1);
        return $this;
    }
    
    /**
     * @param GI_Form $form
     * @return \AbstractTimeIntervalFormView
     */
    public function getFormView(GI_Form $form) {
        return new TimeIntervalFormView($form, $this);
    }
    
    /**
     * @return \AbstractTimeIntervalDetailView
     */
    public function getDetailView() {
        return new TimeIntervalDetailView($this);
    }
    
    /**
     * @return \AbstractTimeIntervalTooltipView
     */
    public function getTooltipView($start = NULL, $end = NULL) {
        return new TimeIntervalTooltipView($this, $start, $end);
    }
    
    public function getBreadcrumbs(){
        $breadcrumbs = array();

        $indexURL = GI_URLUtils::buildURL(array(
                    'controller' => 'schedule',
                    'action' => 'index',
        ));
        $breadcrumbs[] = array(
            'label' => 'Full Schedule',
            'link' => $indexURL
        );
        return $breadcrumbs;
    }
    
    public function setSpecificWeekDays($selectedWeekDays){
        $weekDays = array(
            'mon',
            'tue',
            'wed',
            'thu',
            'fri',
            'sat',
            'sun'
        );
        foreach($weekDays as $weekDay){
            if(in_array($weekDay, $selectedWeekDays)){
                $this->setProperty('day_' . $weekDay, 1);
            } else {
                $this->setProperty('day_' . $weekDay, 0);
            }
        }
    }
    
    public function verifyAndSetWeekDays($save = false){
        $startDateObj = $this->getStartDateObject();
        $endDateObj = $this->getEndDateObject();
        $curWeekDays = $this->getDaysArray(true);
        $selectedWeekDays = $curWeekDays;
        $containedWeekDays = GI_Time::getContainedWeekDays($startDateObj, $endDateObj);
        foreach($selectedWeekDays as $key => $selectedWeekDay){
            if(!in_array($selectedWeekDay, $containedWeekDays)){
                unset($selectedWeekDays[$key]);
            }
        }
        $this->setSpecificWeekDays($selectedWeekDays);
        if($save){
            return $this->save();
        }
        return true;
    }
    
    public function setPropertiesFromForm(GI_Form $form, $skipRecursion = false){
        $allDay = filter_input(INPUT_POST, 'all_day');
        $startTime = '00:00:00';
        $endTime = '23:59:59';
        if(!$allDay){
            $startTime = filter_input(INPUT_POST, 'start_time');
            $endTime = filter_input(INPUT_POST, 'end_time');
            if($startTime == $endTime){
                $this->setProperty('all_day', 1);
                $endTimeObj = new DateTime($endTime);
                $endTimeObj->modify('-1 second');
                $endTime = GI_Time::formatDateTime($endTimeObj, 'time');
            }
        }
        if($skipRecursion){
            $startDate = $this->getStartDate();
            $endDate = $this->getEndDate();
            $startDateTime = new DateTime($startDate . ' ' . $startTime);
            $endDateTime = new DateTime($endDate . ' ' . $endTime);
            $this->setProperty('start_date_time', GI_Time::formatDateTime($startDateTime));
            $this->setProperty('end_date_time', GI_Time::formatDateTime($endDateTime));
            return true;
        }
        $singleDay = filter_input(INPUT_POST, 'single_day');
        $this->setProperty('single_day', $singleDay);
        
        $this->setProperty('all_day', $allDay);
        $selectedWeekDays = filter_input(INPUT_POST, 'week_days', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        $startDate = filter_input(INPUT_POST, 'start_date');
        
        $startDateTime = new DateTime($startDate . ' ' . $startTime);
        $endDate = $startDate;
        if(!$singleDay){
            $endDate = filter_input(INPUT_POST, 'end_date');
        } else {
            $selectedWeekDays = array(strtolower($startDateTime->format('D')));
        }
        $endDateTime = new DateTime($endDate . ' ' . $endTime);
        $this->setProperty('start_date_time', GI_Time::formatDateTime($startDateTime));
        $this->setProperty('end_date_time', GI_Time::formatDateTime($endDateTime));

        $this->setSpecificWeekDays($selectedWeekDays);
        return true;
    }
    
    public function setAllowConflicts($allowConflicts){
        $this->allowConflicts = $allowConflicts;
    }
    
    public function validateConflicts(GI_Form $form){
        if(!$this->allowConflicts){
            $conflictingTIs = TimeIntervalFactory::getConflicts($this);
            $conflictCount = count($conflictingTIs);
            if($conflictCount > 0){
                $message = 'There are conflicts ';
                if($conflictCount == 1){
                    $message = 'There is a conflict ';
                }
                $message .= 'with the schedule.';
                foreach($conflictingTIs as $conflictingTI){
                    /*@var $conflictingTI AbstractTimeInterval*/
                    $message .= '<br/>' . $conflictingTI->getDateRangeString();
                    if(!$conflictingTI->isAllDay()){
                        $message .= ' from ' . $conflictingTI->getTimeRangeString();
                    }
                }
                $form->addFieldError('contact_ids', 'conflict', $message);
                return false;
            }
        }
        return true;
    }
    
    public function handleFormSubmission(GI_Form $form, $skipRecursion = false) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $editWhat = filter_input(INPUT_POST, 'edit_what');
            if(!$skipRecursion && $this->isSingleDay() && $editWhat == 'single'){
                $skipRecursion = true;
            }
            if(!$skipRecursion){
                $specificDate = $this->getSpecificDate();
                if(!empty($specificDate) && ($editWhat == 'single' || $editWhat == 'future')){
                    $startDate = filter_input(INPUT_POST, 'start_date');
                    //skip if editing future and the start date = specific date (just edit all)
                    if($specificDate != $startDate || $editWhat != 'future'){
                        $includeDateInSecondChunk = false;
                        if($editWhat == 'future'){
                            $includeDateInSecondChunk = true;
                        }
                        $newChunk = $this->splitAtDate($specificDate, false, $includeDateInSecondChunk);
                        if($newChunk){
                            if($editWhat == 'future'){
                                $futrueRangeSearch = TimeIntervalFactory::getFullRangeSearch($this);
                                $futrueRangeSearch->filterGreaterOrEqualTo('start_date_time', $specificDate)
                                        ->filterNotEqualTo('id', $newChunk->getId());
                                $futureTIs = $futrueRangeSearch->select();
                                foreach($futureTIs as $otherTI){
                                    $otherTI->handleFormSubmission($form, true);
                                }
                            }
                            return $newChunk->handleFormSubmission($form, true);
                        }
                        return false;
                    } else {
                        $editWhat = 'all';
                    }
                }
                if($editWhat == 'all'){
                    $fullRangeSearch = TimeIntervalFactory::getFullRangeSearch($this);
                    $fullRangeSearch->filterNotEqualTo('id', $this->getId());
                    $allTIs = $fullRangeSearch->select();
                    foreach($allTIs as $otherTI){
                        $otherTI->handleFormSubmission($form, true);
                    }
                }
            }
            $this->setPropertiesFromForm($form, $skipRecursion);
            $contactIds = explode(',', filter_input(INPUT_POST, 'contact_ids'));
            $contacts = array();
            foreach($contactIds as $contactId){
                $contact = ContactFactory::getModelById($contactId);
                if($contact){
                    $contacts[] = $contact;
                }
            }
            $this->contacts = $contacts;
            
            if (!$this->validateConflicts($form) || !$this->save()) {
                return false;
            }
            
            if(isset($_POST['contact_ids'])){
                $contactIds = explode(',', filter_input(INPUT_POST, 'contact_ids'));
                $contacts = array();
                foreach($contactIds as $contactId){
                    $contact = ContactFactory::getModelById($contactId);
                    if($contact){
                        $contacts[] = $contact;
                    }
                }
                ContactScheduledFactory::adjustSchedule($this, $contacts);
            }
            $this->verifyAndSetWeekDays(true);
            return true;
        }
        return false;
    }
    
    /** @return AbstractContact[] */
    public function getScheduledContacts(){
        if(is_null($this->contacts)){
            $links = ContactScheduledFactory::getByInterval($this);
            $contacts = array();
            if($links){
                foreach($links as $link){
                    $contacts[] = $link->getContact();
                }
            }
            $this->contacts = $contacts;
        }
        return $this->contacts;
    }
    
    public function getScheduledContactsExcerptText($max = 2) {
        $contactsArray = $this->getScheduledContacts();
        $contactsText = '';
        if(!empty($contactsArray)) {
            $contactNameArray = array();
            if (count($contactsArray) > $max) {
                $contactsText = $contactsArray[0]->getName() . ' +'. (count($contactsArray) - 1);
            } else {
                foreach($contactsArray as $contact) {
                    $contactNameArray[] = $contact->getName();
                }
                $contactsText = implode(', ', $contactNameArray);
            }
        } else {
            $contactsText .= 'N/A';
        }
        return $contactsText;
    }
    public function getScheduledContactsText() {
        $contactsArray = $this->getScheduledContacts();
        $contactsText = '';
        if(!empty($contactsArray)) {
            $contactNameArray = array();
            foreach($contactsArray as $contact) {
                $contactNameArray[] = $contact->getName();
            }
            $contactsText = implode(', ', $contactNameArray);
        } else {
            $contactsText .= 'N/A';
        }
        return $contactsText;
    }
    
    public function getScheduledContactsCnt() {
        return count($this->getScheduledContacts());
    }
    
    public function getTimeIntervalsDataSearch($startDate = NULL, $endDate = NULL) {
        $dataSearch = TimeIntervalFactory::search()
                ->filterByTypeRef($this->getTypeRef())
                ->orderBy('start_date_time', 'DESC');
        TimeIntervalFactory::addStartDateEndDateFiltersToDataSearch($dataSearch, $startDate, $endDate);
        return $dataSearch;
    }
    public function getTimeIntervals($startDate = NULL, $endDate = NULL) {
        $dataSearch = $this->getTimeIntervalsDataSearch($startDate, $endDate);
        return $dataSearch->select();
    }
    
    public function getCalendarEvents($startDate = NULL, $endDate = NULL){
        $timeIntervals = $this->getTimeIntervals($startDate, $endDate);
        return TimeIntervalFactory::buildCalendarEvents($timeIntervals);
    }
    
    /**
     * Get linked models
     * 
     * @param type $startDate
     * @param type $endDate
     * @return GI_Model[]
     */
    public function getLinkedModelsByStartDateEndDate($startDate = NULL, $endDate = NULL){
        return NULL;
    }
    
    /**
     * Get linked models by model ids
     * 
     * @return GI_Model[]
     */
    public function getLinkedModelsByModelIds($modelIds = ''){
        return NULL;
    }
    
    /**
     * Get linked models by scheduled contact ids
     * 
     * @return GI_Model[]
     */
    public function getLinkedModelsByScheduledContactIds($contactIds = ''){
        return NULL;
    }
    
    public function convertModelToCalendarEventForBasicData() {
        $event = array();
        $event['id'] = $this->getId();
        $event['title'] = $this->getTitle();
        $event['color'] = '#'.$this->getColour();
        $event['type'] = 'ti';
        $event['contacts'] = $this->getScheduledContactsText();
        $event['contacts_excerpt'] = $this->getScheduledContactsExcerptText(1);
        $event['contacts_cnt'] = $this->getScheduledContactsCnt();
        $event['editable'] = $this->isEditable();
        $event['deleteable'] = $this->isDeleteable();
        //Commented out because TimeInterval event shouldn't show in the All day slot
//        if ($this->isAllDay()) {
//            $event['allDay'] = true;
//        } else {
//            $event['allDay'] = false;
//        }
        
        $event['dow'] = $this->convertRecurringDayToArray();
     
        return $event;
    }
    
    public function convertRecurringDayToArray() {
        //Recurring event
        $recurringDayArray = array();
        if ($this->getProperty('day_sun')) {
            $recurringDayArray[] = 0;
        }
        if ($this->getProperty('day_mon')) {
            $recurringDayArray[] = 1;
        }
        if ($this->getProperty('day_tue')) {
            $recurringDayArray[] = 2;
        }
        if ($this->getProperty('day_wed')) {
            $recurringDayArray[] = 3;
        }
        if ($this->getProperty('day_thu')) {
            $recurringDayArray[] = 4;
        }
        if ($this->getProperty('day_fri')) {
            $recurringDayArray[] = 5;
        }
        if ($this->getProperty('day_sat')) {
            $recurringDayArray[] = 6;
        }
        
        return $recurringDayArray;

    }
    
    public function convertModelToCalendarEvent() {
        $event = $this->convertModelToCalendarEventForBasicData();
        $event['start'] = $this->getStartDateTimeForCalendar();
        $event['end'] = $this->getEndDateTimeForCalendar();
        return $event;

    }
    
    /**
     * Break down the event to each scheduled contacts
     * @return event array
     */
    public function convertModelToScheduledContactEvents() {
        $scheduledContactEvents = array();
        $scheduledContacts = $this->getScheduledContacts();
        if (!empty($scheduledContacts)) {
            $event = $this->convertModelToCalendarEvent();
            $copiedEvent = array();
            //Break down contacts
            foreach ($scheduledContacts as $scheduledContact) {
                $copiedEvent = $event;
                $copiedEvent['sourceId'] = $scheduledContact->getId();
                $scheduledContactEvents[] = $copiedEvent;
            }
        }
        return $scheduledContactEvents;
    }
    
    public function convertModelToCalendarMuliDayEvent() {
        $event = $this->convertModelToCalendarEventForBasicData();
        
        //a multi day timeinterval -> break down to events for each dates
        $events = array();
        $startDateObj = new DateTime($this->getStartDate());
        $endDateObj =  new DateTime($this->getEndDate());
        $startTimeString = $this->getStartTime();
        $endTimeString = $this->getEndTime();
        $recurringDayArray = $event['dow'];
        do {
            $weekDayNum = date('w', $startDateObj->getTimestamp());
            if (in_array($weekDayNum, $recurringDayArray)) {
                $newEvent = $event;
                $newStartDateTime = new DateTime($startDateObj->format('Y-m-d').' '.$startTimeString);
                $newEndDateTime = new DateTime($startDateObj->format('Y-m-d').' '.$endTimeString);
                if ($this->isCrossMidnight()) {
                    //If the event is a cross-midnight event, add 1 day to the end time
                    $newEndDateTime->modify('1 day');
                }
                $newEvent['start'] = $newStartDateTime->format('c');
                $newEvent['end'] = $newEndDateTime->format('c');
                $events[] = $newEvent;
            }
            $startDateObj->modify('1 day');
        } while ($endDateObj >= $startDateObj);
        return $events;
    }
    
    public function addCustomFiltersToDataSearch($dataSearch) {
        return $dataSearch;
    }
    
    public static function getUITableCols() {
        return NULL;
    }
    
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \ScheduleSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new ScheduleSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return ScheduleSearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, $redirectArray = array()){
        $form = new GI_Form('schedule_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'schedule',
                    'action' => 'index'
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                }
            }
            
            $redirectArray['queryId'] = $queryId;
            
            GI_URLUtils::redirect($redirectArray);
        }
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $contactIds = $dataSearch->getSearchValue('contact_ids');
        if(!empty($contactIds)){
            static::addContactFilterToDataSearch($contactIds, $dataSearch);
        }
        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){
            $contactIds = filter_input(INPUT_POST, 'search_contact_ids');
            $dataSearch->setSearchValue('contact_ids', $contactIds);
            
            $goToDate = filter_input(INPUT_POST, 'search_go_to_date');
            $dataSearch->setSearchValue('go_to_date', $goToDate);
        }
        
        return true;
    }
    
    public static function addContactFilterToDataSearch($contactIds, GI_DataSearch $dataSearch){
        $tableName = TimeIntervalFactory::getDbPrefix().'time_interval';
        $dataSearch->join('contact_scheduled', 'time_interval_id', $tableName, 'id', 'CS')
                ->filterIn('CS.contact_id', explode(',', $contactIds));
    }
    
    /**
     * Determine the date is between start time and end time
     * @param string Y-m-d $date
     * @param GI_DataSearch $dataSearch
     */
    public function isDateInInterval($date){
        $dateObj = new DateTime($date);
        $startDateObj = new DateTime($this->getStartDate());
        $endDateObj =  new DateTime($this->getEndDate());
        if ($this->isCrossMidnight()) {
            //If the event is a cross-midnight event, subtract 1 day from the end date
            $endDateObj->modify('-1 day');
        }
        if ($startDateObj <= $dateObj && $dateObj <= $endDateObj) {
            return true;
        }

        return false;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \AbstractTimeIntervalDeleteFormView
     */
    public function getDeleteFormView(\GI_Form $form, $buildForm = false) {
        $view = new TimeIntervalDeleteFormView($form, $this);
        if($buildForm){
            $view->buildForm();
        }
        return $view;
    }
    
    public function getDateRangeString(){
        return GI_Time::formatFromDateToDate($this->getStartDate(), $this->getEndDate());
    }
    
    public function getTimeRangeString(){
        return GI_Time::formatFromTimeToTime($this->getStartTime(), $this->getEndTime());
    }
    
    public function setSpecificDate($date){
        $dateObj = new DateTime($date);
        $specificDate = GI_Time::formatDateTime($dateObj, 'date');
        if(GI_Time::isDateInRange($specificDate, $this->getStartDate(), $this->getEndDate())){
            $this->specificDate = $specificDate;
        }
        return $this;
    }
    
    public function getSpecificDate(){
        return $this->specificDate;
    }
    
    /**
     * @param string $date
     * @param boolean $removeProvidedDay
     * @param boolean $includeDateInSecondChunk
     * @return boolean|\AbstractTimeInterval
     */
    public function splitAtDate($date, $removeProvidedDay = true, $includeDateInSecondChunk = false){
        $startTime = $this->getStartTime();
        $endTime = $this->getEndTime();
        
        $chunk2 = clone $this;
        $specificChunk = clone $this;
        
        $chunk1EndObj = new DateTime($date . ' ' . $endTime);
        $chunk1EndObj->modify('-1 day');
        
        $this->setProperty('end_date_time', GI_Time::formatDateTime($chunk1EndObj));
        $this->save();
        
        $chunk2StartObj = new DateTime($date . ' ' . $startTime);
        if($removeProvidedDay || !$includeDateInSecondChunk){
            $chunk2StartObj->modify('+1 day');
        }
        
        $chunk2->setProperty('start_date_time', GI_Time::formatDateTime($chunk2StartObj));
        if(!$this->saveClone($chunk2)){
            return false;
        }
        
        if(!$removeProvidedDay){
            if($includeDateInSecondChunk){
                return $chunk2;
            } else {
                $specificStartObj = new DateTime($date . ' ' . $startTime);
                $specificEndObj = new DateTime($date . ' ' . $endTime);
                $specificChunk->setProperty('single_day', 1);
                $specificChunk->setProperty('start_date_time', GI_Time::formatDateTime($specificStartObj));
                $specificChunk->setProperty('end_date_time', GI_Time::formatDateTime($specificEndObj));
                if(!$this->saveClone($specificChunk)){
                    return false;
                }
                return $specificChunk;
            }
        }
        return true;
    }
    
    public function saveClone(AbstractTimeInterval $clone){
        $scheduledContacts = $this->getScheduledContacts();
        $sourceId = $this->getProperty('source_ti_id');
        if(empty($sourceId)){
            $sourceId = $this->getId();
        }
        $clone->setProperty('source_ti_id', $sourceId);
        $clone->verifyAndSetWeekDays();
        if($clone->save()){
            return ContactScheduledFactory::adjustSchedule($clone, $scheduledContacts);
        }
        return false;
    }
    
    public function handleDeleteForm(\GI_Form $form, $skipRecursion = false) {
        if($form->wasSubmitted() && $form->validate()){
            $specificDate = $this->getSpecificDate();
            $deleteWhat = filter_input(INPUT_POST, 'delete_what');
            if(!$skipRecursion && $this->isSingleDay() && $deleteWhat == 'single'){
                $skipRecursion = true;
            }
            if(!$skipRecursion){
                if(!empty($specificDate) && ($deleteWhat == 'single' || $deleteWhat == 'future')){
                    $startDate = $this->getStartDate();
                    //skip if deleting future and the start date = specific date (just delete all)
                    if($specificDate != $startDate || $deleteWhat != 'future'){
                        $endDate = $this->getEndDate();
                        $crop = false;
                        if($specificDate == $startDate){
                            $startTime = $this->getStartTime();
                            $startDateTime = new DateTime($specificDate . ' ' . $startTime);
                            $startDateTime->modify('+1 day');
                            $this->setProperty('start_date_time', GI_Time::formatDateTime($startDateTime));
                            $crop = true;
                        } elseif($specificDate == $endDate || $deleteWhat == 'future'){
                            $endTime = $this->getEndTime();
                            $endDateTime = new DateTime($specificDate . ' ' . $endTime);
                            $endDateTime->modify('-1 day');
                            $this->setProperty('end_date_time', GI_Time::formatDateTime($endDateTime));
                            $crop = true;
                        }
                        if($deleteWhat == 'future'){
                            $futrueRangeSearch = TimeIntervalFactory::getFullRangeSearch($this);
                            $futrueRangeSearch->filterGreaterOrEqualTo('start_date_time', $specificDate)
                                    ->filterNotEqualTo('id', $this->getId());
                            $futureTIs = $futrueRangeSearch->select();
                            foreach($futureTIs as $otherTI){
                                $otherTI->softDelete();
                            }
                        }
                        if($crop){
                            return $this->save();
                        }
                        return $this->splitAtDate($this->getSpecificDate());
                    } else {
                        $deleteWhat = 'all';
                    }
                }
                if($deleteWhat == 'all'){
                    $fullRangeSearch = TimeIntervalFactory::getFullRangeSearch($this);
                    $fullRangeSearch->filterNotEqualTo('id', $this->getId());
                    $allTIs = $fullRangeSearch->select();
                    foreach($allTIs as $otherTI){
                        $otherTI->softDelete();
                    }
                }
            }
        }
        return parent::handleDeleteForm($form);
    }
    
    public function getRecurringOptions(){
        $options = array();
        $specificDate = $this->getSpecificDate();
        if($specificDate){
            $options['single'] = 'Single Day <i class="sml_text primary">' . GI_Time::formatDateForDisplay($specificDate) . '</i>';
            $options['future'] = 'All Future Events <i class="sml_text primary">' . GI_Time::formatFromDateToDate($specificDate, $this->getFullRangeEndDate()) . '</i>';
        }
        $options['all'] = 'All Events <i class="sml_text primary">' . GI_Time::formatFromDateToDate($this->getFullRangeStartDate(), $this->getFullRangeEndDate()) . '</i>';
        return $options;
    }
    
    public function getRedirectAfterDeleteAttrs(){
        $attrs = array(
            'controller' => 'schedule',
            'action' => 'index',
            'type' => $this->getTypeRef()
        );
        return $attrs;
    }
    
    public function getFullRangeStartDate(){
        if(is_null($this->fullRangeStartTI)){
            $search = TimeIntervalFactory::getFullRangeSearch($this);
            $timeIntervalResult = $search->setItemsPerPage(1)
                    ->orderBy('start_date_time', 'ASC')
                    ->select();
            if($timeIntervalResult){
                $this->fullRangeStartTI = $timeIntervalResult[0];
            }
        }
        if(!empty($this->fullRangeStartTI)){
            return $this->fullRangeStartTI->getStartDate();
        }
        return $this->getStartDate();
    }
    
    public function getFullRangeEndDate(){
        if(is_null($this->fullRangeEndTI)){
            $search = TimeIntervalFactory::getFullRangeSearch($this);
            $timeIntervalResult = $search->setItemsPerPage(1)
                    ->orderBy('end_date_time', 'DESC')
                    ->select();
            if($timeIntervalResult){
                $this->fullRangeEndTI = $timeIntervalResult[0];
            }
        }
        if(!empty($this->fullRangeEndTI)){
            return $this->fullRangeEndTI->getEndDate();
        }
        return $this->getEndDate();
    }
    
    /**
     * @param string $filters : parameter string
     * @return array()
     */
    public function convertScheduleFilterStringToArray($filters){
        return NULL;
    }
    
    /**
     * Set all filters on
     */
    public function setScheduleFilterShowAll($filterArray = NULL){
        if (empty($filterArray)) {
            $filterArray = array();
        }
        $filterArray['show_all'] = true;
        return $filterArray;
    }
    
    /**
     * @param array $filterArray
     * @return GI_View
     */
    public static function getScheduleFilterFormView($filterArray){
        return NULL;
    }
    
}
