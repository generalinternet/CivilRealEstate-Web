<?php

class ContentInvestment extends Content {
    
    public function getRefURL(){
        $ref = $this->getRef();
        if($ref){
            return GI_URLUtils::buildCleanURL(array(
                'controller' => 'static',
                'action' => 'opportunities',
                'ref' => $ref
            ));
        }
    }
    
    public function getWindowIcon() {
        return 'dollars';
    }
    
    public function getAvatarHTML(){
        return GI_StringUtils::getSVGIcon('dollars');
    }
    
    public function getViewTitle($plural = true) {
        $title = 'Investment';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    public function getView() {
        $contentView = new ContentInvestmentDetailView($this);
        return $contentView;
    }

    /**
     * Add the type to attributes in order to show the list filtered by the type
     * @return type
     */
    public function getViewURL() {
        $viewURLAttrs = $this->getViewURLAttrs();
        $viewURLAttrs['type'] = $this->getTypeRef();
        return GI_URLUtils::buildURL($viewURLAttrs);
    }
    
    /**
     * Add the type to attributes in order to show the list filtered by the type
     * @return type
     */
    public function getEditURL(){
        $viewURLAttrs = $this->getEditURLAttrs();
        $viewURLAttrs['type'] = $this->getTypeRef();
        return GI_URLUtils::buildURL($viewURLAttrs);
    }
    
    /**
     * Add the type to attributes in order to show the list filtered by the type
     * @return type
     */
    public function getDeleteURL(){
        $viewURLAttrs = $this->getDeleteURLAttrs();
        $viewURLAttrs['type'] = $this->getTypeRef();
        return GI_URLUtils::buildURL($viewURLAttrs);
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = $this->buildNewFormView($form);
        $uploader = $this->getUploader($form);
        $uploader->setFilesLabel('Downloadable Files');
        $uploader->setFileLimit(1);
        $contentFormView->setUploader($uploader);
        
        $featuredImageUploader = $this->getFeaturedImageUploader($form);
        $featuredImageUploader->setFileLimit(1);
        $contentFormView->setFeaturedImageUploader($featuredImageUploader);
        
        $bannerImageUploader = $this->getBannerImageUploader($form);
        $bannerImageUploader->setFileLimit(1);
        $contentFormView->setBannerImageUploader($bannerImageUploader);
        
        $downloadThumbImageUploader = $this->getDownloadThumbImgUploader($form);
        $downloadThumbImageUploader->setFileLimit(1);
        $contentFormView->setDownloadThumbImageUploader($downloadThumbImageUploader);
        
        $this->addCustomUploaders($form, $contentFormView);
        
        $contentFormView->setShowRef(true);
        if($buildForm){
            $contentFormView->buildForm();
        }
        return $contentFormView;
    }
    
    public function addCustomUploaders(\GI_Form $form, \GI_View $formView) {
        
    }
    
    protected function buildNewFormView(\GI_Form $form){
        return new ContentInvestmentFormView($form, $this, false);;
    }
    
    protected function getFeaturedImageUploader(GI_Form $form = NULL){
        if($this->getId()){
            $appendName = 'edit_featured_image' . $this->getId();
        } else {
            $appendName = 'add_featured_image';
            $contentNumber = $this->getContentNumber();
            if(!empty($contentNumber)){
                $appendName .= '_' . $contentNumber;
            }
        }
        
        $uploader = GI_UploaderFactory::buildImageUploader('content_' . $appendName);
        $uploader->setFilesLabel('Featured Image');
        $uploader->setBrowseLabel('Upload Image');
        $uploader->setDescription('For optimal Feature Image size, we strongly recommend 800 x 800 pixels');
        $folder = $this->getFeaturedImageFolder();
        
        $uploader->setTargetFolder($folder);
        if($form){
            $uploader->setForm($form);
        }
        
        return $uploader;
    }
    
    public function getFeaturedImageFolder(){
        return $this->getSubFolderByRef('featured_image', array(
            'title' => 'Featured Image'
        ));
    }
    
    public function getFeaturedImageFile(){
        $folder = $this->getFeaturedImageFolder();
        if($folder){
            $files = $folder->getFiles();
            if (!empty($files)) {
                return $files[0];
            }
        }
        return NULL;
    }
    
    public function getFeaturedImageLink($width = 790, $height = 446){
        $folder = $this->getFeaturedImageFolder();
        if($folder){
            $files = $folder->getFiles();
            if (!empty($files)) {
                $file = $files[0];
                return $file->getResizedImage($width, $height);
            }
        }
        return NULL;
    }
    
    protected function getBannerImageUploader(GI_Form $form = NULL){
        if($this->getId()){
            $appendName = 'edit_banner_image' . $this->getId();
        } else {
            $appendName = 'add_banner_image';
            $contentNumber = $this->getContentNumber();
            if(!empty($contentNumber)){
                $appendName .= '_' . $contentNumber;
            }
        }
        
        $uploader = GI_UploaderFactory::buildImageUploader('content_' . $appendName);
        $uploader->setFilesLabel('Banner Image');
        $uploader->setBrowseLabel('Upload Image');
        $uploader->setDescription('For optimal Banner Image size, we strongly recommend 2560 x 1440 pixels');
        $folder = $this->getBannerImageFolder();
        
        $uploader->setTargetFolder($folder);
        if($form){
            $uploader->setForm($form);
        }
        
        return $uploader;
    }
    
    public function getBannerImageFolder(){
        return $this->getSubFolderByRef('banner_image', array(
            'title' => 'Banner Image'
        ));
    }
    
    public function getBannerImageFile(){
        $folder = $this->getBannerImageFolder();
        if($folder){
            $files = $folder->getFiles();
            if (!empty($files)) {
                return $files[0];
            }
        }
        return NULL;
    }
    
    public function getBannerImageLink($width = 1920, $height = 476){
        $folder = $this->getBannerImageFolder();
        if($folder){
            $files = $folder->getFiles();
            if (!empty($files)) {
                $file = $files[0];
                return $file->getResizedImage($width, $height);
            }
        }
        return NULL;
    }

    protected function getDownloadThumbImgUploader(GI_Form $form = NULL){
        if($this->getId()){
            $appendName = 'edit_download_thumb_image' . $this->getId();
        } else {
            $appendName = 'add_download_thumb_image';
            $contentNumber = $this->getContentNumber();
            if(!empty($contentNumber)){
                $appendName .= '_' . $contentNumber;
            }
        }
        
        $uploader = GI_UploaderFactory::buildImageUploader('content_' . $appendName);
        $uploader->setFilesLabel('Download Thumbnail Image');
        $uploader->setBrowseLabel('Upload Image');
        $uploader->setDescription('For optimal Thumbnail Image size, we strongly recommend 800 x 800 pixels');
        $folder = $this->getDownloadThumbImgFolder();
        
        $uploader->setTargetFolder($folder);
        if($form){
            $uploader->setForm($form);
        }
        
        return $uploader;
    }
    
    public function getDownloadThumbImgFolder(){
        return $this->getSubFolderByRef('download_thumbnail_image', array(
            'title' => 'Download Thumbnail Image'
        ));
    }
    
    public function getDownloadThumbImgFile(){
        $folder = $this->getDownloadThumbImgFolder();
        if($folder){
            $files = $folder->getFiles();
            if (!empty($files)) {
                return $files[0];
            }
        }
        return NULL;
    }
    
    public function getDownloadThumbImgLink($width = 1920, $height = 476){
        $folder = $this->getDownloadThumbImgFolder();
        if($folder){
            $files = $folder->getFiles();
            if (!empty($files)) {
                $file = $files[0];
                return $file->getResizedImage($width, $height);
            }
        }
        return NULL;
    }

     public function handleFormSubmission(\GI_Form $form) {
        $featuredImageUploader = $this->getFeaturedImageUploader($form);
        $bannerImageUploader = $this->getBannerImageUploader($form);
        $downloadThumbImageUploader = $this->getDownloadThumbImgUploader($form);
         
        if (parent::handleFormSubmission($form)) {
            if($featuredImageUploader){
                $featuredImageUploader->setTargetFolder($this->getFeaturedImageFolder());
                FolderFactory::putUploadedFilesInTargetFolder($featuredImageUploader);
            }
            
            if($bannerImageUploader){
                $bannerImageUploader->setTargetFolder($this->getBannerImageFolder());
                FolderFactory::putUploadedFilesInTargetFolder($bannerImageUploader);
            }
            
            if($downloadThumbImageUploader){
                $downloadThumbImageUploader->setTargetFolder($this->getDownloadThumbImgFolder());
                FolderFactory::putUploadedFilesInTargetFolder($downloadThumbImageUploader);
            }
            return true;
        }
        return false;
    }
    
    protected function setPropertiesFromForm(GI_Form $form){
        parent::setPropertiesFromForm($form);
        
        $investStatus = filter_input(INPUT_POST,  $this->getFieldName('invest_status'));
        $this->setProperty('content_investment.invest_status', $investStatus);
        
        $dueDate = filter_input(INPUT_POST,  $this->getFieldName('due_date'));
        $this->setProperty('content_investment.due_date', $dueDate);

        $expectedReturns = filter_input(INPUT_POST,  $this->getFieldName('expected_returns'));
        $this->setProperty('content_investment.expected_returns', $expectedReturns);

        $fundsRate = filter_input(INPUT_POST,  $this->getFieldName('funds_rate'));
        $this->setProperty('content_investment.funds_rate', $fundsRate);
        
        $targetAmt = filter_input(INPUT_POST,  $this->getFieldName('target_amt'));
        $this->setProperty('content_investment.target_amt', $targetAmt);
        
        $investedAmt = filter_input(INPUT_POST,  $this->getFieldName('invested_amt'));
        $this->setProperty('content_investment.invested_amt', $investedAmt);
        
        $featuredYoutubeVideoURL = filter_input(INPUT_POST,  $this->getFieldName('featured_youtube_video_url'));
        $this->setProperty('content_investment.featured_youtube_video_url', $featuredYoutubeVideoURL);

        $isFeaturedInvestment = filter_input(INPUT_POST,  $this->getFieldName('is_featured_investment'), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if(!$isFeaturedInvestment){
            $isFeaturedInvestment = 0;
        }else{
            $isFeaturedInvestment = $isFeaturedInvestment[0];
        }
        $this->setProperty('content_investment.is_featured_investment', $isFeaturedInvestment);

        return true;
    }
    
    public function getInvestmentStatusTitle() {
        $investStatus = $this->getProperty('content_investment.invest_status');
        if (!empty($investStatus) && array_key_exists($investStatus, ContentFactory::$OPITIONS_INVEST_STATUS)) {
            return ContentFactory::$OPITIONS_INVEST_STATUS[$investStatus];
        }
        return '';
    }
    
    public function getDisplayFundsRate() {
        $fundsRate = $this->getProperty('content_investment.funds_rate');
        if (!empty($fundsRate)) {
            return $fundsRate.' %';
        }
        return '0 %';
    }

    public function getExpectedReturns() {
        return $this->getProperty('content_investment.expected_returns');
    }
    
    public function getStatusBarHTML() {
        $fundsRate = $this->getProperty('content_investment.funds_rate');
        if (empty($fundsRate)) {
            $fundsRate = 0;
        }
        $investStatus = $this->getProperty('content_investment.invest_status');

        $html = '<div class="status-progress">';
        $html .= '<div class="status-progress__bar-wrap">';
        $html .= '<span class="status-progress__bar '.$investStatus.'" data-rate="'.$fundsRate.'"></span>';
        $html .= '</div>'; // status-progress__bar-wrap
        $html .= '</div><!--.status-progress-->';

        return $html;
    }

    public function getFundsRateHTML(){
        // preview arr
        $previewArr = array(
            [
                'name' => 'Funded:',
                'value' => $this->getDisplayFundsRate(),
            ],
            [
                'name' => 'Status:',
                'value' => $this->getInvestmentStatusTitle(),
            ],
        );

        // investment preview
        return $this->getPreviewBlocks($previewArr, 'investment__preview-wrap_block_2 investment__preview-wrap_theme_white');

    }
    
    public function getSubtitle() {
        return '';
    }
    
    public function getTimeLeftHTML() {
        $investStatus = $this->getProperty('content_investment.invest_status');
        if ($investStatus != 'new') {
            //Funding is closed, so set due date before current date
            $dueDate = '1999-12-31';
        } else {
            $dueDate = $this->getProperty('content_investment.due_date');
        }
        $dueDateObject = new DateTime($dueDate);
        $dueDateObject->add(new DateInterval('P1D'));
        $endDatetime = $dueDateObject->format('Y/m/d H:i:s');
        
        $html = '<div class="investment__preview-wrap investment__preview-wrap_theme_white">';
            $html .= '<p class="investment__preview-block">';
                $html .= '<span class="investment__preview-name">Time left: </span>';
                $html .= '<span class="investment__preview-value investment__preview-value_type_timeleft" data-end-datetime="'.$endDatetime.'"><span class="timer"></span></span>';
            $html .= '</p>'; // close preview-block    
        $html .= '</div>'; // close preview-block

        return $html;
    }
    
    public function getFeaturedYoutubeVideoEmbedHTML() {
        $featuredYoutubeVideoURL = $this->getProperty('content_investment.featured_youtube_video_url');
        if (!empty($featuredYoutubeVideoURL)) {
            return GI_StringUtils::embedYouTube($featuredYoutubeVideoURL);
        }
        return '';
    }
    
    public function getDisplayDueDate() {
        $dueDate = $this->getProperty('content_investment.due_date');
        if (!empty($dueDate)) {
            return $dueDate;
        }
        return '';
    }
    
    /**
     * @param string $contentTypeRef
     * @return array
     */
    public function getBreadcrumbs($contentTypeRef = NULL) {
        $breadcrumbs = array();
        $bcIndexLink = GI_URLUtils::buildURL(array(
            'controller' => 'content',
            'action' => 'index',
            'type' => 'investment',
            'general' => 1,
        ));
        $breadcrumbs[] = array(
            'label' => 'All Deals',
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
    
    /*
     * List item view 
     */
    public function getListItemDetailView() {
        return new ContentInvestmentListItemDetailView($this);
    }
    
    public function getFeaturedMediaBlockHTML($type = 'featured', $youtubeThumbQuality = "mqdefault") {
        //Get a featured image
        $featuredImageHTML = $this->getFeaturedImageHTML($type);
        //If there is a YouTube video, get the video
        $youtubeVideoId = ContentFactory::getFeaturedYoutubeVideoId($this);
        $classNames = 'investment__featured-media-wrap';

        if(empty($featuredImageHTML) && empty($youtubeVideoId)){
            $classNames .= ' investment__featured-media-wrap_no-image';
        }

        $html = '<div class="'.$classNames.'">';
        
        if(!empty($youtubeVideoId)){
            $html .= '<div class="youtube-embeded embed-responsive embed-responsive-16by9">';
            $html .= '<div class="youtube-player youtube-embeded__player" data-id="'.$youtubeVideoId.'">';
            $html .= '<div class="youtube-embeded__placeholder place_holder">';
            $html .= '<img src="https://img.youtube.com/vi/'.$youtubeVideoId.'/'.$youtubeThumbQuality.'.jpg" alt="Youtube Video Placeholder" class="placeholder-bg">'; 
            $html .= '<div class="video-btn youtube-embeded__play-icon">';
            $html .= '<img src="resources/media/img/icons/icon_play_red.svg" alt="YouTube Play Icon" title="Play">';
            $html .= '</div>';
            $html .= '</div>';// place_holder
            $html .= '</div>';// youtube-player
            $html .= '</div><!--.video-container-->';

        } else if(!empty($featuredImageHTML)){
            $html .= $featuredImageHTML;
        }

        $html .= '</div>';
        return $html;
    }
    
    protected function getFeaturedImageHTML($type = 'featured'){
        $html = '';
        $featuredImage = $this->getFeaturedImageFile();
        if($featuredImage){
            $fileView = $featuredImage->getView($type);
            $html .= $fileView->getHTMLView();
        }
        return $html;
    }
    
    public function getDisplayTargetAmt() {
        $targetAmt = $this->getProperty('content_investment.target_amt');
        return ContentFactory::formatMoney($targetAmt);
    }
    
    public function getDisplayInvestedAmt() {
        $investedAmt = $this->getProperty('content_investment.invested_amt');
        return ContentFactory::formatMoney($investedAmt);
    }
    
    public function getFirstFileURL() {
        $folder = $this->getFolder(false);
        if($folder){
            $files = $folder->getFiles();
            if (!empty($files)) {
                $firstFile = $files[0];
                if (!empty($firstFile)) {
                    return $firstFile->getFileURL();
                }
            }
        }
        return NULL;
    }
    
    public function getTitleBockHTML() {
        $html = '<div class="investment__title-wrap">'; 
        $html .= '<h2 class="investment__title investment__title_type_main">'.$this->getTitle().'</h2>';
        $html .= '<div class="investment__title investment__title_type_sub">'.$this->getSubtitle().'</div>';
        $html .= '</div>';
        return $html;
    }
    
    public function getPreviewBockHTML() {
        $dataArr = array(
            [
                'name' => 'Invested',
                'value' => $this->getDisplayInvestedAmt(),
            ],
            [
                'name' => 'Target',
                'value' => $this->getDisplayTargetAmt(),
            ],
        );
        return $this->getPreviewBlocks($dataArr, 'investment__preview-wrap_block_2');
    }
    
    public function getStatusDetailBockHTML() {
        $html = $this->getStatusBarHTML();
        $html .= $this->getFundsRateHTML();
        $html .= $this->getTimeLeftHTML();
        return $html;
        
    }
    
    public function getSiteTitle() {
        return $this->getTitle();
    }
    
    public function getInvestmentCustomDetailView() {
        return new ContentInvestmentCustomDetailView($this);
    }
    
    public function getInvestmentItemDetailBlockHTML() {
        //@todo
        return '';
    }
    
    
    /**
     * Custom detail view
     */
    public function getCustomView() {
        $id = $this->getId();
        $fileURL = 'concrete/static/view/investment/investment_'. $id .'_detailView.php';
    
        if(file_exists($fileURL)){
            require_once($fileURL);
            $customViewClass = 'Investment'.$id.'DetailView';
            $customView = new $customViewClass();
            return $customView;
        }
        return NULL;
    }
    
    public function getDownloadFilesHTML() {
        $curUser = Login::getUser();
        $html = '';
        $target = '';
        if (empty($curUser)) {
            //Link to signup
            $viewText = 'Sign Up For Investment Package';
            $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'user',
                'action' => 'signup',
                'source' => 'opportunities',
                'sourceRef' => $this->getRef()
            ), false, true);
        } else {
            //Link to download
            $viewText = 'Download Investment Package';
            $target = ' target="_blank"';
            $linkURL = $this->getFirstFileURL();
        }
        if (!empty($linkURL)) {
            $html .= '<div class="download_doc_img">';

            if(empty($downloadThumbSrc)){
                $html .= '<div class="investment__featured-media-wrap_no-image investment__featured-media-wrap_square"></div>';
            }else{
                $downloadThumbSrc = $this->getDownloadThumbImgLink();
                $html .= '<img src="'.$downloadThumbSrc.'" alt="document image" title="document">';
            }

            $html .= '</div>';
            $html .= '<a href="'.$linkURL.'" class="button button_theme_primary button_has-icon"'.$target.'>'.$viewText.'<span class="button__icon button__icon_color_dark"></span></a>';
        }
        
        return $html;
    }

    protected function getPreviewBlocks($previewArr, $additionalClasses = ""){
        $html = '<div class="investment__preview-wrap '.$additionalClasses.'">';
        foreach($previewArr as $previewItem){
            if(empty($previewItem['value']) && $previewItem['value'] !== 0){
                continue;
            }
            $html .= '<p class="investment__preview-block">';
                $html .= '<span class="investment__preview-name">'.$previewItem['name'].' </span>';
                $html .= '<span class="investment__preview-value">'.$previewItem['value'].'</span>';
            $html .= '</p>'; // close preview-block    
        }
        $html .= '</div>'; // close preview-block
        return $html;
    }

    /*
     * List item view 
     */
    public function getSliderItemDetailView() {
        return new ContentInvestmentSliderItemDetailView($this);
    }

    public function getTypeHeaderBlock(){
        $viewTitle = $this->getViewTitle();
        $typeRef = $this->getTypeRef();
        $categoryTitle = $this->getCategoryRef();
        if(empty($categoryTitle)){
            $categoryTitle = $viewTitle;
        }

        $categoryColorStyle = '';
        $categoryColor = $this->getCategoryColor();
        if(!empty($categoryColor)){
            $categoryColorStyle = 'style="background: '.$categoryColor.';"';
        }
        
        $html = '<div class="investment__header-wrap">';
        $html .= '<div class="investment__header">';
        $html .= '<div class="investment__header-image">';
        $html .= '<img src="resources/media/img/icons/service_icon_'.$typeRef.'.png" alt="'.$viewTitle.'" class="featured-icon">';
        $html .= '</div>'; // investment__header-image
        $html .= '<h3 class="investment__header-title investment__header-title_color_grey">'.SITE_NAME.'</h3>';
        $html .= '<h3 class="investment__header-title investment__header-title_color_white">'.$viewTitle.'</h3>';
        $html .= '</div>'; // header
        $html .= '<p class="investment__header-title-bottom" '.$categoryColorStyle.'>'.$categoryTitle.'</p>';
        $html .= '</div>'; // header-wrap

        return $html;
    }

    
    public function getDetailPreviewBlockHTML(){
    }

    public function getChildTypes(){
        $childTypes = ContentAvailableChildTypeFactory::search()
                ->filter('p_content_ref', 'investment')
                ->orderBy('pos', 'ASC')
                ->select();
        
        return $childTypes;
    }

    public function getChildContentView(){
        $html = '';

        if(Login::isLoggedIn()){
            $innerContents = $this->getInnerContent();
            foreach($innerContents as $innerContent){
                $view = $innerContent->getView();
                $html .= $view->getPublicViewHTML();
            }
        } else {
            $html .= $this->getUnregisterPlaceholder("Deal detail", 'placeholder_deal_detail');
        }

        return $html;
    }

    public function getUnregisterPlaceholder($title = 'Summary', $imageName = 'placeholder_deal_summary'){
        $registerURL = GI_URLUtils::buildURL(array(
            'controller' => 'user',
            'action' => 'signup',
            'source' => 'opportunities',
            'sourceRef' => $this->getRef()
        ), false, true);

        $msg = "To gain access to the full investment deal sign in or sign up here"; // To gain access to the full investment package, sign in here. If you are not a registered user yet, please click here to register.

        $html = '<div class="row investment__detail-info-row">';
        $html .= '<div class="col-xs-12"><h3 class="investment__general-info-title">'.$title.'</h3></div>';
        $html .= '<div class="col-xs-12">';
            $html .= '<div class="investment__unregisterd-placeholder">';
            $html .= '<a class="investment__unregisterd-placeholder-link" href="'.$registerURL.'" title="Click to register">';
            $html .= '<img class="investment__unregisterd-placeholder-image" src="resources/media/img/investment/'.$imageName.'.jpg" alt="Investor register">';
            $html .= '<p class="investment__unregisterd-placeholder-text">'.$msg.'</p>';
            $html .= '</a>';
            $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function getCategoryRef(){
        // child class implementation
    }
    protected function getCategoryColor(){
        // child class implementation
    }
    public function isFeaturedListing(){
        $isFeaturedInvestment = $this->getProperty('content_investment.is_featured_investment');
        return ($isFeaturedInvestment) ? 'Yes' : 'No';
    }

    public function getDownloadFileSection(){
        $downloadHTML = $this->getDownloadFilesHTML();
        if(empty($downloadHTML)){
            return '';
        }

        $html = '';
        $html .= '<div class="row investment__detail-info-row">';
            $html .= '<div class="col-xs-12"><h3 class="investment__general-info-title">Download file</h3></div>';
            $html .= '<div class="col-xs-12">';
                $html .= '<div class="investment__general-info-item-value investment__general-info-item-value_type_download">'.$downloadHTML.'</div>';
            $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}
