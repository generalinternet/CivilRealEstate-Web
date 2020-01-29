<?php
/**
 * Description of AbstractWindowView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractWindowView extends GI_View {
    
    protected $viewWrapClass = '';
    protected $viewWrapId = '';
    protected $viewHeaderClass = '';
    protected $viewBodyClass = '';
    protected $viewBodyId = '';
    protected $viewFooterClass = '';
    protected $addViewWrap = true;
    protected $addViewHeader = true;
    protected $addViewBodyWrap = true;
    protected $addViewBody = true;
    protected $addViewFooter = true;
    protected $addOuterWrap = true;
    protected $listBarURL = NULL;
    protected $listBarClass = '';
    protected $windowTitle = '';
    protected $windowIcon = '';
    protected $addWrap = false;
    protected $onlyBodyContent = false;
    protected $viewBodyBarClass = '';
    protected $addViewBodyBar = false;
    /**
     * The primary model to be displayed in the view (this property will be used to gather the common data to be displayed to a super admin)
     * @var GI_Model 
     */
    protected $primaryViewModel = NULL;
    protected $extraPrimaryViewModelData = array();
    protected $useAJAXLoading = true;
    protected $viewHeaderAttrs = array();
    protected $viewFooterAttrs = array();
    protected $viewBodyAttrs = array();
    
    public function __construct() {
        parent::__construct();
        
        $isModal = GI_URLUtils::getAttribute('modal');
        if($isModal){
            $this->setAddOuterWrap(false);
        }
    }
    
    public function setWindowTitle($windowTitle){
        $this->windowTitle = $windowTitle;
        return $this;
    }
    
    public function getWindowTitle(){
        return $this->windowTitle;
    }
    
    public function setWindowIcon($windowIcon){
        $this->windowIcon = $windowIcon;
        return $this;
    }
    
    public function getWindowIcon(){
        return $this->windowIcon;
    }
    
    public function getViewWrapClass(){
        return $this->viewWrapClass;
    }
    
    public function getViewBodyClass(){
        return $this->viewBodyClass;
    }
    
    public function getViewHeaderClass(){
        return $this->viewHeaderClass;
    }
    
    public function getViewWrapId() {
        return $this->viewWrapId;
    }
    
    public function getViewBodyId() {
        return $this->viewBodyId;
    }
    
    public function setViewWrapClass($class){
        $this->viewWrapClass = $class;
        return $this;
    }
    
    public function setViewHeaderClass($class){
        $this->viewHeaderClass = $class;
        return $this;
    }
    
    public function setViewBodyClass($class){
        $this->viewBodyClass = $class;
        return $this;
    }
    
    public function setViewWrapId($viewWrapId) {
        $this->viewWrapId = $viewWrapId;
        return $this;
    }
    
    public function setViewBodyId($viewBodyId) {
        $this->viewBodyId = $viewBodyId;
        return $this;
    }

    protected function openOuterWrap(){
        return $this;
    }
    
    protected function closeOuterWrap(){
        return $this;
    }
    
    public function setAddOuterWrap($addOuterWrap){
        $this->addOuterWrap = $addOuterWrap;
        return $this;
    }
    
    public function getOnlyBodyContent(){
        return $this->onlyBodyContent;
    }
    
    public function setOnlyBodyContent($onlyBodyContent){
        $this->onlyBodyContent = $onlyBodyContent;
        return $this;
    }
    
    public function getUseAJAXLoading(){
        return $this->useAJAXLoading;
    }
    
    public function setUseAJAXLoading($useAJAXLoading){
        $this->useAJAXLoading = $useAJAXLoading;
        return $this;
    }
    
    protected function openViewWrap(){
        if(!$this->addViewWrap || !$this->addOuterWrap){
            return $this;
        }
        $this->addHTML('<div');
        $viewWrapId = $this->getViewWrapId();
        if($viewWrapId){
            $this->addHTML(' id="'.$viewWrapId.'"');
        }
        $this->addHTML(' class="view_wrap ' . $this->getViewWrapClass() . '">');
        return $this;
    }
    
    protected function closeViewWrap(){
        if(!$this->addViewWrap || !$this->addOuterWrap){
            return $this;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    public function setAddViewWrap($addViewWrap){
        $this->addViewWrap = $addViewWrap;
        return $this;
    }
    
    protected function openViewHeader(){
        if(!$this->addViewHeader){
            return $this;
        }
        $this->addHTML('<div class="view_header ' . $this->getViewHeaderClass() . '" ' . $this->getViewHeaderAttrString() . '>');
        return $this;
    }
    
    protected function closeViewHeader(){
        if(!$this->addViewHeader){
            return $this;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    public function setAddViewHeader($addViewHeader){
        $this->addViewHeader = $addViewHeader;
        return $this;
    }
    
    public function setViewHeaderAttr($attr, $val){
        $this->viewHeaderAttrs[$attr] = $val;
        return $this;
    }
    
    public function setViewFooterAttr($attr, $val){
        $this->viewFooterAttrs[$attr] = $val;
        return $this;
    }
    
    public function setViewBodyAttr($attr, $val){
        $this->viewBodyAttrs[$attr] = $val;
        return $this;
    }
    
    public function getViewAttrString($attrs){
        $string = '';
        foreach($attrs as $attr => $val){
            $string .= ' ' . $attr . '="' . $val . '"';
        }
        return $string;
    }
    
    public function getViewHeaderAttrString(){
        return $this->getViewAttrString($this->viewHeaderAttrs);
    }
    
    public function getViewFooterAttrString(){
        return $this->getViewAttrString($this->viewFooterAttrs);
    }
    
    public function getViewBodyAttrString(){
        return $this->getViewAttrString($this->viewBodyAttrs);
    }
    
    protected function openViewBody(){
        if(!$this->addViewBody || !$this->addViewBodyWrap){
            return $this;
        }
        $this->addHTML('<div');
        $viewBodyId = $this->getViewBodyId();
        if($viewBodyId){
            $this->addHTML(' id="'.$viewBodyId.'"');
        }
        $this->addHTML(' class="view_body ' . $this->getViewBodyClass() . '" ' . $this->getViewBodyAttrString() . '>');
        return $this;
    }
    
    protected function closeViewBody(){
        if(!$this->addViewBody || !$this->addViewBodyWrap){
            return $this;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    public function setAddViewBody($addViewBody){
        $this->addViewBody = $addViewBody;
        return $this;
    }
    
    public function setAddViewBodyWrap($addViewBodyWrap){
        $this->addViewBodyWrap = $addViewBodyWrap;
        return $this;
    }
    
    protected function getViewFooterClass(){
        return $this->viewFooterClass;
    }
    
    protected function openViewFooter(){
        if(!$this->addViewFooter){
            return $this;
        }
        $this->addHTML('<div class="view_footer ' . $this->getViewFooterClass() . '" ' . $this->getViewFooterAttrString() . '>');
        return $this;
    }
    
    protected function closeViewFooter(){
        if(!$this->addViewFooter){
            return $this;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    public function setAddViewFooter($addViewFooter){
        $this->addViewFooter = $addViewFooter;
        return $this;
    }
    
    public function setListBarURL($listBarURL){
        $this->listBarURL = $listBarURL;
        return $this;
    }
    
    public function getListBarURL(){
        if(empty($this->listBarURL)){
            //@todo we should have a way to set the default list bar url when one is not provided
            $notification = NotificationFactory::buildNewModel();
            if($notification){
                $this->listBarURL = $notification->getListBarURL();
            }
        }
        return $this->listBarURL;
    }
    
    public function setListBarClass($listBarClass){
        $this->listBarClass = $listBarClass;
        return $this;
    }
    
    public function getListBarClass(){
        return $this->listBarClass;
    }
    
    public function buildView(){
        if($this->getOnlyBodyContent()){
            $this->addViewBodyContent();
            return $this;
        }
        $this->openOuterWrap()
                ->openViewWrap()
                ->buildViewHeader()
                ->buildViewBodyBar()
                ->buildViewBody()
                ->buildViewFooter()
                ->closeViewWrap()
            ->closeOuterWrap();
        return $this;
    }
    
    protected function buildViewHeader(){
        if(!$this->addViewHeader){
            return $this;
        }
        $this->openViewHeader();
            $this->addViewHeaderContent();
        $this->closeViewHeader();
        return $this;
    }
    
    protected function buildViewBody(){
        if(!$this->addViewBody){
            return $this;
        }
        $this->addPrimaryViewModelInfo();
        $this->openViewBody();
            $this->addViewBodyContent();
        $this->closeViewBody();
        return $this;
    }
    
    protected function buildViewFooter(){
        if(!$this->addViewFooter){
            return $this;
        }
        $this->openViewFooter();
            $this->addViewFooterContent();
        $this->closeViewFooter();
        return $this;
    }
    
    protected function addViewHeaderContent(){
        $this->addWindowBtnWrap();
        $this->addWindowTitle();
        return $this;
    }
    
    protected function addViewBodyContent(){
        return $this;
    }
    
    protected function addViewFooterContent(){
        return $this;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function addWindowTitle(){
        $mainTitle = '';
        $windowIcon = $this->getWindowIcon();
        $class = 'main_head';
        if($windowIcon){
            $class .= ' has_left_icon';
            $mainTitle .= GI_StringUtils::getSVGIcon($windowIcon, '1em', '1em', 'left_icon');
        }
        $windowTitle = $this->getWindowTitle();
        if($windowTitle){
            $mainTitle .= $windowTitle;
        }
        $this->addMainTitle($mainTitle, $class);
        return $this;
    }
    
    protected function addWindowBtnWrap(){
        $class = '';
        if($this->getUseAJAXLoading()){
            $class = 'ajax_link_wrap';
        }
        $this->addHTML('<div class="right_btns ' . $class . '">');
            $this->addWindowBtns();
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addWindowBtns(){
        return $this;
    }
    
    public function setPrimaryViewModel($primaryViewModel = NULL){
        $this->primaryViewModel = $primaryViewModel;
        return $this;
    }
    
    /** @return GI_Model */
    public function getPrimaryViewModel(){
        return $this->primaryViewModel;
    }
    
    public function setAddWrap($addWrap){
        $this->addWrap = $addWrap;
        return $this;
    }

    protected function openPaddingWrap() {
        if($this->addWrap){
            $this->addHTML('<div class="content_padding">');
        }
        return $this;
    }

    protected function closePaddingWrap() {
        if($this->addWrap){
            $this->addHTML('</div>');
        }
        return $this;
    }
    
    public function setAddViewBodyBar($addViewBodyBar){
        $this->addViewBodyBar = $addViewBodyBar;
        return $this;
    }
    
    public function setViewBodyBarClass($class){
        $this->viewBodyBarClass = $class;
        return $this;
    }
    
    public function getViewBodyBarClass(){
        return $this->viewBodyBarClass;
    }
    
    protected function openViewBodyBar(){
        if(!$this->addViewBodyBar){
            return $this;
        }
        $this->addHTML('<div class="view_body_bar ' . $this->getViewBodyBarClass() . '">');
        return $this;
    }
    
    protected function closeViewBodyBar(){
        if(!$this->addViewBodyBar){
            return $this;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildViewBodyBar(){
        if(!$this->addViewBodyBar){
            return $this;
        }
        $this->openViewBodyBar();
            $this->addViewBodyBarContent();
        $this->closeViewBodyBar();
        return $this;
    }
    
    protected function addViewBodyBarContent(){
        return $this;
    }
    
    protected function addPrimaryViewModelInfo(){
        $primaryViewModel = $this->getPrimaryViewModel();
        if(!empty($primaryViewModel)){
            $inception = GI_Time::formatDateTimeForDisplay($primaryViewModel->getProperty('inception'));
            $lastMod = GI_Time::formatDateTimeForDisplay($primaryViewModel->getProperty('last_mod'));
            $this->addHTML('<div class="admin_only view_body_bar">');
                $this->addHTML(GI_StringUtils::getLabelWithValue('Id', $primaryViewModel->getId()));
                $this->addHTML(GI_StringUtils::getLabelWithValue('Created By', $primaryViewModel->getCreatedByName()));
                $this->addHTML(GI_StringUtils::getLabelWithValue('Created Date', $inception));
                $this->addHTML(GI_StringUtils::getLabelWithValue('Last Modified By', $primaryViewModel->getLastModByName()));
                $this->addHTML(GI_StringUtils::getLabelWithValue('Last Modified Date', $lastMod));
                foreach($this->extraPrimaryViewModelData as $extraData){
                    $this->addHTML(GI_StringUtils::getLabelWithValue($extraData['label'], $extraData['value']));
                }
            $this->addHTML('</div>');
        }
    }
    
    public function addExtraPrimaryViewModelData($label, $value){
        $this->extraPrimaryViewModelData[] = array(
            'label' => $label,
            'value' => $value
        );
        return $this;
    }
    
}
