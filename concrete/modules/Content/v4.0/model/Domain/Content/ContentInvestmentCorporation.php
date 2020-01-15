<?php

class ContentInvestmentCorporation extends ContentInvestment {
    public static $TYPE_OPPORTUNITY_REF = 'opportunities';
    public function getView() {
        $contentView = new ContentInvestmentCorporationDetailView($this);
        return $contentView;
    }
    
    protected function buildNewFormView(\GI_Form $form){
        return new ContentInvestmentCorporationFormView($form, $this, false);
    }
    
    public function addCustomUploaders(\GI_Form $form, \GI_View $formView) {
        $logoImageUploader = $this->getLogoImageUploader($form);
        $logoImageUploader->setFileLimit(1);
        $formView->setLogoImageUploader($logoImageUploader);
    }
    
    protected function getLogoImageUploader(GI_Form $form = NULL){
        if($this->getId()){
            $appendName = 'edit_logo_image' . $this->getId();
        } else {
            $appendName = 'add_logo_image';
            $contentNumber = $this->getContentNumber();
            if(!empty($contentNumber)){
                $appendName .= '_' . $contentNumber;
            }
        }
        
        $uploader = GI_UploaderFactory::buildImageUploader('content_' . $appendName);
        $uploader->setFilesLabel('Logo Image');
        $uploader->setBrowseLabel('Upload Image');
        $uploader->setDescription('For optimal Logo Image size, we strongly recommend 800 x 800 pixels');
        $folder = $this->getLogoImageFolder();
        
        $uploader->setTargetFolder($folder);
        if($form){
            $uploader->setForm($form);
        }
        
        return $uploader;
    }
    
    public function getLogoImageFolder(){
        return $this->getSubFolderByRef('lgo_image', array(
            'title' => 'Logo Image'
        ));
    }
    
    public function getLogoImageFile(){
        $folder = $this->getLogoImageFolder();
        if($folder){
            $files = $folder->getFiles();
            if (!empty($files)) {
                return $files[0];
            }
        }
        return NULL;
    }
    
    public function getLogoImageLink($width = 760, $height = 254){
        $folder = $this->getLogoImageFolder();
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
        $logoImageUploader = $this->getLogoImageUploader($form);
         
        if (parent::handleFormSubmission($form)) {
            if($logoImageUploader){
                $logoImageUploader->setTargetFolder($this->getLogoImageFolder());
                FolderFactory::putUploadedFilesInTargetFolder($logoImageUploader);
            }
            return true;
        }
        return false;
    }
    
    
    protected function setPropertiesFromForm(GI_Form $form){
        parent::setPropertiesFromForm($form);
        
        $investors = filter_input(INPUT_POST,  $this->getFieldName('investors'));
        $this->setProperty('content_investment_corporation.investors', $investors);
        
        $equity = filter_input(INPUT_POST,  $this->getFieldName('equity'));
        $this->setProperty('content_investment_corporation.equity', $equity);
        
        $industry = filter_input(INPUT_POST,  $this->getFieldName('industry'));
        $this->setProperty('content_investment_corporation.industry', $industry);
        
        $location = filter_input(INPUT_POST,  $this->getFieldName('location'));
        $this->setProperty('content_investment_corporation.location', $location);

        $currency = filter_input(INPUT_POST,  $this->getFieldName('currency'));
        $this->setProperty('content_investment_corporation.currency', $currency);
        
        $employees = filter_input(INPUT_POST,  $this->getFieldName('employees'));
        $this->setProperty('content_investment_corporation.employees', $employees);
        
        $incorporationType = filter_input(INPUT_POST,  $this->getFieldName('incorporation_type'));
        $this->setProperty('content_investment_corporation.incorporation_type', $incorporationType);
        
        $foundDate = filter_input(INPUT_POST,  $this->getFieldName('found_date'));
        $this->setProperty('content_investment_corporation.found_date', $foundDate);
        
        $website = filter_input(INPUT_POST,  $this->getFieldName('website'));
        $this->setProperty('content_investment_corporation.website', $website);
        
        $slogan = filter_input(INPUT_POST,  $this->getFieldName('slogan'));
        $this->setProperty('content_investment_corporation.slogan', $slogan);
        
        $summary = filter_input(INPUT_POST,  $this->getFieldName('summary'));
        $this->setProperty('content_investment_corporation.summary', $summary);

        $corporationCategory = filter_input(INPUT_POST,  $this->getFieldName('corporation_category'));
        $this->setProperty('content_investment_corporation.corporation_category', $corporationCategory);

        return true;
    }
    
    public function getSubtitle() {
        return $this->getLocation();
    }
    
    public function getLocation() {
        return $this->getProperty('content_investment_corporation.location');
    }
    
    public function getPreviewBockHTML() {
        $dataArr = array(
            [
                'name' => 'Invested',
                'value' => $this->getDisplayInvestedAmt()
            ],
            [
                'name' => 'Investors',
                'value' => $this->getDisplayInvestors()
            ],
            [
                'name' => 'Target',
                'value' => $this->getDisplayTargetAmt()
            ],
            [
                'name' => 'Equity',
                'value' => $this->getDisplayEquity()
            ],
        );

        if($this->getTypeRef() == ContentInvestmentCorporation::$TYPE_OPPORTUNITY_REF){
            $dataArr[] = array(
                'name' => 'Expected Returns',
                'value' => $this->getExpectedReturns(),
            );
        }

        return $this->getPreviewBlocks($dataArr, 'investment__preview-wrap_theme_primary');
    }
    
    public function getDisplayInvestors() {
        $investors = $this->getProperty('content_investment_corporation.investors');
        return $investors;
    }
    
    public function getDisplayEquity() {
        $equity = $this->getProperty('content_investment_corporation.equity');
        return $equity;
    }
    
    public function getDisplayFoundDate() {
        $foundDate = $this->getProperty('content_investment_corporation.found_date');
        return GI_Time::formatDateForDisplay($foundDate, 'F Y');
    }
    
    public function getDisplaySummary() {
        $summary = $this->getProperty('content_investment_corporation.summary');
        return GI_StringUtils::nl2brHTML($summary);
    }
    
    public function getDisplayLogo() {
        $logoLink = $this->getLogoImageLink();
        if (!empty($logoLink)) {
                return '<img src="'.$logoLink.'" title="Company logo" class="company-logo">';
            }
        return '';
    }

    public function getCorporationCategoryTitle() {
        $corporationCategory = $this->getProperty('content_investment_corporation.corporation_category');
        if (!empty($corporationCategory) && array_key_exists($corporationCategory, ContentFactory::$OPITIONS_CORPORATION_CATEGORY)) {
            return ContentFactory::$OPITIONS_CORPORATION_CATEGORY[$corporationCategory];
        }
        return '';
    }

    public function getDetailPreviewBlockHTML(){

        $items = [
            [
                'name' => 'Industry',
                'value' => $this->getProperty('content_investment_corporation.industry'),
            ],
            [
                'name' => 'Location',
                'value' => $this->getProperty('content_investment_corporation.location'),
            ],
            [
                'name' => 'Currency',
                'value' => $this->getProperty('content_investment_corporation.currency'),
            ],
            [
                'name' => 'Founded',
                'value' => $this->getDisplayFoundDate(),
            ],
            [
                'name' => 'Employees',
                'value' => $this->getProperty('content_investment_corporation.employees'),
            ],
            [
                'name' => 'Incorporation Type',
                'value' => $this->getProperty('content_investment_corporation.incorporation_type'),
            ],
            [
                'name' => 'Website',
                'value' => $this->getWebsiteLinkHTML(),
            ],
        ];
        
        $isEmpty = true;
        $html = '<div class="investment__detail-info-row investment__detail-info-row_type_hightlight">';
            $html .= '<div class="row">';
                foreach($items as $item){
                    if(empty($item['value']) && $item['value'] !== 0){
                        continue;
                    }
                    $isEmpty = false;
                    $html .= '<div class="col-xs-12 col-md-6">';
                        $html .= '<span class="investment__general-info-item-name">'.$item['name'].'</span>';
                        $html .= '<span class="investment__general-info-item-value">'.$item['value'].'</span>';
                    $html .= '</div>';
                }
            $html .= '</div>';
        $html .= '</div>';

        if($isEmpty){
            return '';
        }

        return $html;
    }

    
    public function getInvestmentItemDetailBlockHTML() {
        $html = '';

        if(Login::isLoggedIn()){
            $summaryInfo = [
                [
                    'name' => 'Project Status',
                    'value' => $this->getInvestmentStatusTitle(),
                ],
                [
                    'name' => 'Target Amount',
                    'value' => $this->getDisplayTargetAmt(),
                ],
                [
                    'name' => 'Invested Amount',
                    'value' => $this->getDisplayInvestedAmt(),
                ],
                [
                    'name' => 'Funded',
                    'value' => $this->getDisplayFundsRate(),
                ],
                [
                    'name' => 'Due Date',
                    'value' => $this->getDisplayDueDate(),
                ],
                [
                    'name' => 'Investors',
                    'value' => $this->getDisplayInvestors(),
                ],
                [
                    'name' => 'Equity',
                    'value' => $this->getDisplayEquity(),
                ],
            ];
            $html .= '<div class="row investment__detail-info-row">';
            $html .= '<div class="col-xs-12"><h3 class="investment__general-info-title">Summary</h3></div>';
            $html .= '<div class="col-xs-12"><p class="investment__general-info-item-summary">'.$this->getDisplaySummary().'</p></div>';
            foreach($summaryInfo as $infoItem){
                if(empty($infoItem['value']) && $infoItem['value'] !== 0){
                    continue;
                }
                $html .= '<div class="col-xs-12">';
                $html .= '<span class="investment__general-info-item-name">'.$infoItem['name'].'</span>';
                $html .= '<span class="investment__general-info-item-value">'.$infoItem['value'].'</span>';
                $html .= '</div>';
            }
            $html .= '</div>';
        } else {
            $html .= $this->getUnregisterPlaceholder();
        }

        $logoHTML = $this->getDisplayLogo();
        if(!empty($logoHTML)){
            $html .= '<div class="row investment__detail-info-row">';
                $html .= '<div class="col-xs-12"><h3 class="investment__general-info-title">Current Logo</h3></div>';
                $html .= '<div class="col-xs-12">';
                    $html .= '<span class="investment__general-info-item-value investment__general-info-item-value_type_logo">'.$logoHTML.'</span>';
                $html .= '</div>';
            $html .= '</div>';
        }

        return $html;
    }

    
    public function getViewTitle($plural = true) {
        $typeModel = $this->getTypeModel();
        $title = $typeModel->getProperty('title');
        // if ($plural) {
        //     $title .= ' Investments';
        // }
        return $title;
    }

    public function getWebsiteLinkHTML(){
        $website = $this->getProperty('content_investment_corporation.website');
        if(empty($website)){
            return NULL;
        }

        return '<a href="'.GI_StringUtils::fixLink($website).'" target="_blank">'.$website.'</a>';
    }

    public function getCategoryRef(){
        return $this->getCorporationCategoryTitle();
    }

    public function getCategoryColor(){
        $corporationCategory = $this->getProperty('content_investment_corporation.corporation_category');
        if(empty($corporationCategory)){
            return ContentFactory::$INVESTMENT_CATEGORY_COLOR[$this->getTypeRef()];
        }
        return ContentFactory::$INVESTMENT_CATEGORY_COLOR[$corporationCategory];
    }
}
