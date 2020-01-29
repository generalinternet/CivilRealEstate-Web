<?php
/**
 * Description of AbstractContentDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentDetailView extends MainWindowView {
    
    /** @var Content  */
    protected $content;
    protected $referenceOnly = false;
    protected $displayAsChild = false;
    protected $pdfMode = false;
    protected $detailWrapAttrs = array();
    
    public function __construct(AbstractContent $content) {
        parent::__construct();
        $this->content = $content;
        $this->addSiteTitle('Content');
        $typeTitle = $this->content->getViewTitle();
        if($typeTitle != 'Content'){
            $this->addSiteTitle($typeTitle);
        }
        $this->addSiteTitle($this->content->getTitle());
        $this->addCSS('framework/modules/Content/' . MODULE_CONTENT_VER . '/resources/content.css');
        $this->addJS('framework/modules/Content/' . MODULE_CONTENT_VER . '/resources/content.js');
        $this->setListBarURL($this->content->getListBarURL());
        $title = $this->content->getTitle();
        $refLink = $this->content->getRefURL();
        $refString = '';
        if(!empty($refLink)){
            $refString = ' <a href="' . $refLink . '" title="' . $title . '" class="content_ref"><span class="icon primary link"></span></a>';
        }
        $this->setWindowTitle('<span class="inline_block">' . $title . '</span>' . $refString);
        $this->setPrimaryViewModel($this->content);
        $this->setCurLayoutMenuRef('content');
    }
    
    protected function addWindowTitle(){
        $titleTag = $this->content->getTitleTag();
        $mainTitle = '';
        $windowIcon = $this->getWindowIcon();
        if($windowIcon){
            $mainTitle .= GI_StringUtils::getSVGIcon($windowIcon, '26px', '26px', 'left_icon');
        }
        $windowTitle = $this->getWindowTitle();
        if($windowTitle){
            $mainTitle .= $windowTitle;
        }
        $this->addHTML('<' . $titleTag . ' class="main_head">');
        $this->addHTML($mainTitle);
        $this->addHTML('</' . $titleTag . '>');
        return $this;
    }
    
    public function setDisplayAsChild($displayAsChild){
        $this->displayAsChild = $displayAsChild;
        if($displayAsChild){
            $this->setAddOuterWrap(false);
            $this->setAddViewWrap(false);
            $this->setAddViewHeader(false);
            $this->setAddViewBodyWrap(false);
            $this->setAddViewFooter(false);
            $this->setAddWrap(false);
        }
        return $this;
    }
    
    public function setPDFMode($pdfMode){
        $this->pdfMode = $pdfMode;
        return $this;
    }
    
    public function setReferenceOnly($referenceOnly){
        $this->referenceOnly = $referenceOnly;
        return $this;
    }
    
    protected function getDetailWrapId(){
        return NULL;
    }
    
    protected function getDetailWrapAttrs(){
        return $this->detailWrapAttrs;
    }
    
    protected function getDetailWrapClass(){
        return $this->content->getHTMLClass();
    }
    
    protected function addDetailWrapAttr($attr, $val = NULL){
        $this->detailWrapAttrs[$attr] = $val;
        return $this;
    }
    
    protected function openDetailViewWrap(){
        $detailWrapId = $this->getDetailWrapId();
        $detailWrapIdAttr = '';
        if($detailWrapId){
            $detailWrapIdAttr = 'id="' . $detailWrapId . '"';
        }
        $detailWrapAttrString = '';
        $detailWrapAttrs = $this->getDetailWrapAttrs();
        if(!empty($detailWrapAttrs)){
            foreach($detailWrapAttrs as $attr => $val){
                $detailWrapAttrString .= 'data-' . $attr . '="' . $val . '" ';
            }
        }
        $detailWrapClass = $this->getDetailWrapClass();
        $typeRef = $this->content->getTypeRef();
        $this->addHTML('<div ' . $detailWrapIdAttr . ' class="content_detail_view ' . $typeRef . ' ' . $detailWrapClass . '" ' . $detailWrapAttrString . '>');
    }
    
    protected function closeDetailViewWrap(){
        $this->addHTML('</div>');
    }
    
    protected function addBtns(){
        if($this->referenceOnly || $this->displayAsChild || $this->pdfMode){
            return;
        }
        $this->openBtnWrap();
        $this->addManageEditorsBtn();
        $this->addEditBtn();
        $this->addDeleteBtn();
        $this->closeBtnWrap();
    }
    
    protected function addWindowBtns() {
        $this->addBtns();
    }
    
    protected function openBtnWrap(){
        $this->addHTML('<div class="right_btns">');
    }
    
    protected function closeBtnWrap(){
        $this->addHTML('</div>');
    }
    
    protected function getEditBtnClass(){
        return NULL;
    }
    
    protected function addEditBtn(){
        if ($this->content->isEditable()) {
            $editURL = $this->content->getEditURL();
            $editTerm = Lang::getString('edit');
            $editBtnClass = $this->getEditBtnClass();
            $this->addHTML('<a href="' . $editURL . '" title="' . $editTerm . '" class="custom_btn edit_btn ' . $editBtnClass . '">' . GI_StringUtils::getIcon('edit') . '<span class="btn_text">' . $editTerm . '</span></a>');
        }
    }
    
    protected function addDeleteBtn(){
        if ($this->content->isDeleteable()) {
            $deleteURL = $this->content->getDeleteURL();
            $deleteTerm = Lang::getString('delete');
            $this->addHTML('<a href="' . $deleteURL . '" class="custom_btn open_modal_form" title="' . $deleteTerm . '">' . GI_StringUtils::getIcon('delete') . '<span class="btn_text">' . $deleteTerm . '</span></a>');
        }
    }
    
    protected function addManageEditorsBtn(){
        if ($this->content->canManageEditors()) {
            $manageEditorsURL = GI_URLUtils::buildURL(array(
                'controller' => 'content',
                'action' => 'manageEditors',
                'contentId' => $this->content->getId()
            ));
            $this->addHTML('<a href="' . $manageEditorsURL . '" title="Manage Editors" class="custom_btn open_modal_form">' . GI_StringUtils::getIcon('edit') . '<span class="btn_text">Manage Editors</span></a>');
        }
    }
    
    public function addChildHeader(){
        $this->addBtns();
        $this->addContentTitle();
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        $this->openDetailViewWrap();
        
        if($this->displayAsChild){
            $this->addChildHeader();
        }
        
        $this->buildViewGuts();
        
        $this->buildInnerContent();
        
        $this->closeDetailViewWrap();
        $this->closePaddingWrap();
    }
    
    protected function buildInnerContent(){
        $innerContents = $this->content->getInnerContent();
        if(!empty($innerContents)){
            $this->addHTML('<div class="content_in_content">');
        }
        foreach($innerContents as $innerContent){
            $view = $innerContent->getView();
            $view->setDisplayAsChild(true);
            $this->addHTML($view->getHTMLView());
            $this->innerContentAdded($innerContent);
        }
        if(!empty($innerContents)){
            $this->addHTML('</div>');
        }
    }
    
    protected function addContentTitle(){
        $title = $this->content->getTitle();
        $titleTag = $this->content->getTitleTag();
        $refLink = $this->content->getRefURL();
        $refString = '';
        if(!empty($refLink)){
            $refString = ' <a href="' . $refLink . '" title="' . $title . '" class="content_ref"><span class="icon primary link"></span></a>';
        }
        $this->addHTML('<' . $titleTag . ' class="content_title"><span class="inline_block">' . $title . '</span>' . $refString . '</' . $titleTag . '>');
        
    }
    
    protected function buildViewGuts(){
        $this->addFileViews();
    }
    
    protected function addFileViews(){
        $folder = $this->content->getFolder(false);
        if($folder){
            $this->addHTML('<div class="content_files">');
            $files = $folder->getFiles();
            foreach($files as $file){
                $fileView = $file->getView();
                $fileView->setIsDeleteable(false);
                $fileView->setIsRenamable(false);
                $this->addHTML($fileView->getHTMLView());
            }
            $this->addHTML('</div>');
        }
    }
    
    protected function innerContentAdded(AbstractContent $content){
        //listener for when inner content is added to the view
        return $this;
    }
    
}
