<?php
/**
 * Description of AbstractNote
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractNote extends GI_Model {
    
    protected $user = NULL;
    protected $notifiedUsers = NULL;
    
    protected static $maxNumberOfMinutesToEdit = 5;
    
    public static function getNotesThreadView(GI_Model $model, $noteTypeRef = NULL) {
        $notes = NoteFactory::getNotesLinkedToModel($model, $noteTypeRef);
        return new NoteThreadView($notes, $model);
    }
    
    /** @return \NoteDetailView */
    public function getView() {
        $uploader = $this->getUploader();
        $uploader->setEnabled(false);
        $uploader->setFilesLabel('');
        $uploader->setDownloadZip(true);
        $view = new NoteDetailView($this, $uploader);
        return $view;
    }
    
    /** @return string */
    public function getViewURL() {
        $id = $this->getId();
        $url = GI_URLUtils::buildURL(array(
            'controller' => 'note',
            'action' => 'view',
            'id' => $id
        ));
        return $url;
    }
    
    /** @return string */
    public function getEditURL() {
        $id = $this->getId();
        $url = GI_URLUtils::buildURL(array(
            'controller' => 'note',
            'action' => 'edit',
            'id' => $id
        ));
        return $url;
    }
    
    /** @return string */
    public function getDeleteURL() {
        $id = $this->getId();
        $url = GI_URLUtils::buildURL(array(
            'controller' => 'note',
            'action' => 'delete',
            'id' => $id
        ));
        return $url;
    }
    
    /**
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = false) {
        $title = $this->getTypeTitle();
        if($plural){
            $title .= ' Notes';
        } else {
            $title .= ' Note';
        }
        return $title;
    }
    
    /**
     * @param GI_Form $form
     * @return AbstractGI_Uploader
     */
    protected function getUploader(GI_Form $form = NULL){
        if($this->getProperty('id')){
            $appendName = 'edit_' . $this->getId();
        } else {
            $appendName = 'add';
        }
        $uploader = GI_UploaderFactory::buildUploader('note_' . $appendName);
        $folder = $this->getFolder();
        
        $uploader->setTargetFolder($folder);
        if (!empty($form)) {
            $uploader->setForm($form);
        }
        return $uploader;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \NoteFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $formView = new NoteFormView($form, $this);
        $uploader = $this->getUploader($form);
        $uploader->setFilesLabel('Attachments');
      //  $uploader->setAddBrowseButton(false);
        $uploader->setBrowseLabel('');
        $uploader->setBrowseIconClass('icon paperclip');
        $formView->setUploader($uploader);
        if($buildForm){
            $formView->buildForm();
        }
        return $formView;
    }
    
    public function getUser() {
        if (empty($this->user)) {
            $this->user = UserFactory::getModelById($this->getProperty('uid'));
        }
        return $this->user;
    }
    
    /**
     * @param GI_Form $form
     * @return boolean
     */
    protected function setPropertiesFromForm(GI_Form $form){
        if (!$form->wasSubmitted() && $this->validateForm($form)) {
            return false;
        }
        $noteContent = filter_input(INPUT_POST, 'note');
        $summary = filter_input(INPUT_POST, 'summary');
        $this->setProperty('note', $noteContent);
        if (empty($summary) && !empty($noteContent)) {
            $summary = GI_StringUtils::summarize($noteContent, 80, true);
        }
        $this->setProperty('summary', $summary);
        $usersToNotifyString = filter_input(INPUT_POST, 'users_to_notify');
        if (!empty($usersToNotifyString)) {
            $this->setProperty('notified_user_ids', $usersToNotifyString);
        }
        return true;
    }

    /**
     * @param GI_Form $form
     * @return boolean
     */
    public function handleFormSubmission(GI_Form $form, $model = NULL) {
        if ($this->validateForm($form)) {
            $originalUsersToNotifyString = $this->getProperty('notified_user_ids');
            if(!$this->setPropertiesFromForm($form)){
                return false;
            }
            $uploader = $this->getUploader($form);
            if (!$this->save()) {
                return false;
            }
            if (!empty($model)) {
                if (!$this->linkNoteToModel($model)) {
                    return false;
                }
            }
            if (!empty($uploader)) {
                $uploader->setTargetFolder($this->getFolder());
                if (!FolderFactory::putUploadedFilesInTargetFolder($uploader)) {
                    return false;
                }
            }
            $updatedUsersToNotifyString = $this->getProperty('notified_user_ids');
            if (empty($originalUsersToNotifyString) && !empty($updatedUsersToNotifyString)) {
                $currentUser = Login::getUser();
                $currentUserName = $currentUser->getFullName();
                $message = $currentUserName . ' has added a note';
                $attributes = NULL;
                if (!empty($model)) {
                    $message .= ' to ' . $model->getSpecificTitle();
                    $attributes = $model->getViewURLAttributes();
                }
                $userIds = explode(',', $updatedUsersToNotifyString);
                foreach ($userIds as $userId) {
                    $userToNotify = UserFactory::getModelById($userId);
                    Notification::notifyUser($userToNotify, $message, $attributes);
                }
            }

            return true;
        }
        return false;
    }
    
    public function getTitle(){
        return 'Note #' . $this->getId();
    }
    
    /**
     * @param string $noteTypeRef
     * @return array
     */
    public function getBreadcrumbs($noteTypeRef = NULL) {
        $breadcrumbs = array();
        $bcIndexLink = GI_URLUtils::buildURL(array(
            'controller' => 'note',
            'action' => 'index'
        ));
        $breadcrumbs[] = array(
            'label' => 'All Notes',
            'link' => $bcIndexLink
        );
        if (empty($noteTypeRef)) {
            $noteTypeRef = $this->getTypeRef();
        }
        if(!empty($noteTypeRef) && $noteTypeRef != 'note'){
            $bcLink = GI_URLUtils::buildURL(array(
                'controller' => 'note',
                'action' => 'index',
                'type' => $noteTypeRef
            ));
            $breadcrumbs[] = array(
                'label' => $this->getViewTitle(true),
                'link' => $bcLink
            );
        }
        $noteId = $this->getId();
        if (!is_null($noteId)) {
            $breadcrumbs[] = array(
                'label' => $this->getTitle(),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }
    
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \NoteSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new NoteSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $name = $dataSearch->getSearchValue('contents');
        if(!empty($name)){
            static::addContentsFilterToDataSearch($name, $dataSearch);
        }
        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){
            $name = filter_input(INPUT_POST, 'search_contents');
            $dataSearch->setSearchValue('contents', $name);
        }
        return true;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return NoteSearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, $redirectArray = array()){
        $form = new GI_Form('note_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'note',
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
    
    public static function addContentsFilterToDataSearch($contents, GI_DataSearch $dataSearch){
        $dataSearch->filterTermsLike(array(
            'note'
        ), $contents)
                ->orderByLikeScore('note', $contents);
    }
    
    public function getIsAddable() {
        if(Permission::verifyByRef('add_notes')){
            return true;
        }
        return false;
    }
    
    public function getIsViewable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('view_notes')){
            return true;
        }
        return false;
    }
    
    public function getIsEditable() {
        if (!$this->wasPostedRecentlyEnoughToEdit()) {
            return false;
        }
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('edit_notes')){
            return true;
        }
        return false;
    }
    
    public function getIsDeleteable(){
        if (!$this->wasPostedRecentlyEnoughToEdit()) {
            return false;
        }
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('delete_notes')){
            return true;
        }
        return false;
    }
    
    public function getContent($summary = false){
        if($summary){
            $contentString = $this->getProperty('summary');
            if(empty($contentString)){
                $contentString = $this->getProperty('note');
            }
            $content = GI_StringUtils::summarize($contentString, 80, true);
        } else {
            $contentString = $this->getProperty('note');
            $content = GI_StringUtils::nl2brHTML($contentString);
        }
        return $content;
    }
    
    public function linkNoteToModel(GI_Model $model) {
        return NoteFactory::linkNoteAndModel($this, $model);
    }
    
    public function getTimestampForDisplay() {
        $postedDateTime = new DateTime($this->getProperty('inception'));
        $currentDateTime = new DateTime();
        $string = 'Posted';
        if ($postedDateTime->format('Y-m-d') === $currentDateTime->format('Y-m-d')) {
            $string .= ' Today at ' . $postedDateTime->format('g:i a');
        } else {
            $currentDateTime->modify('-1 day');
            if ($postedDateTime->format('Y-m-d') === $currentDateTime->format('Y-m-d')) {
                $string .= ' Yesterday at ' . $postedDateTime->format('g:i a');
            } else {
                $dateFormat = 'F jS';
                if ($postedDateTime->format('Y') !== $currentDateTime->format('Y')) {
                    $dateFormat .= ', Y';
                }
                $string .= ' ' . $postedDateTime->format($dateFormat) . ' at ' . $postedDateTime->format('g:i a');
            }
        }
        return $string;
    }
    
    public function hasAttachments() {
        $folder = $this->getFolder();
        if (!empty($folder)) {
            $files = $folder->getFiles();
            if (!empty($files)) {
                return true;
            }
        }
        return false;
    }
    
    public function getNotifiedUsers() {
        if (empty($this->notifiedUsers)) {
            $notifiedUserIdsString = $this->getProperty('notified_user_ids');
            if (!empty($notifiedUserIdsString)) {
                $notifiedUsers = array();
                $notifiedUserIdsArray = explode((','), $notifiedUserIdsString);
                if (!empty($notifiedUserIdsArray)) {
                    foreach ($notifiedUserIdsArray as $notifiedUserId) {
                        $notifiedUsers[$notifiedUserId] = UserFactory::getModelById($notifiedUserId);
                    }
                }
                $this->notifiedUsers = $notifiedUsers;
            }
        }
        return $this->notifiedUsers;
    }
    
    protected function wasPostedRecentlyEnoughToEdit() {
        $postedDateTime = new DateTime($this->getProperty('inception'));
        $currentDateTime = new DateTime();
        $difference = $currentDateTime->diff($postedDateTime);
        $numOfMinutes = $difference->days * 24 * 60;
        $numOfMinutes += $difference->h * 60;
        $numOfMinutes += $difference->i;
        if ($numOfMinutes < static::$maxNumberOfMinutesToEdit) {
            return true;
        }
        return false;
    }
    
    public static function getNotesThreadHTML(GI_Model $model, $noteTypeRef = NULL, $pageNumber = 1, $itemsPerPage = 3) {
        $notes = NoteFactory::getNotesLinkedToModel($model, $noteTypeRef, $pageNumber, $itemsPerPage);
        $threadHtml = '';
        if (!empty($notes)) {
            foreach ($notes as $note) {
                $detailView = $note->getView();
                $threadHtml .= $detailView->getHTMLView();
            }
            if (count($notes) == $itemsPerPage) {
                $threadView = new NoteThreadView(NULL, $model);
                $buttonHTML = $threadView->getLoadMoreButtonHTML($pageNumber + 1);
                $threadHtml .= $buttonHTML;
            }
        }
        return $threadHtml;
    }
}
