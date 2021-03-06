<?php
/**
 * Description of GI_Model
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class GI_Model extends GI_Object {
    
    /** @var GI_DataMap */
    protected $map = NULL;
    protected $factoryClassName;
    protected $deleteable = NULL;
    protected $editable = NULL;
    protected $viewable = NULL;
    protected $addable = NULL;
    protected $indexViewable = NULL;
    protected $folder = NULL;
    protected $daoArrayToDeleteOnSave = array();
    protected $formValidated = false;
    /** @var AbstractUser */
    protected $createdBy = NULL;
    /** @var AbstractUser */
    protected $lastModBy = NULL;
    protected static $searchFieldSuffix = NULL;
    protected static $searchFieldPrefix = NULL;
    protected static $selectRowFieldName = NULL;
    /** @var AbstractTag[] */
    protected $tags = NULL;
    protected $fresh = false;
    
    protected $contextRoles = NULL;
    protected $defaultContextRoles = NULL;
    protected $eventsLogFolder = NULL;
    
    public function __construct(GI_DataMap $map, $factoryClassName = NULL) {
        $this->map = $map;
        $this->factoryClassName = $factoryClassName;
    }
    
    public function setFactoryClassName($factoryClassName) {
        $this->factoryClassName = $factoryClassName;
    }
    
    public function getFactoryClassName() {
        return $this->factoryClassName;
    }
    
    public function getProperty($key, $original = false) {
        if (!is_null($this->map)) {
            return $this->map->getProperty($key, $original);
        }
        return NULL;
    }
    
    public function getId(){
        return $this->getProperty('id');
    }
    
    /**
     * @param string $key
     * @param mixed $value
     * @return \GI_Model
     */
    public function setProperty($key, $value) {
        if (!is_null($this->map)) {
            $this->map->setProperty($key, $value);
        }
        return $this;
    }
    
    /**
     * @return array property => value
     */
    public function getProperties(){
        $properties = $this->map->getProperties();
        return $properties;
    }
    
    /**
     * @param GI_Model $model
     * @return \GI_Model
     */
    public function setPropertiesFromModel(GI_Model $model){
        $properties = $model->getProperties();
        foreach($properties as $property => $value){
            $keyArray = explode('.', $property);
            $propertyKey = $keyArray[count($keyArray) - 1];
            if(GI_DAO::isColumnCloneable($propertyKey)){
                $this->setProperty($property, $value);
            }
        }
        return $this;
    }
    
    public function getTypeProperty($key) {
        if (!is_null($this->map)) {
            return $this->map->getTypeProperty($key);
        }
        return NULL;
    }
    
    public function setPropertyIfPostIsset($property, $postVariable){
        
        if(isset($_POST[$postVariable])){
            $postValue = filter_input(INPUT_POST, $postVariable);
            $this->setProperty($property, $postValue);
            return true;
        }
        
        return false;
        
    }
    
    public function isFresh(){
        return $this->fresh;
    }
    
    public function save() {
        $setFresh = false;
        if(!$this->getId()){
            $setFresh = true;
        }
        $mapSaveResult = $this->map->save();
        if ($mapSaveResult) {
            if (!empty($this->daoArrayToDeleteOnSave)) {
                foreach ($this->daoArrayToDeleteOnSave as $daoToDelete) {
                    if (!$daoToDelete->softDelete()) {
                        return false;
                    }
                }
            }
            if($setFresh){
                $this->fresh = true;
            }
            return true;
        }
        return false;
    }
    
    public function softDelete(){
        return $this->map->softDelete();
    }
    
    public function unSoftDelete() {
        return $this->map->unSoftDelete();
    }
            
    
    public function getMap() {
        return $this->map;
    }
    
    public function setMap(GI_DataMap $map) {
        $this->map = $map;
    }
    
    public function getTypeRef() {
        return $this->map->getTypeRef();
    }
    
    public function getTypeTitle() {
        return $this->map->getTypeTitle();
    }
    
    public function getTableName() {
        $map = $this->getMap();
        $tableName = $map->getPrimaryTableName();
        return $tableName;
    }
    
    public function getCreatedDate() {
        $date = $this->getProperty('inception');
        if(!empty($date)){
            $string = GI_Time::formatDateForDisplay($date);
            return $string;
        }
        return NULL;
    }
    
    public function getCreatedDateWithTime(){
        $date = $this->getProperty('inception');
        if(!empty($date)){
            $string = GI_Time::formatDateTimeForDisplay($date);
            return $string;
        }
        return NULL;
    }
    
    public function getModifiedDate() {
        $date = $this->getProperty('last_mod');
        if(!empty($date)){
            $string = GI_Time::formatDateForDisplay($date);
            return $string;
        }
        return NULL;
    }
    
    public function getModifiedDateWithTime(){
        $date = $this->getProperty('last_mod');
        if(!empty($date)){
            $string = GI_Time::formatDateTimeForDisplay($date);
            return $string;
        }
        return NULL;
    }
    
    public function __clone(){
        $this->map = clone $this->map;
        $this->createdBy = NULL;
        $this->lastModBy = NULL;
        $this->deleteable = NULL;
        $this->editable = NULL;
        $this->viewable = NULL;
        $this->addable = NULL;
        $this->folder = NULL;
        $this->daoArrayToDeleteOnSave = array();
        $this->formValidated = false;
    }
    
    /**
     * Returns boolean for whether this GI_Model is deletable or not.
     * This should be overwritten.
     * 
     * @return boolean
     */
    protected function getIsDeleteable() {
        $factoryClassName = $this->factoryClassName;
        $deleteable = $factoryClassName::isModelDeleteable($this);
        return $deleteable;
    }
    
    /**
     * Publicly accessible check to see if this GI_Model is deletable, and sets the deletable property to avoid multiple checks
     * 
     * @return boolean
     */
    public function isDeleteable(){
        if (is_null($this->deleteable)) {
            $this->deleteable = $this->getIsDeleteable();
        }
        return $this->deleteable;
    }
    
    /**
     * Returns boolean for whether this GI_Model is editable or not.
     * This should be overwritten.
     * 
     * @return boolean
     */
    protected function getIsEditable() {
        $editable = true;
        return $editable;
    }
    
    /**
     * Publicly accessible check to see if this GI_Model is editable, and sets the editable property to avoid multiple checks
     * 
     * @return boolean
     */
    public function isEditable(){
        if (is_null($this->editable)) {
            $this->editable = $this->getIsEditable();
        }
        return $this->editable;
    }
    
    /**
     * Returns boolean for whether this GI_Model (type) is addable or not.
     * This should be overwritten.
     * 
     * @return boolean
     */
    protected function getIsAddable() {
        $addable = true;
        return $addable;
    }
    
    /**
     * Publicly accessible check to see if this GI_Model (type) is addable, and sets the addable property to avoid multiple checks
     * 
     * @return boolean
     */
    public function isAddable(){
        if (is_null($this->addable)) {
            $this->addable = $this->getIsAddable();
        }
        return $this->addable;
    }
    
    /**
     * Returns boolean for whether this GI_Model is viewable or not.
     * This should be overwritten.
     * 
     * @return boolean
     */
    protected function getIsViewable() {
        $viewable = true;
        return $viewable;
    }
    
    /**
     * Publicly accessible check to see if this GI_Model is viewable, and sets the viewable property to avoid multiple checks
     * 
     * @return boolean
     */
    public function isViewable(){
        if (is_null($this->viewable)) {
            $this->viewable = $this->getIsViewable();
        }
        return $this->viewable;
    }
    
    /**
     * Returns boolean for whether this GI_Model index/list is viewable or not.
     * This should be overwritten.
     * 
     * @return boolean
     */
    protected function getIsIndexViewable() {
        $viewable = true;
        return $viewable;
    }
    
    /**
     * Publicly accessible check to see if this GI_Model index/list is viewable, and sets the viewable property to avoid multiple checks
     * 
     * @return boolean
     */
    public function isIndexViewable(){
        if (is_null($this->indexViewable)) {
            $this->indexViewable = $this->getIsIndexViewable();
        }
        return $this->indexViewable;
    }
    
    public static function getUITableCols(){
        $uiTableCol = new UITableCol();
        $uiTableCol->setHeaderTitle('ID')
                ->setHeaderClass('')
                ->setHeaderHoverTitle('ID')
                ->setMethodName('getProperty')
                ->setMethodAttributes('id')
                ->setCSSClass('')
                ->setCellHoverTitleMethodName('')
                ->setCellURLMethodName('');
        return array($uiTableCol);
    }
    
    public static function getUIRolodexCols(){
        return static::getUITableCols();
    }
    
    /** @return AbstractUICardView */
    public function getUICardView(){
        return NULL;
    }
    
    public function getUITableRowClass(){
        return NULL;
    }

    public function getViewTitle($plural = true) {
        $typeTitle = $this->getTypeTitle();
        $title = $typeTitle;
        if ($plural) {
            $plTitle = $this->getTypeProperty('pl_title');
            if(!empty($plTitle)){
                return $plTitle;
            }
            $title .= 's';
        }
        return $title;
    }
    
    public function getSpecificTitle() {
        return $this->getViewTitle(false);
    }
    
    /**
     * Retrieves/Builds the model's main folder properties
     * @return array
     */
    public function getFolderProperties(){
        $defaultRef = $this->getTableName() . '_' . $this->getProperty('id');
        $folderProperties = array(
            'title' => $defaultRef,
            'ref' => $defaultRef
        );
        return $folderProperties;
    }
    
    /**
     * Retrieves the root folder for the model, default the model root folder is stored in the root user's my_files folder
     * @return Folder
     */
    public function getModelRootFolder(){
        $rootUser = UserFactory::getRootUser();
        $rootFolder = FolderFactory::getUserRootFolder($rootUser);
        if($rootFolder){
            $tableName = $this->getTableName();
            $modelRootFolder = FolderFactory::getSubFolderByRef($rootFolder, $tableName, true, true);
            if(!$modelRootFolder){
                $tableResult = TableFactory::search()
                        ->filter('system_title', $tableName)
                        ->select();
                if(!$tableResult){
                    return NULL;
                }
                $table = $tableResult[0];
                $modelRootFolder = FolderFactory::buildNewModel();
                $modelRootFolder->setProperty('title', $table->getProperty('title'));
                $modelRootFolder->setProperty('is_root', 1);
                $modelRootFolder->setProperty('user_root', 0);
                $modelRootFolder->setProperty('system', 1);
                $modelRootFolder->setProperty('ref', $tableName);
                $modelRootFolder->setProperty('user_id', Login::getUserId(true));
                if(!$modelRootFolder->save()){
                    return NULL;
                }
                
                if(!FolderFactory::linkFolderToFolder($rootFolder, $modelRootFolder)){
                    return NULL;
                }
            }
            
            return $modelRootFolder;
        }
        
        return NULL;
    }
    
    /**
     * Links the given folder (sub folder) with the model's root folder
     * @param Folder $folder
     * @return boolean or NULL
     */
    public function putFolderInModelRootFolder(Folder $folder){
        $modelRootFolder = $this->getModelRootFolder();
        if($modelRootFolder){
            return FolderFactory::linkFolderToFolder($modelRootFolder, $folder);
        }
        return NULL;
    }
    
    /**
     * Retrieves the main folder for this model
     * @param boolean $createIfMissing
     * @return Folder
     */
    public function getFolder($createIfMissing = true){
        $folderProperties = $this->getFolderProperties();
        if(is_null($this->folder)){
            $this->folder = FolderFactory::getFolderByItemIdAndTableName($this->getProperty('id'), $this->getTableName(), $createIfMissing, $folderProperties);
            if($this->folder && !$this->putFolderInModelRootFolder($this->folder)){
                return NULL;
            }
        }
        if($this->folder && isset($folderProperties['title']) && $this->folder->getProperty('title') != $folderProperties['title']){
            $this->folder->setProperty('title', $folderProperties['title']);
            $this->folder->save();
        }
        return $this->folder;
    }
    
    /**
     * Retrieves a subfolder by a ref that is within the model's main folder
     * @param string $ref
     * @param array $newSubFolderProperties
     * @return Folder
     */
    public function getSubFolderByRef($ref, $newSubFolderProperties = array()){
        $folder = $this->getFolder();
        if(empty($folder)){
            return NULL;
        }
        $subFolder = FolderFactory::getSubFolderByRef($folder, $ref);
        
        if(!$subFolder && !empty($newSubFolderProperties)){
            $subFolder = FolderFactory::buildNewModel();
            $subFolder->setProperty('is_root', 0);
            $subFolder->setProperty('user_root', 0);
            $subFolder->setProperty('system', 1);
            $subFolder->setProperty('ref', $ref);
            $subFolder->setProperty('user_id', Login::getUserId(true));
            foreach($newSubFolderProperties as $prop => $val){
                $subFolder->setProperty($prop, $val);
            }
            
            if ($subFolder->save()) {
                $linkResult = FolderFactory::linkFolderToFolder($folder, $subFolder);
                if (!$linkResult) {
                    return false;
                }
            }
        }
        
        return $subFolder;
    }
    
    public function setDAOArrayToDeleteOnSave($daoArrayToDeleteOnSave) {
        $this->daoArrayToDeleteOnSave = $daoArrayToDeleteOnSave;
    }
    
    public function getBreadcrumbs(){
        
    }
    
    /**
     * 
     * @param type $columnsToIgnore - an array of column names to ignore
     * @return boolean
     */
    public function getHasChanged($columnsToIgnore = array()) {
        $baseColumnsToIgnore = array(
            'id',
            'inception',
            'status',
            'uid',
            'last_mod',
            'last_mod_by',
        );
        $map = $this->getMap();
        $keys = $map->getKeyNames();
        foreach ($keys as $key) {
            $usedState = $map->getUsedState($key);
            if ($usedState) {
                $property = $map->getProperty($key);
                $origProperty = $map->getProperty($key, true);
                if ($property === $origProperty) {
                    $usedState = false;
                }
            }
            $keyArray = explode('.', $key);
            $columnName = $keyArray[1];
            if ($usedState == true) {
                if (!(in_array($columnName, $baseColumnsToIgnore) || in_array($columnName, $columnsToIgnore) || in_array($key, $columnsToIgnore))) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function getTypeModel($typeRef = '') {
        if (!empty($this->map)) {
            return $this->map->getTypeModel($typeRef);
        }
        return NULL;
    }
    
    public function getFilterGeneral() {
        return true;
    }

    /**
     * @param GI_Form $form
     * @return boolean
     */
    public function validateForm(GI_Form $form) {
        if (!$form->wasSubmitted()) {
            return false;
        }
        if (!$this->formValidated) {
            $this->formValidated = $form->validate();
        }
        return $this->formValidated;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractNote
     */
    public function getNotes($typeRef = 'note'){
        $notes = NoteFactory::getNotesLinkedToModel($this, $typeRef);
        return $notes;
    }
    
    public function addNote($noteContent, $typeRef = 'note', $dbType = 'client'){
        $id = $this->getId();
        if(!empty($id)){
            $note = NoteFactory::buildNewModel($typeRef);
            $note->setProperty('note', $noteContent);
            if($note->save()){
                $tableName = $this->getTableName();
                $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
                $noteLink = new $defaultDAOClass('item_link_to_note', array(
                    'dbType' => $dbType
                ));
                $noteLink->setProperty('table_name', $tableName);
                $noteLink->setProperty('item_id', $id);
                $noteLink->setProperty('note_id', $note->getId());
                return $noteLink->save();
            }
        }
        return false;
    }
    
    public function addPrivateNote($noteContent, $dbType = 'client'){
        return $this->addNote($noteContent, 'private', $dbType);
    }
    
    public function getViewURL(){
        return GI_URLUtils::buildURL($this->getViewURLAttrs());
    }
    
    public function getViewURLAttrs(){
        $reflector = new ReflectionMethod($this, 'getViewURLAttributes');
        if($reflector->getDeclaringClass()->getName() !== 'GI_Model'){
            return $this->getViewURLAttributes();
        }
        return NULL;
    }
    
    /** @deprecated since version 3.0.6 */
    public function getViewURLAttributes() {
        return $this->getViewURLAttrs();
    }
    
    public function getListBarURLAttrs(){
        return NULL;
    }
    
    public function getListBarURL(){
        $urlAttrs = $this->getListBarURLAttrs();
        if(!empty($urlAttrs)){
            return GI_URLUtils::buildURL($urlAttrs);
        }
        return NULL;
    }
    
    /** @return AbstractUser */
    public function getCreatedByUser(){
        if(is_null($this->createdBy)){
            $this->createdBy = UserFactory::getModelById($this->getProperty('uid'));
        }
        return $this->createdBy;
    }
    
    public function getCreatedByName(){
        $createdBy = $this->getCreatedByUser();
        if($createdBy){
            return $createdBy->getFullName();
        }
        return '';
    }
    
    /** @return AbstractUser */
    public function getLastModByUser(){
        if(is_null($this->lastModBy)){
            $this->lastModBy = UserFactory::getModelById($this->getProperty('last_mod_by'));
        }
        return $this->lastModBy;
    }
    
    public function getLastModByName(){
        $lastModBy = $this->getLastModByUser();
        if($lastModBy){
            return $lastModBy->getFullName();
        }
        return '';
    }
    
    public function isQuickbooksExportable() {
        return false;
    }
    
    public function getQBExportTitle(){
        return NULL;
    }
    
    public function getQuickbooksId() {
        return $this->getProperty('quickbooks_id');
    }
    
    public function getQuickbooksExportDate() {
        return $this->getProperty('quickbooks_export_date');
    }
    
    public function getQuickbooksExportedStatusHTML() {
        $icon = 'red remove';
        $formattedDate = '';
        if (!empty($this->getQuickbooksId()) || !empty($this->getQuickbooksExportDate())) {
            if ($this->getRequiresQuickbooksReExport()) {
                $icon = 'red check';
            } else {
                $icon = 'green check';
            }
            $date = $this->getQuickbooksExportDate();
            $formattedDate = '<span class="qb_export_date">' . GI_Time::formatDateForDisplay($date) . '</span>';
        }
        return '<span class="icon_wrap"><span class="icon ' . $icon . '"></span></span>' . $formattedDate;
    }
    
    public function getRequiresQuickbooksReExport() {
        if (!empty($this->getProperty('qb_re_export_required'))) {
            return true;
        }
        return false;
    }

    public static function getSearchFieldName($fieldName){
        if(!is_null(static::$searchFieldPrefix)){
            $fieldName = static::$searchFieldPrefix . '_' . $fieldName;
        }
        if(!is_null(static::$searchFieldSuffix)){
            $fieldName .= '_' . static::$searchFieldSuffix;
        }
        return $fieldName;
    }
    
    public static function getSelectAllRowsOnOff(){
        if(empty(static::$selectRowFieldName)){
            trigger_error('You must specify protected variable [$selectRowFieldName].');
            return NULL;
        }
        $fieldName = static::$selectRowFieldName . '_check_all';
        $tmpForm = new GI_Form('tmp_form');
        $checkbox = $tmpForm->getField($fieldName, 'onoff', array(
            'showLabel' => false,
            'formElementClass' => 'check_all'
        ));
        return $checkbox;
    }
    
    public function getSelectRowOnOffHoverTitle(){
        return 'Select ' . $this->getViewTitle(false);
    }
    
    public function getSelectRowOnOff(){
        if(empty(static::$selectRowFieldName)){
            trigger_error('You must specify protected variable [$selectRowFieldName].');
            return NULL;
        }
        $id = $this->getId();
        if(empty($id)){
            return NULL;
        }
        $fieldName = static::$selectRowFieldName . '[' . $id . ']';
        $tmpForm = new GI_Form('tmp_form');
        $checkbox = $tmpForm->getField($fieldName, 'onoff', array(
            'showLabel' => false,
            'onoffValue' => $id
        ));
        return $checkbox;
    }
    
    public function getTableWrapId(){
        if(isset($this->tableWrapId)){
            return $this->tableWrapId;
        }
        return $this->getTableName() . '_list';
    }
    
    /**
     * @param AbstractTag[] $tags
     * @return $this
     */
    public function setTags($tags = array()){
        $this->tags = $tags;
        return $this;
    }
    
    /** @return AbstractTag[] */
    public function getTags($typeRef = NULL, $contextRef = NULL){
        $dbType = 'client';
        if(empty($typeRef) && empty($contextRef)){
            if(is_null($this->tags)){
                $this->tags = TagFactory::getByModel($this, false, $dbType);
            }
            return $this->tags;
        }
        return TagFactory::getByModel($this, false, $dbType, $typeRef, false, $contextRef);
    }
    
    public function getTagColumnDataArray($column, $typeRef = NULL, $contextRef = NULL){
        $tableName = $this->getTableName();
        $itemId = $this->getId();
        
        $tagSearch = TagFactory::search();
        $tagTable = $tagSearch->prefixTableName('tag');
        $tagSearch->createJoin('item_link_to_tag', 'tag_id', $tagTable, 'id', 'TL')
                ->filter('TL.item_id', $itemId)
                ->filter('TL.table_name', $tableName);
        if (!empty($typeRef)) {
            $tagSearch->filterByTypeRef($typeRef);
        }
        if(!empty($contextRef)){
            $tagSearch->filter('TL.context_ref', $contextRef);
        }
        $tagSearch->setSelectColumns(array(
            $column => $column
        ));
        $results = $tagSearch->select();
        $tagInfo = array();
        if(!empty($results)){
            $tagInfo = array_column($results, $column);
        }
        return $tagInfo;
    }
    
    public function getTagIds($typeRef = NULL, $contextRef = NULL){
        return $this->getTagColumnDataArray('id', $typeRef, $contextRef);
    }
    
    public function getTagTitles($typeRef = NULL, $contextRef = NULL){
        return $this->getTagColumnDataArray('title', $typeRef, $contextRef);
    }
    
    public function saveTags($removeOldTags = true){
        if($removeOldTags){
            if(is_null($this->tags)){
                $this->tags = array();
            }
            if(!TagFactory::adjustTagsOnModel($this, $this->tags)){
                return false;
            }
        } else {
            if(is_array($this->tags)){
                foreach($this->tags as $tag){
                    if(!TagFactory::linkModelAndTag($this, $tag)){
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    public function getColour() {
        $colour = $this->getProperty('colour');
        if($colour){
            return $colour;
        }
        $id = $this->getId();
        $colour = GI_Colour::getRandomColour('default', NULL, $id);
        return $colour;
    }
    
    public function changeColour($newColour){
        $this->setProperty('colour', $newColour);
        return $this->save();
    }
    
    /**
     * @param GI_Form $form
     * @return \AbstractDeleteFormView
     */
    public function getDeleteFormView(GI_Form $form, $buildForm = false){
        $view = new GenericDeleteFormView($form, $this);
        if($buildForm){
            $view->buildForm();
        }
        return $view;
    }
    
    /**
     * @param GI_Form $form
     * @return boolean
     */
    public function handleDeleteForm(GI_Form $form){
        if ($form->wasSubmitted() && $form->validate()) {
            if($this->isDeleteable() && $this->softDelete()){
                return true;
            }
        }
        return false;
    }

    public function getTaxCodeQBId() {
        return $this->getProperty('tax_code_qb_id');
    }
    
    public function getDeletedDetailView(){
        $deletedView = new GenericDeletedDetailView($this);
        return $deletedView;
    }
    
    public function getPropertyColType($key){
        if (!is_null($this->map)) {
            return $this->map->getPropertyColType($key);
        }
        return NULL;
    }
    
    public function getPropertyForDisplay($key, $original = false){
        $value = $this->getProperty($key, $original);
        $propertyType = $this->getPropertyColType($key);
        switch($propertyType){
            case 'date':
                $displayValue = GI_Time::formatDateForDisplay($value);
                break;
            case 'time':
                $displayValue = GI_Time::formatTimeForDisplay($value);
                break;
            case 'datetime':
                $displayValue = GI_Time::formatDateTimeForDisplay($value);
                break;
            case 'textarea':
                $displayValue = GI_StringUtils::nl2brHTML($value);
                break;
            case 'money':
                $displayValue = '$' . GI_StringUtils::formatMoney($value);
                break;
            case 'decimal':
                $displayValue = GI_Math::defaultRound($value);
                break;
            default:
                $displayValue = $value;
                break;
        }
        return $displayValue;
    }
    
    public function getEditNotificationSettingsURLAttrs(AbstractEvent $event) {
        return array();
    }
    
    public function getEditNotificationSettingsURL(AbstractEvent $event) {
        $attrs = $this->getEditNotificationSettingsURLAttrs($event);
        if (!empty($attrs)) {
            return GI_URLUtils::buildURL($attrs);
        }
        return NULL;
    }

    public function getContextRoleCount() {
        return ContextRoleFactory::getContextRoleCount($this);
    }

    public function getContextRoles($includeDefaultsIfSpecific = false) {
        if (empty($this->getId())) {
            return $this->getDefaultContextRoles();
        }
        if (!$includeDefaultsIfSpecific) {
            if (empty($this->contextRoles)) {
                $search = ContextRoleFactory::search();
                $search->filter('table_name', $this->getTableName());
                if (!empty($this->getId())) {
                    $search->filter('item_id', $this->getId());
                } else {
                    $search->filterNull('item_id');
                }
                $search->orderBy('pos', 'ASC')
                        ->orderBy('id');
                $this->contextRoles = $search->select(true);
            }
            return $this->contextRoles;
        }
        $combinedSearch = ContextRoleFactory::search();
        $joinTableName = ContextRoleFactory::getDbPrefix() . 'context_role';
        $combinedSearch->filter('table_name', $this->getTableName());
        $join = $combinedSearch->createJoin('context_role', 'source_context_role_id', $joinTableName, 'id', 'SRC2', 'left');
        $join->filter('SRC2.table_name', $this->getTableName())
                ->filter('SRC2.item_id', $this->getId());
        $combinedSearch->filterGroup()
                ->filterGroup()
                ->filter('item_id', $this->getId())
                ->closeGroup()
                ->orIf()
                ->filterGroup()
                ->andIf()
                ->filterNullOr('SRC2.status')
                ->filterNull('item_id')
                ->closeGroup()
                ->closeGroup()
                ->andIf();
        $combinedSearch->groupBy('id');
        $combinedSearch->orderBy('pos', 'ASC')
                ->orderBy('id');
        return $combinedSearch->select(true);
    }

    public function getDefaultContextRoles() {
        if (empty($this->defaultContextRoles)) {
            $search = ContextRoleFactory::search();
            $search->filter('table_name', $this->getTableName())
                    ->filterNull('item_id');
            $this->defaultContextRoles = $search->select();
        }
        return $this->defaultContextRoles;
    }
    
    public function getEditContextRoleURLAttrs($contextRoleId) {
        $attrs = array();
        return $attrs;
    }
    
    public function getEditContextRoleURL($contextRoleId) {
        return GI_URLUtils::buildURL($this->getEditContextRoleURLAttrs($contextRoleId));
    }
    
    public function getAddContextRoleURLAttrs() {
        return array();
    }
    
    public function getAddContextRoleURL() {
        return GI_URLUtils::buildURL($this->getAddContextRoleURLAttrs());
    }
    
    public function getDeleteContextRoleURLAttrs($contextRoleId) {
        $attrs = array();
        return $attrs;
    }
    
    public function getDeleteContextRoleURL($contextRoleId) {
        return GI_URLUtils::buildURL($this->getDeleteContextRoleURLAttrs($contextRoleId));
    }
    
    public function getDefaultEventNotifies(AbstractEvent $event) {
        return EventNotifiesFactory::getDefaultEventNotifies($this, $event);
    }
    
    public function getNotificationViewURL() {
        $attrs = $this->getViewURLAttrs();
        if (!empty($attrs)) {
            return GI_URLUtils::buildURL($attrs, true, true);
        }
        return NULL;
    }
    
    public function getEventsLogFolder() {
        if (empty($this->eventsLogFolder)) {
            if (empty($this->getId())) {
                return NULL;
            }
            $search = FolderFactory::search();
            $folderTableName = FolderFactory::getDbPrefix() . 'folder';
            $search->join('folder_link_to_folder', 'c_folder_id', $folderTableName, 'id', 'FLTF1')
                    ->join('folder', 'id', 'FLTF1', 'p_folder_id', 'FOLD2')
                    ->join('folder_link_to_folder', 'c_folder_id', 'FOLD2', 'id', 'FLTF2')
                    ->join('folder', 'id', 'FLTF2', 'p_folder_id', 'FOLD3');
            $search->filter('ref', $this->getTableName() . '_' . $this->getId())
                    ->filter('FOLD2.ref', 'events')
                    ->filter('FOLD3.ref', 'logs');
            $results = $search->select();
            if (!empty($results)) {
                $this->eventsLogFolder = $results[0];
            } else {
                $logFolder = LogService::getLogsFolder();
                if (empty($logFolder)) {
                    return NULL;
                }
                $eventsSubFolder = FolderFactory::getSubFolderByRef($logFolder, 'events', true, false);
                if (empty($eventsSubFolder)) {
                    $eventsSubFolder = FolderFactory::buildNewModel();
                    $eventsSubFolder->setProperty('ref', 'events');
                    $eventsSubFolder->setProperty('title', 'Events');
                    $eventsSubFolder->setProperty('system', 1);
                    $eventsSubFolder->setProperty('is_root', 0);
                    $eventsSubFolder->setProperty('user_root', 0);
                    if (!($eventsSubFolder->save() && $logFolder->addSubfolder($eventsSubFolder))) {
                        return NULL;
                    }
                }
                $ref = 'event_logs_' . $this->getTableName() . '_' . $this->getId();
                $eventsLogFolder = FolderFactory::getSubFolderByRef($eventsSubFolder, $ref, true, false);
                if (empty($eventsLogFolder)) {
                    $eventsLogFolder = FolderFactory::buildNewModel();
                    $eventsLogFolder->setProperty('ref', $ref);
                    $eventsLogFolder->setProperty('title', $this->getTypeTitle() . ' ' . $this->getId() . ' Event Logs');
                    $eventsLogFolder->setProperty('system', 1);
                    $eventsLogFolder->setProperty('is_root', 0);
                    $eventsLogFolder->setProperty('user_root', 0);
                    if (!($eventsLogFolder->save() && $eventsSubFolder->addSubfolder($eventsLogFolder))) {
                        return NULL;
                    }
                }
                $this->eventsLogFolder = $eventsLogFolder;
            }
        }
        return $this->eventsLogFolder;
    }
    
    /**
     * @return AbstractGI_Uploader
     */
    public function getEventsLogUploader(){
        $uploaderRef = $this->getTypeTitle() . '_events_log';
        $id = $this->getId();
        if (empty($id)) {
            $id = 0;
        }
        $uploaderRef .= '_' . $id;
        $uploader = GI_UploaderFactory::buildUploader($uploaderRef);
        $folder = $this->getEventsLogFolder();
        if (empty($folder)) {
            return NULL;
        }
        $uploader->setTargetFolder($folder);
        $uploader->setEnabled(false);
        $uploader->setAddBrowseButton(false);
        $uploader->setAreFilesDeleteable(false);
        $uploader->setAreFilesRenamable(false);
        $uploader->setFilesLabel('Log Files');
        return $uploader;
    }
    
    public function isSubTypeOf($typeRef){
        $thisTypeRef = $this->getTypeRef();
        $factoryClassName = $this->getFactoryClassName();
        $thisTypeRefArray = $factoryClassName::getTypeRefArray($thisTypeRef);
        if(in_array($typeRef, $thisTypeRefArray)){
            return true;
        }
        return false;
    }
    
    public function getPostDeleteRedirectProps(){
        return array(
            'controller' => 'dashboard',
            'action' => 'index'
        );
    }
    
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $searchType = $dataSearch->getSearchValue('search_type');
        if (!empty($searchType) && $searchType !== 'basic') {
            $tagIds = $dataSearch->getSearchValue('tag_ids');
            if(!empty($tagIds)){
                static::addTagIdsFilterDataSearch($tagIds, $dataSearch);
            }
        }
        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){
            $searchType = filter_input(INPUT_POST, 'search_type');
            if (!empty($searchType) && $searchType !== 'basic') {
                $tagIds = explode(',', filter_input(INPUT_POST, 'search_tag_ids'));
                $dataSearch->setSearchValue('tag_ids', $tagIds, true);
            }
        }
        
        return true;
    }
    
    public static function addTagIdsFilterDataSearch($tagIds, GI_DataSearch $dataSearch){
        $dataSearch->filterByTagIds($tagIds);
    }

}
