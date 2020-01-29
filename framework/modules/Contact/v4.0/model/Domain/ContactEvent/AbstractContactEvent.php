<?php
/**
 * Description of AbstractEvent
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactEvent extends GI_Model {
    
    protected $contact;
    protected $contactCat;
    protected $timeinterval;
    /**
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = true) {
        $title = 'Event';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    /**
     * @param GI_Form $form
     * @return \ContactEventFormView
     */
    public function getFormView($form) {
        $formView = new ContactEventFormView($form, $this);
        $uploader = $this->getUploader($form);
        $formView->setUploader($uploader);
        return $formView;
    }
    
    /**
     * Get detail view
     * @return \ContactEventDetailView
     */
    public function getDetailView() {
        $detailView = new ContactEventDetailView($this);
        return $detailView;
    }
    
    /**
     * Get uploader
     * @param type $form
     * @return type
     */
    public function getUploader($form){
        if($this->getProperty('id')){
            $appendName = 'edit_' . $this->getProperty('id');
        } else {
            $appendName = 'add_event';
        }
        $uploader = GI_UploaderFactory::buildUploader('event_' . $appendName);
        $folder = $this->getFolder();
        $uploader->setTargetFolder($folder);
        $uploader->setForm($form);
        $uploader->setAreFilesRenamable(false);
        return $uploader;
    }
    
    /**
     * Form submit handler
     * @param type $form
     * @param type $pId : contact id
     * @return boolean|$this
     */
    public function handleFormSubmission($form) {
        if (!$form->wasSubmitted() || !$form->validate()) {
            return false;
        }
        $title = filter_input(INPUT_POST, 'title');
        $this->setProperty('contact_event.title', $title);
        $notes = filter_input(INPUT_POST, 'notes');
        $this->setProperty('contact_event.notes', $notes);
        
        $mulpleDatesTypeString = ContactEventFactory::getMulpleDatesTypeString();
        $mulpleDatesTypeArr = explode(',', $mulpleDatesTypeString);
        $mulpleTimesTypeString = ContactEventFactory::getMulpleTimesTypeString();
        $mulpleTimesTypeArr = explode(',', $mulpleTimesTypeString);
        
        //time interval
        $startDate = filter_input(INPUT_POST, 'start_date');
        if (in_array($this->getTypeRef(), $mulpleDatesTypeArr)) {
            //Multi-date event
            $endDate = filter_input(INPUT_POST, 'end_date');
        } else {
            //Single-date event
            $endDate = $startDate;
        }
        $startTime = filter_input(INPUT_POST, 'start_time');
        if (in_array($this->getTypeRef(), $mulpleTimesTypeArr)) {
            //Multi-time event
            $endTime = filter_input(INPUT_POST, 'end_time');
        } else {
            //Single-time event
            $endTime = $startTime;
        }
        $startDateTime = new DateTime($startDate . ' ' . $startTime);
        $endDateTime = new DateTime($endDate . ' ' . $endTime);
        $timeInterval = $this->getTimeinterval();
        $timeInterval->setProperty('start_date_time', GI_Time::formatDateTime($startDateTime));
        $timeInterval->setProperty('end_date_time', GI_Time::formatDateTime($endDateTime));
        if ($timeInterval->save()) {
            $this->setProperty('contact_event.time_interval_id', $timeInterval->getProperty('id'));
            $uploader = $this->getUploader($form);
            if ($this->save()) {
                $uploader->setTargetFolder($this->getFolder()); 
                if(FolderFactory::putUploadedFilesInTargetFolder($uploader)) {
                    return $this;
                }
            }
        }
        
        return NULL;
    }
    
    /**
     * Get Timeinterval object
     * @return TimeInterval
     */
    public function getTimeinterval() {
        if (empty($this->timeinterval)) {
            $tiId = $this->getProperty('time_interval_id');
            if (empty($tiId)) {
                $this->timeinterval = TimeIntervalFactory::buildNewModel('interval');
            } else {
                $this->timeinterval = TimeIntervalFactory::getModelById($tiId);
            }
        }
        return $this->timeinterval;
    }
    
    /**
     * Get start date
     * @param type $customFormat
     * @return type
     */
    public function getStartDate($customFormat = 'Y-m-d') {
        $timeInterval = $this->getTimeinterval();
        if (!empty($timeInterval)) {
            return GI_Time::formatDateTimeForDisplay($timeInterval->getProperty('start_date_time'), $customFormat);
        }
        return '';
    }
    
    /**
     * Get start time
     * @param type $customFormat
     * @return type
     */
    public function getStartTime($customFormat = 'h:i a') {
        $timeInterval = $this->getTimeinterval();
        if (!empty($timeInterval)) {
            return GI_Time::formatDateTimeForDisplay($timeInterval->getProperty('start_date_time'), $customFormat);
        }
        return '';
    }
    
    /**
     * Get end date
     * @param type $customFormat
     * @return type
     */
    public function getEndDate($customFormat = 'Y-m-d') {
        $timeInterval = $this->getTimeinterval();
        if (!empty($timeInterval)) {
            return GI_Time::formatDateTimeForDisplay($timeInterval->getProperty('end_date_time'), $customFormat);
        }
        return '';
    }
    
    /**
     * Get end time
     * @param type $customFormat
     * @return type
     */
    public function getEndTime($customFormat = 'h:i a') {
        $timeInterval = $this->getTimeinterval();
        if (!empty($timeInterval)) {
            return GI_Time::formatDateTimeForDisplay($timeInterval->getProperty('end_date_time'), $customFormat);
        }
        return '';
    }
    
    /**
     * Get start datetime to display
     * @param type $customFormat
     * @return type
     */
    public function getDisplayStartDateTime($customFormat = 'M jS, Y g:i a') {
        $timeInterval = $this->getTimeinterval();
        if (!empty($timeInterval)) {
            return GI_Time::formatDateTimeForDisplay($timeInterval->getProperty('start_date_time'), $customFormat);
        }
        return '';
    }
    
    /**
     * Get end datetime to display
     * @param type $customFormat
     * @return type
     */
    public function getDisplayEndDateTime($customFormat = 'M jS, Y g:i a') {
        $timeInterval = $this->getTimeinterval();
        if (!empty($timeInterval)) {
            return GI_Time::formatDateTimeForDisplay($timeInterval->getProperty('end_date_time'), $customFormat);
        }
        return '';
    }
    
    public function getViewURLAttributes() {
        return array(
            'controller' => 'contactevent',
            'action' => 'view',
            'id' => $this->getId(),
        );
    }
    
    public function getIsViewable() {
        if(Permission::verifyByRef('view_c_events') || $this->getProperty('uid') == Login::getUserId()) {
            return parent::getIsViewable();
        }
        //Check contact category permission
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            if ($contactCat->isEventViewable()) {
                return parent::getIsViewable();
            }
        }
        
        $contact = $this->getContact();
        if (!empty($contact)) {
            $contactSearch = ContactFactory::search();
            $contactSearch->filter('id', $contact->getProperty('id'));
            $contact->addClientRestrictionFiltersToDataSearch($contactSearch);
            $contactArray = $contactSearch->select();
            if (!empty($contactArray)) {
                $this->viewable = true;
                return true;
            }
        }
        
        return false;
    }
    
    public function getAddURLAttributes($contactId) {
        if (empty($contactId)) {
            $contact = $this->getContact();
        } else {
            $contact = ContactFactory::getModelById($contactId);
        }
        if (!empty($contact)) {
            return array(
                'controller' => 'contactevent',
                'action' => 'add',
                'pId' => $contact->getId(),
            );
        }
        return NULL;
    }
    
    public function getAddURL($contactId = NULL) {
        $addURLAttributes = $this->getAddURLAttributes($contactId);
        if (!empty($addURLAttributes)) {
            return GI_URLUtils::buildURL($addURLAttributes);
        }
        return NULL;
    }
    
    public function getIsAddable() {
        if(Permission::verifyByRef('add_c_events')) {
            return parent::getIsAddable();
        }

        //Check contact category permission
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            if ($contactCat->isEventAddable()) {
                return parent::getIsAddable();
            }
        }
        
        return false;
    }
    
    public function getDeleteURLAttributes() {
        $deleteURLAttributes = array(
                'controller' => 'contactevent',
                'action' => 'delete',
                'id' => $this->getId(),
            );
        $contact = $this->getContact();
        if (!empty($contact)) {
            $deleteURLAttributes['pId'] = $contact->getId();
        }
        return $deleteURLAttributes;
    }
    
    public function getDeleteURL() {
        if ($this->isDeleteable()) {
            $deleteURLAttributes = $this->getDeleteURLAttributes();
            return GI_URLUtils::buildURL($deleteURLAttributes);
        }
        return NULL;
    }
    
    public function getIsDeleteable() {
        if(Permission::verifyByRef('delete_c_events')) {// $this->getProperty('uid') == Login::getUserId() doesn't apply because even it's mine, the user can't delete it without a permission
            return parent::getIsDeleteable();
        }
        //Check contact category permission
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            if ($contactCat->isEventDeleteable()) {
                return parent::getIsDeleteable();
            }
        }
        return false;
    }
    
    public function getEditURLAttributes() {
        $editURLAttributes = array(
                'controller' => 'contactevent',
                'action' => 'edit',
                'id' => $this->getId(),
            );
        $contact = $this->getContact();
        if (!empty($contact)) {
            $editURLAttributes['pId'] = $contact->getId();
        }
        return $editURLAttributes;
    }
    
    public function getEditURL() {
        $editURLAttributes = $this->getEditURLAttributes();
        return GI_URLUtils::buildURL($editURLAttributes);
    }
    
    public function getIsEditable() {
        if(Permission::verifyByRef('edit_c_events') || $this->getProperty('uid') == Login::getUserId()) {
            return parent::getIsEditable();
        }
        //Check contact category permission
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            if ($contactCat->isEventEditable()) {
                return parent::getIsEditable();
            }
        }
    }
    
    /**
     * Get start~end datetime to display
     * @param type $customFormat
     * @return type
     */
    public function getDisplayDateRange() {
        $timeInterval = $this->getTimeinterval();
        if (!empty($timeInterval)) {
            return GI_Time::formatFromDateToDate($this->getStartDate(),$this->getEndDate());
        }
        return '';
    }
    
    /**
     * Get contact GI_Model
     * @return GI_Model
     */
    public function getContact() {
        if (empty($this->contact)) {
            $contactId = $this->getProperty('contact_id');
            if (!empty($contactId)) {
                $this->contact = ContactFactory::getModelById($contactId);
            }
        }
        return $this->contact;
    }
    
    /**
     * Get contact category GI_Model
     * @return GI_Model
     */
    public function getContactCat() {
        if (empty($this->contactCat)) {
            $contact = $this->getContact();
            if (!empty($contact)) {
                $this->contactCat = $contact->getContactCat();
            }
        }
        return $this->contactCat;
    }
    
    /**
     * Get contact name
     * @return string
     */
    public function getContactName() {
        $contact = $this->getContact();
        if (!empty($contact)) {
            return $contact->getName();
        }
        return '';
    }
    
    /**
     * Get contact view URL
     * @return string
     */
    public function getContactViewURL() {
        $contact = $this->getContact();
        if (!empty($contact) && $contact->isViewable()) {
            return $contact->getViewURL();
        }
        return '';
    }
    
    /**
     * Get event title
     * @return string
     */
    public function getEventTitle() {
        $title = $this->getProperty('title');
        if (empty($title)) {
            $title = $this->getTypeTitle();
        }
        return $title;
    }
    
    /**
     *  Get event title
     * @return string
     */
    public function getEventTitleWithLink() {
        $title = $this->getEventTitle();
        if ($this->isViewable()) {
            $viewURL = $this->getViewURL();
            $viewURL .= '&refresh=1';
            return '<a href="'.$viewURL.'" class="open_modal_form" data-modal-class="medium_sized">'.$title.'</a>';
        } else {
            return $title;
        }
    }
    
    
    
    /**
     * Get the summary of notes
     * @return string
     */
    public function getNotesSummary($limit = 30, $lastWord = false) {
        return GI_StringUtils::summarize($this->getProperty('notes'), $limit, $lastWord);
    }
    
    /**
     * Check if there is attached file
     * @return boolean
     */
    public function hasAttachedFiles() {
        $folder = $this->getFolder(false);
        if($folder){
            $files = $folder->getFiles();
            if (!empty($files)) {
                return true;
            }
        }
        return false;
    }
    
    public static function getUITableCols() {
        $tableColArrays = array(
            //Type
            array(
                'header_title' => 'Type',
                'method_name' => 'getTypeTitle',
                'css_header_class' => 'med_col',
                'css_class' => 'med_col',
            ),
            //Date
            array(
                'header_title' => 'Date',
                'method_name' => 'getDisplayDateRange',
            ),
            //Contact Name
            array(
                'header_title' => 'Contact',
                'method_name' => 'getContactName',
                'cell_url_method_name' => 'getContactViewURL',
            ),
            //Created By
            array(
                'header_title' => 'Created By',
                'method_name' => 'getCreatedByName',
             ),
            //Title
            array(
                'header_title' => 'Title',
                'method_name' => 'getEventTitleWithLink',
                'css_class' => 'linked',
             ),
            //Summary
            array(
                'header_title' => 'Summary',
                'method_name' => 'getNotesSummary',
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public static function getUIRolodexCols() {
        $tableColArrays = array(
            array(
                'method_name' => 'getTypeIconWithLink',
                'css_class' => 'icon_cell linked',
                'cell_hover_title_method_name' => 'getTypeTitle',
            ),
            array(
                'method_name' => 'getContactName',
                'cell_url_method_name' => 'getContactViewURL',
            ),
            array(
                'method_name' => 'getEventTitleWithLink',
                'css_class' => 'linked',
            ),
        );
        $UIRolodexCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UIRolodexCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UIRolodexCols;
    }
    
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \ContactSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new ContactEventSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    public static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $searchType = $dataSearch->getSearchValue('search_type');
        if (empty($searchType) || $searchType === 'basic') {
            //Basic Search
            $basicSearchField = $dataSearch->getSearchValue('basic_search_field');
            if(!empty($basicSearchField)){
                static::addBasicSearchFieldFilterToDataSearch($basicSearchField, $dataSearch);
            }
        } else {
            //Advanced Search
            $contactIds = $dataSearch->getSearchValue('contact_ids');
            if (!empty($contactIds)) {
                $contactIdsArray = explode(',', $contactIds);
                static::addContactIdsFilterToDataSearch($contactIdsArray, $dataSearch);
            }
            
            $userIds = $dataSearch->getSearchValue('user_ids');
            if (!empty($userIds)) {
                $userIdsArray = explode(',', $userIds);
                static::addUserIdsFilterToDataSearch($userIdsArray, $dataSearch);
            }
            
            $startDate = $dataSearch->getSearchValue('start_date');
            if(!empty($startDate)){
                static::addStartDateFilterToDataSearch($startDate, $dataSearch);
            }

            $endDate = $dataSearch->getSearchValue('end_date');
            if(!empty($endDate)){
                static::addEndDateFilterToDataSearch($endDate, $dataSearch);
            }
            
            $contentText = $dataSearch->getSearchValue('content_text');
            if(!empty($contentText)){
                static::addContentTextFilterToDataSearch($contentText, $dataSearch);
            }
            
            $eventType = $dataSearch->getSearchValue('event_type');
            if(!empty($eventType) && $eventType !== 'NULL'){
                if ($eventType == 'event') {
                    $dataSearch->filterByTypeRef($eventType, false);
                } else {
                    $dataSearch->filterByTypeRef($eventType);
                }
            }
        }
        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){
            $dataSearch->clearSearchValues();
            $searchType = filter_input(INPUT_POST, 'search_type');
            if (empty($searchType) || $searchType === 'basic') {
                $dataSearch->setSearchValue('search_type', 'basic');
                $basicSearchField = filter_input(INPUT_POST, 'basic_search_field');
                $dataSearch->setSearchValue('basic_search_field', $basicSearchField);
            } else {
                $dataSearch->setSearchValue('search_type', 'advanced');
                $contactIds = filter_input(INPUT_POST, 'search_contact_ids');
                $dataSearch->setSearchValue('contact_ids', $contactIds);

                $userIds = filter_input(INPUT_POST, 'search_user_ids');
                $dataSearch->setSearchValue('user_ids', $userIds);

                $startDate = filter_input(INPUT_POST, 'search_start_date');
                $dataSearch->setSearchValue('start_date', $startDate);

                $endDate = filter_input(INPUT_POST, 'search_end_date');
                $dataSearch->setSearchValue('end_date', $endDate);
                
                $contentText = filter_input(INPUT_POST, 'search_content_text');
                $dataSearch->setSearchValue('content_text', $contentText);
                
                $eventType = filter_input(INPUT_POST, 'search_event_type');
                $dataSearch->setSearchValue('event_type', $eventType);
            }
        }
    }
    
    /**
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return \ContactSearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, &$redirectArray = array(), $catType = NULL){
        $form = new GI_Form('contact_event_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'contactevent',
                    'action' => 'index',
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                }
                
                if(!empty($catType)){
                    $redirectArray['catType'] = $catType;
                }
            }
            
            $redirectArray['queryId'] = $queryId;
            if(GI_URLUtils::getAttribute('ajax')){
                if(GI_URLUtils::getAttribute('redirectAfterSearch')){
                    //Set new Url for search
                    unset($redirectArray['ajax']);
                    $redirectArray['fullView'] = 1;
                    $redirectArray['newUrl'] = GI_URLUtils::buildURL($redirectArray);
                    $redirectArray['newUrlTargetId'] = 'list_bar';
                    $redirectArray['jqueryAction'] = 'clearMainPanel();';
                } else {
                    $redirectArray['ajax'] = 1;
                    GI_URLUtils::redirect($redirectArray);
                }
            } else {
                GI_URLUtils::redirect($redirectArray);
            }
        }
        return $searchView;
    }
    
    public function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        if (!Permission::verifyByRef('view_c_events')) {
            $sampleContact = ContactFactory::buildNewModel('contact');
            if (!Permission::verifyByRef('view_c_client_events')) {
                //Exclude clients except for ones assigned to login id or created by login id
                $sampleContact->addClientRestrictionFiltersToDataSearch($dataSearch);
            }

            if (!Permission::verifyByRef('view_c_vendor_events')) {
                //Exclude vendors except for ones created by login id
                $sampleContact->addVendorRestrictionFiltersToDataSearch($dataSearch);
            }

            if (!Permission::verifyByRef('view_c_internal_events')) {
                //Exclude internal contacts except for ones created by login id
                $sampleContact->addInternalRestrictionFiltersToDataSearch($dataSearch);
            }
        }
    }
    
    public function addContactCatJoinsToDataSearch(GI_DataSearch $dataSearch) {
        if (!Permission::verifyByRef('view_c_events')) {
            if (!Permission::verifyByRef('view_c_client_events')) {
                $this->addContactCatJoinsToClientDataSearch($dataSearch);
            } 
            if (!Permission::verifyByRef('view_c_vendor_events')) {
                $this->addContactCatJoinsToVendorDataSearch($dataSearch);
            } 
            if (!Permission::verifyByRef('view_c_internal_events')) {
                $this->addContactCatJoinsToInternalDataSearch($dataSearch);
            }
        }
    }
    
    protected function addContactCatJoinsToClientDataSearch(GI_DataSearch $dataSearch) {
        $clientType = TypeModelFactory::getTypeModelByRef('client', 'contact_cat_type');
        $contactEventTable = $dataSearch->prefixTableName('contact_event');
        
        if(!$dataSearch->isJoinedWithTable('CCNOTCLIENT')){
            $dataSearch->createJoin('contact_cat', 'contact_id', $contactEventTable, 'contact_id', 'CCNOTCLIENT', 'left')
                    ->filterNotEqualTo('CCNOTCLIENT.contact_cat_type_id', $clientType->getProperty('id'));
        }
          
        if(!$dataSearch->isJoinedWithTable('CCCLIENT')){
            $dataSearch->createJoin('contact_cat', 'contact_id', $contactEventTable, 'contact_id', 'CCCLIENT', 'left')
                    ->filter('CCCLIENT.contact_cat_type_id', $clientType->getProperty('id'));
        }
        
        if(!$dataSearch->isJoinedWithTable('CREL')){
            $dataSearch->join('contact_relationship', 'c_contact_id', $contactEventTable, 'contact_id', 'CREL', 'left');
        }
    }
    
    protected function addContactCatJoinsToVendorDataSearch(GI_DataSearch $dataSearch) {
        $contactEventTable = $dataSearch->prefixTableName('contact_event');
        if(!$dataSearch->isJoinedWithTable('VCONCAT')){
            $dataSearch->join('contact_cat', 'contact_id', $contactEventTable, 'contact_id', 'VCONCAT', 'left');
        }
    }
    
    protected function addContactCatJoinsToInternalDataSearch(GI_DataSearch $dataSearch) {
        $contactEventTable = $dataSearch->prefixTableName('contact_event');
        if(!$dataSearch->isJoinedWithTable('ICONCAT')){
            $dataSearch->join('contact_cat', 'contact_id', $contactEventTable, 'contact_id', 'ICONCAT', 'left');
        }
    }
    
    protected static function addContactIdsFilterToDataSearch($contactIdsArray, GI_DataSearch $dataSearch) {
        $count = count($contactIdsArray);
        static::addContactTableToDataSearch($dataSearch);
        $dataSearch->filterGroup();
        for ($i = 0; $i < $count; $i++) {
            $dataSearch->filter('CONTACT.id', $contactIdsArray[$i]);
            if ($i != ($count - 1)) {
                $dataSearch->orIf();
            }
        }
        $dataSearch->closeGroup()
                ->andIf();
        return $dataSearch;
    }
    
    protected static function addUserIdsFilterToDataSearch($userIdsArray, GI_DataSearch $dataSearch) {
        $count = count($userIdsArray);
        $dataSearch->filterGroup();
        for ($i = 0; $i < $count; $i++) {
            $dataSearch->filter('uid', $userIdsArray[$i]);
            if ($i != ($count - 1)) {
                $dataSearch->orIf();
            }
        }
        $dataSearch->closeGroup()
                ->andIf();
        return $dataSearch;
    }
    
    public static function addContactTableToDataSearch(GI_DataSearch $dataSearch){
        if($dataSearch->isJoinedWithTable('CONTACT')){
            return;
        }
        $contactEventTable = $dataSearch->prefixTableName('contact_event');
        $dataSearch->join('contact', 'id', $contactEventTable, 'contact_id', 'CONTACT');
    }
    
    public static function addUserTableToDataSearch(GI_DataSearch $dataSearch){
        if($dataSearch->isJoinedWithTable('USER')){
            return;
        }
        $contactEventTable = $dataSearch->prefixTableName('contact_event');
        $dataSearch->join('user', 'id', $contactEventTable, 'uid', 'USER');
    }
    
    public static function addTimeIntervalTableToDataSearch(GI_DataSearch $dataSearch){
        if($dataSearch->isJoinedWithTable('TI')){
            return;
        }
        $contactEventTable = $dataSearch->prefixTableName('contact_event');
        $dataSearch->join('time_interval', 'id', $contactEventTable, 'time_interval_id', 'TI');
    }
    
    public static function addStartDateFilterToDataSearch($startDate, GI_DataSearch $dataSearch){
        static::addTimeIntervalTableToDataSearch($dataSearch);
        $dataSearch->filterGreaterOrEqualTo('TI.start_date_time', $startDate);
    }
    
    public static function addEndDateFilterToDataSearch($endDate, GI_DataSearch $dataSearch){
        static::addTimeIntervalTableToDataSearch($dataSearch);
        //Because $endDate doesn't have time, add 1 day
        $endDateObj = new DateTime($endDate);
        $endDateObj->modify('1 day');
        $dataSearch->filterLessThan('TI.end_date_time', $endDateObj->format('Y-m-d'));
    }
    
    public static function addSortingToDataSearch(GI_DataSearch $dataSearch){
        static::addTimeIntervalTableToDataSearch($dataSearch);
        $dataSearch->orderBy('TI.start_date_time', 'DESC');
    }
    
    public static function addBasicSearchFieldFilterToDataSearch($basicSearchField, GI_DataSearch $dataSearch){
        $dataSearch->filterGroup()
                ->filterGroup();
        static::addContactNameFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->closeGroup()
                ->orIf()
                ->filterGroup();
        static::addCreatedByNameFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->closeGroup()
                ->orIf()
                ->filterGroup();
        static::addContentTextFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->closeGroup()
                ->closeGroup()
                ->andIf();
    }
    
    public static function addContactNameFilterToDataSearch($name, GI_DataSearch $dataSearch){
        static::addContactTableToDataSearch($dataSearch);
        $dataSearch->leftJoin('contact_ind', 'parent_id', 'CONTACT', 'id', 'ind')
                ->leftJoin('contact_org', 'parent_id', 'CONTACT', 'id', 'org')
                ->leftJoin('contact_loc', 'parent_id', 'CONTACT', 'id', 'loc')
                ->groupBy('id');
        Contact::addNameFilterToDataSearch($name, $dataSearch, 'CONTACT');
    }
    
    public static function addCreatedByNameFilterToDataSearch($name, GI_DataSearch $dataSearch){
        static::addUserTableToDataSearch($dataSearch);
        $dataSearch->filterGroup()
                ->filterLike('USER.first_name', '%'.$name.'%')
                ->orIf()
                ->filterLike('USER.last_name', '%'.$name.'%')
                ->closeGroup()
                ->andIf();
    }

    public static function addContentTextFilterToDataSearch($text, GI_DataSearch $dataSearch){
        $dataSearch->filterGroup()
                ->filterLike('title',  '%'. $text . '%')
                ->orIf()
                ->filterLike('notes',  '%'. $text . '%')
                ->closeGroup()
                ->andIf();
    }
    
    public function getTypeIcon($colour = 'gray') {
        $type = $this->getTypeRef();
        $typeIcon = $type;
        switch ($type) {
            case 'event':
                $typeIcon = 'calendar';
                break;
            case 'meeting':
                $typeIcon = 'handshake';
                break;
            case 'phone_call':
                $typeIcon = 'phone';
                break;
            default:
        }
        return GI_StringUtils::getIcon($typeIcon, true, $colour );
    }
    
    public function getTypeIconWithLink($colour = 'gray') {
        $typeIconHTML = $this->getTypeIcon($colour);
        if ($this->isViewable()) {
            $viewURL = $this->getViewURL();
            $viewURL .= '&refresh=1';
            return '<a href="'.$viewURL.'" class="open_modal_form" data-modal-class="medium_sized">'.$typeIconHTML.'</a>';
        } else {
            return $typeIconHTML;
        }
    }
    
    public function getIndexURLAttrs($withPageNumber = false){
        $indexURLAttributes = array(
            'controller' => 'contactevent',
            'action' => 'index',
        );
        $attributes = GI_URLUtils::getAttributes();
        if (isset($attributes['queryId'])) {
            $indexURLAttributes['queryId'] = $attributes['queryId'];
        }
        if ($withPageNumber && isset($attributes['pageNumber'])) {
            $indexURLAttributes['pageNumber'] = $attributes['pageNumber'];
        }
        return $indexURLAttributes;
    }
    
    public function getListBarURL($otherAttributes = NULL) {
        if (!$this->isIndexViewable()) {
            return NULL;
        }
        $listURLAttributes = $this->getIndexURLAttrs();
        $listURLAttributes['targetId'] = 'list_bar';
        $listURLAttributes['fullView'] = 1;
        $contact = $this->getContact();
        if (!empty($contact)) {
            $listURLAttributes['curId'] = $contact->getId();
        }
        if (isset($otherAttributes['type'])) {
            //overrite type
            $listURLAttributes['type'] = $otherAttributes['type'];
        }
        
        return GI_URLUtils::buildURL($listURLAttributes);
    }
}
