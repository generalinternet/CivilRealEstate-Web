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
    public function getTags(){
        if(is_null($this->tags)){
            $this->tags = TagFactory::getByModel($this);
        }
        return $this->tags;
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
            default:
                $displayValue = $value;
                break;
        }
        return $displayValue;
    }

}
