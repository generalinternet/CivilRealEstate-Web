<?php
/**
 * Description of AbstractContent
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContent extends GI_Model {
    
    protected $contentNumber = 0;
    protected $parentNumber = NULL;
    protected $uploadsEnabled = true;
    /** @var AbstractContent */
    protected $parentContent = NULL;
    /** @var AbstractTag[] */
    protected $tags = NULL;
    protected $tagLimit = 0;
    protected $childCount = NULL;
    
    /**
     * @return \ContentDetailView
     */
    public function getView() {
        $contentView = new ContentDetailView($this);
        return $contentView;
    }
    
    public function getViewURLAttrs(){
        $attrs = array(
            'controller' => 'content',
            'action' => 'view',
            'id' => $this->getId()
        );
        return $attrs;
    }
    
    /**
     * @return string
     */
    public function getViewURL() {
        return GI_URLUtils::buildURL($this->getViewURLAttrs());
    }
    
    public function getPublicDetailViewURL() {
        return GI_URLUtils::buildURL(array(
            'controller' => 'static',
            'action' => 'opportunities',
            'ref' => $this->getRef(),
        ));
    }
    
    public function getEditURLAttrs(){
        $attrs = array(
            'controller' => 'content',
            'action' => 'edit',
            'id' => $this->getId()
        );
        return $attrs;
    }
    
    public function getEditURL(){
        return GI_URLUtils::buildURL($this->getEditURLAttrs());
    }
    
    public function getDeleteURLAttrs(){
        $attrs = array(
            'controller' => 'content',
            'action' => 'delete',
            'id' => $this->getId()
        );
        return $attrs;
    }
    
    public function getDeleteURL(){
        return GI_URLUtils::buildURL($this->getDeleteURLAttrs());
    }
    
    public function getRefURL(){
        $ref = $this->getRef();
        if($ref){
            return GI_URLUtils::buildCleanURL(array(
                'controller' => 'content',
                'action' => 'view',
                'ref' => $ref
            ));
        }
    }
    
    public function getIndexURL(){
        $typeRef = $this->getTypeRef();
        return GI_URLUtils::buildURL(array(
            'controller' => 'content',
            'action' => 'index',
            'type' => $typeRef
        ));
    }
    
    /**
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = true) {
        $title = 'Content';
        return $title;
    }
    
    /**
     * @param string $fieldName
     * @return string
     */
    public function getFieldName($fieldName){
        return $fieldName .= '_' . $this->contentNumber;
    }
    
    /**
     * @param int $number
     * @return Content
     */
    public function setContentNumber($number) {
        $this->contentNumber = $number;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getContentNumber(){
        return $this->contentNumber;
    }
    
    /**
     * @param int $number
     * @return Content
     */
    public function setParentNumber($number){
        $this->parentNumber = $number;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getParentNumber(){
        return $this->parentNumber;
    }
    
    public function getFolderProperties() {
        $folderProperties = parent::getFolderProperties();
        $folderProperties['title'] = $this->getTitle();
        return $folderProperties;
    }
    
    protected function getUploader(GI_Form $form = NULL){
        if(!$this->uploadsEnabled){
            return NULL;
        }
        if($this->getId()){
            $appendName = 'edit_' . $this->getId();
        } else {
            $appendName = 'add';
            $contentNumber = $this->getContentNumber();
            if(!empty($contentNumber)){
                $appendName .= '_' . $contentNumber;
            }
        }
        
        $uploader = GI_UploaderFactory::buildUploader('content_' . $appendName);
        $folder = $this->getFolder();
        
        $uploader->setTargetFolder($folder);
        if($form){
            $uploader->setForm($form);
        }
        
        return $uploader;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = new ContentFormView($form, $this, false);
        $uploader = $this->getUploader($form);
        $contentFormView->setUploader($uploader);
        $contentFormView->setShowRef(true);
        if($buildForm){
            $contentFormView->buildForm();
        }
        return $contentFormView;
    }
    
    /**
     * @param \GI_Form $form
     * @return boolean
     */
    public function handleFormSubmission(\GI_Form $form) {
        if ($this->validateForm($form)) {
            if($this->getContentNumber() === 0){
                $this->validateAllRefs($form);
            }
            if(!$form->fieldErrorCount()){
                if(!$this->setPropertiesFromForm($form)){
                    return false;
                }
                
                $uploader = $this->getUploader($form);
                
                if($this->save()){
                    if($uploader){
                        $uploader->setTargetFolder($this->getFolder());
                        FolderFactory::putUploadedFilesInTargetFolder($uploader);
                    }
                    
                    $tagIdString = filter_input(INPUT_POST, $this->getFieldName('tag_ids'));
                    $tagIds = explode(',', $tagIdString);
                    $tags = array();
                    foreach($tagIds as $tagId){
                        if(!empty($tagId)){
                            $tags[] = TagFactory::getModelById($tagId);
                        }
                    }
                    
                    if(!TagFactory::adjustTagsOnModel($this, $tags)){
                        return false;
                    }
                    
                    $parentNum = filter_input(INPUT_POST, $this->getFieldName('parent_number'));
                    if(is_null($parentNum)){
                        return $this->handleChildFormSubmission($form);
                    }
                }
            }
        }
        return false;
    }
    
    protected function setPropertiesFromForm(GI_Form $form){
        $ref = filter_input(INPUT_POST,  $this->getFieldName('ref'));
        $cleanRef = GI_Sanitize::ref($ref);
        $this->setProperty('ref', $cleanRef);

        $title = filter_input(INPUT_POST, $this->getFieldName('title'));
        $this->setProperty('title', $title);

        $this->setPropertyIfPostIsset('title_tag', $this->getFieldName('title_tag'));

        $htmlClass = filter_input(INPUT_POST, $this->getFieldName('html_class'));
        if(!is_null($htmlClass)){
            $this->setProperty('html_class', GI_Sanitize::specialChars($htmlClass));
        }
        return true;
    }
    
    protected function validateAllRefs(\GI_Form $form){
        $contentNums = filter_input(INPUT_POST, 'content_numbers', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        
        $usedRefs = array();
        
        foreach($contentNums as $contentNum){
            $refFieldName = 'ref_' . $contentNum;
            $ref = filter_input(INPUT_POST,  $refFieldName);
            if(!empty($ref)){
                if(in_array($ref, $usedRefs)){
                    $form->addFieldError($refFieldName, 'not_unique', 'Provided reference has already been used.');
                }
                $reasonCleaned = NULL;
                $cleanRef = GI_Sanitize::ref($ref, $reasonCleaned);
                if($cleanRef != $ref){
                    $form->addFieldError($refFieldName, 'unclean', 'Provided reference ' . $reasonCleaned . '.');
                } else {
                    $existingRefSearch = ContentFactory::search()
                            ->filter('ref', $cleanRef);
                    if(!empty($this->getId())){
                        $existingRefSearch->filterNotEqualTo('id', $this->getId());
                    }
                    $existingRef = $existingRefSearch->count();
                    if($existingRef){
                        $form->addFieldError($refFieldName, 'not_unique', 'Provided reference has already been used.');
                    }
                }

                $usedRefs[] = $ref;
            }
        }
    }
    
    public function handleChildFormSubmission(\GI_Form $form){
        $children = $this->getInnerContent($form);
        
        $parentId = $this->getId();
        
        $existingLinkResult = ContentInContentFactory::search()
                ->filter('p_content_id', $parentId)
                ->select();
        $existingLinks = array();
        foreach($existingLinkResult as $existingLink){
            $existingLinks[$existingLink->getProperty('c_content_id')] = $existingLink;
        }
        
        $pos = 0;
        foreach($children as $child){
            if (!$child->handleFormSubmission($form)) {
                return false;
            }
            
            $childId = $child->getId();
            if(isset($existingLinks[$childId])){
                $childLink = $existingLinks[$childId];
                unset($existingLinks[$childId]);
            } else {
                $childLink = ContentInContentFactory::buildNewModel();
                $childLink->setProperty('p_content_id', $parentId);
                $childLink->setProperty('c_content_id', $childId);
            }
            
            $childLink->setProperty('pos', $pos);
            
            if(!$childLink->save()){
                return false;
            }
            $pos++;
        }
        
        foreach($existingLinks as $existingLink){
            $existingLink->softDelete();
        }
        
        return true;
    }

    /**
     * @return UITableCol[]
     */
    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Title',
                'method_name' => 'getTitle',
                'cell_url_method_name' => 'getViewURL',
                'css_class' => ''
            ),
            array(
                'header_title'=>'Created',
                'method_name'=>'getCreatedDate'
            ),
            array(
                'header_title'=>'Modified',
                'method_name'=>'getModifiedDate'
            )
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getUICardView() {
        $cardView = new UICardView($this);
        $cardView->setTitle($this->getTitle());
        $cardView->setSubtitle($this->getCreatedDate());
        $cardView->setAvatarHTML($this->getAvatarHTML());
        return $cardView;
    }
    
    public function getAvatarHTML(){
        return GI_StringUtils::getSVGIcon('content');
    }
    
    public function getCreatedDate() {
        $date = $this->getProperty('inception');
        if(!empty($date)){
            return GI_Time::formatDateForDisplay($date);
        }
        return NULL;
    }
    
    public function getModifiedDate() {
        $date = $this->getProperty('last_mod');
        if(!empty($date)){
            return GI_Time::formatDateForDisplay($date);
        }
        return NULL;
    }
    
    public function getTitle(){
        $title = $this->getProperty('title');
        if(!$title){
            $title = '';
            $parentContent = $this->getParentContent();
            if($parentContent){
                $title .= $parentContent->getTitle() . ' - ';
            }
            $title .= $this->getTypeTitle();
        }
        return $title;
    }
    
    public function getRef(){
        return $this->getProperty('ref');
    }
    
    public function getTitleTag(){
        $titleTag = $this->getProperty('title_tag');
        if(empty($titleTag)){
            $titleTag = 'span';
            if(empty($this->getId())){
                $titleTag = 'h2';
            }
        }
        return $titleTag;
    }
    
    public function getHTMLClass(){
        return $this->getProperty('html_class');
    }
    
    /**
     * @param string $contentTypeRef
     * @return array
     */
    public function getBreadcrumbs($contentTypeRef = NULL) {
        $breadcrumbs = array();
        $bcIndexLink = GI_URLUtils::buildURL(array(
            'controller' => 'content',
            'action' => 'index'
        ));
        $breadcrumbs[] = array(
            'label' => 'All Content',
            'link' => $bcIndexLink
        );
        if (empty($contentTypeRef)) {
            $contentTypeRef = $this->getTypeRef();
        }
        if(!empty($contentTypeRef)){
            $bcLink = GI_URLUtils::buildURL(array(
                'controller' => 'content',
                'action' => 'index',
                'type' => $contentTypeRef
            ));
            $breadcrumbs[] = array(
                'label' => $this->getViewTitle(),
                'link' => $bcLink
            );
        }
        $contentId = $this->getId();
        if (!is_null($contentId)) {
            $breadcrumbs[] = array(
                'label' => $this->getTitle(),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }
    
    /**
     * @param \GI_Form $form
     * @return \Content[]
     */
    public function getInnerContent(\GI_Form $form = NULL) {
        $innerContent = array();
        if(!is_null($form) && $form->wasSubmitted()){
            $childNums = filter_input(INPUT_POST, $this->getFieldName('children_of'), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if($childNums){
                foreach($childNums as $childNum){
                    $typeRef = filter_input(INPUT_POST, 'type_ref_' . $childNum);
                    $childId = filter_input(INPUT_POST, 'content_id_' . $childNum);
                    if(empty($childId)){
                        $childContent = ContentFactory::buildNewModel($typeRef);
                    } else {
                        $childContent = ContentFactory::getModelById($childId);
                    }
                    $childContent->setContentNumber($childNum)
                            ->setParentNumber($this->getContentNumber());
                    $innerContent[] = $childContent;
                }
            }
        } else {
            $contentTable = dbConfig::getDbPrefix() . 'content';
            $innerContent = ContentFactory::search()
                    ->join('content_in_content', 'c_content_id', $contentTable, 'id', 'cic')
                    ->filter('cic.p_content_id', $this->getId())
                    ->orderBy('cic.pos', 'ASC')
                    ->select();
        }
        
        if(empty($innerContent)){
            $innerContent = $this->getDefaultInnerContent();
        }
        return $innerContent;
    }
    
    protected function getDefaultInnerContent(){
        $innerContent = array();
        $availableTypes = $this->getChildTypes();
        foreach($availableTypes as $availableType){
            $minChildren = $availableType->getProperty('min_children');
            if($minChildren > 0){
                for($i=0; $i<$minChildren; $i++){
                    $childContent = ContentFactory::buildNewModel($availableType->getProperty('c_content_ref'));
                    $childContent->setParentNumber($this->getContentNumber());
                    $innerContent[] = $childContent;
                }
            }
        }
        return $innerContent;
    }
    
    public function getChildTypes(){
        $childTypes = ContentAvailableChildTypeFactory::search()
                ->filter('p_content_ref', $this->getTypeRef())
                ->orderBy('pos', 'ASC')
                ->select();
        
        return $childTypes;
    }
    
    public static function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        return $dataSearch;
    }
    
    /** @param GI_DataSearch $dataSearch */
    public static function addSortingToDataSearch(GI_DataSearch $dataSearch){
        $dataSearch->orderBy('last_mod', 'DESC');
        $dataSearch->orderBy('inception', 'DESC');
        return $dataSearch;
    }
    
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \ContentSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new ContentSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $searchType = $dataSearch->getSearchValue('search_type');
        if (empty($searchType) || $searchType === 'basic') {
            //Basic Search
            $basicSearchField = $dataSearch->getSearchValue('basic_search_field');
            if(!empty($basicSearchField)){
                static::addBasicSearchFieldFilterToDataSearch($basicSearchField, $dataSearch);
            }
        } else {
            $title = $dataSearch->getSearchValue('title');
            if(!empty($title)){
                static::addTitleFilterToDataSearch($title, $dataSearch);
            }
            
            $startDate = $dataSearch->getSearchValue('start_date');
            if(!empty($startDate)){
                static::addStartDateFilterToDataSearch($startDate, $dataSearch);
            }

            $endDate = $dataSearch->getSearchValue('end_date');
            if(!empty($endDate)){
                static::addEndDateFilterToDataSearch($endDate, $dataSearch);
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
                
                $title = filter_input(INPUT_POST, 'search_title');
                $dataSearch->setSearchValue('title', $title);
                
                $startDate = filter_input(INPUT_POST, 'search_start_date');
                $dataSearch->setSearchValue('start_date', $startDate);

                $endDate = filter_input(INPUT_POST, 'search_end_date');
                $dataSearch->setSearchValue('end_date', $endDate);
            }
        }
        
        return true;
    }
    
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, $redirectArray = array()){
        $form = new GI_Form('content_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'content',
                    'action' => 'index'
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                }
            }
            
            $redirectArray['queryId'] = $queryId;
            if(GI_URLUtils::getAttribute('ajax')){
                $redirectArray['ajax'] = 1;
                GI_URLUtils::redirect($redirectArray);
            } else {
                GI_URLUtils::redirect($redirectArray);
            }
        }
        return $searchView;
    }
    
    public static function addBasicSearchFieldFilterToDataSearch($basicSearchField, GI_DataSearch $dataSearch){
        static::addTitleFilterToDataSearch($basicSearchField, $dataSearch);
    }
    
    public static function addTitleFilterToDataSearch($title, GI_DataSearch $dataSearch){
        $dataSearch->filterTermsLike(array(
            'title',
            'ref'
        ), $title)
                ->orderByLikeScore('title', $title);
    }
    
    public static function addStartDateFilterToDataSearch($startDate, GI_DataSearch $dataSearch){
        $dataSearch->filterGreaterOrEqualTo('inception', $startDate);
    }
    
    public static function addEndDateFilterToDataSearch($endDate, GI_DataSearch $dataSearch){
        $endDateObj = new DateTime($endDate);
        $endDateObj->add(new DateInterval('P1D'));
        $dataSearch->filterLessOrEqualTo('inception', GI_Time::formatDateTime($endDateObj));
    }
    
    public function softDelete() {
        $contentIns = ContentInContentFactory::search()
                ->filter('p_content_id', $this->getId())
                ->select();
        foreach($contentIns as $contentIn){
            $contentIn->softDelete();
        }
        return parent::softDelete();
    }
    
    public function getIsAddable() {
        if(Permission::verifyByRef('add_content')){
            return true;
        }
        return false;
    }
    
    public function getIsIndexViewable() {
        if(Permission::verifyByRef('view_content_index') && $this->getTypeRef() == 'content'){
            return true;
        }
        return false;
    }
    
    public function getIsViewable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('view_content')){
            return true;
        }
        return false;
    }
    
    public function getIsEditable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('edit_content')){
            return true;
        }
        return false;
    }
    
    public function getIsDeleteable(){
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('delete_content')){
            return true;
        }
        return false;
    }
    
    /** @return AbstractContent */
    public function getParentContent(){
        if(is_null($this->parentContent)){
            $contentTable = dbConfig::getDbPrefix() . 'content';
            $result = ContentFactory::search()
                    ->join('content_in_content', 'p_content_id', $contentTable, 'id', 'pic')
                    ->filter('pic.c_content_id', $this->getId())
                    ->orderBy('pic.pos', 'ASC')
                    ->select();
            if($result){
                $parentContent = $result[0];
                $this->parentContent = $parentContent;
            }
        }
        return $this->parentContent;
    }
    
    /** @return AbstractTag */
    public function getTags(){
        if(is_null($this->tags)){
            $this->tags = TagFactory::getByModel($this);
        }
        return $this->tags;
    }
    
    public function getTagIds(){
        $tags = $this->getTags();
        $tagIds = array();
        if($tags){
            foreach($tags as $tag){
                $tagIds[] = $tag->getId();
            }
        }
        return $tagIds;
    }
    
    public function getTagLimit(){
        return $this->tagLimit;
    }
    
    public function redirectToParent(){
        return false;
    }
    
    public function getChildCount(){
        if(is_null($this->childCount)){
            $search = ContentFactory::search();
            $contentTable = $search->prefixTableName('content');
            $search->join('content_in_content', 'c_content_id', $contentTable, 'id', 'LINK');
            $search->filter('LINK.p_content_id', $this->getId());
            
            $this->childCount = $search->count();
        }
        return $this->childCount;
    }
    
    public function getListBarURL(){
        $urlAttrs = array(
            'controller' => 'content',
            'action' => 'index',
            'type' => $this->getTypeRef()
        );
        if($this->getId()){
            $urlAttrs['curId'] = $this->getId();
        }
        return GI_URLUtils::buildURL($urlAttrs);
    }
    
    public function getAutocompResult($term = NULL){
        $title = $this->getAutocompTitle();
        $autoResult = '<span class="result_text">';
        $autoResult .= GI_StringUtils::markTerm($term, $title);
        $autoResult .= '</span>';
        $result = array(
            'label' => $title,
            'value' => $this->getId(),
            'autoResult' =>  $autoResult,
        );
        return $result;
    }
    
    public function getAutocompTitle(){
        return $this->getTitle();
    }
    
    public function getTextSearchableColumns($useTypeRefAsAlias = true){
        $columns = array('title',
            'ref');
        return $columns;
    }
    
}
