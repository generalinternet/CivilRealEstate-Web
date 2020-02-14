<?php
/**
 * Description of AbstractContactCatalogView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactCatalogView extends MainWindowView {

    /** @var AbstractContact */
    protected $contact;
    protected $logoWidth = 90;
    protected $logoHeight = 90;
    protected $listMode = false;
    
    public function __construct(AbstractContact $contact) {
        parent::__construct();
        $this->contact = $contact;
        $name = $this->contact->getPublicProfileBusinessName();
        $this->addSiteTitle($this->contact->getTypeTitle());
        $this->addSiteTitle($name);
        $this->setWindowTitle($name);
        $this->setWindowIcon($this->contact->getWindowIcon());
    }
    
    public function setListMode($listMode){
        $this->listMode = $listMode;
        $this->setAddOuterWrap(false);
        $this->setAddViewHeader(false);
        $this->setAddViewFooter(false);
        $this->setAddViewBodyWrap(false);
        return $this;
    }

    protected function addViewBodyContent() {
        $this->openCatalogItemWrap();
        $this->addCatalogItemContentSection();
        $this->closeCatalogItemWrap();
    }
    
    protected function openCatalogItemWrap(){
        $class = '';
        $viewURL = $this->contact->getViewProfileURL();
        $this->addHTML('<div class="non_anchor_link catalog_item_wrap ' . $class . '" data-url="' . $viewURL . '" title="View ' . $this->contact->getContactCatTypeTitle() . '">');
        return $this;
    }
    
    protected function closeCatalogItemWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openCatalogItemContentWrap(){
        $this->addHTML('<div class="catalog_item_content_wrap" >');
        return $this;
    }
    
    protected function closeCatalogItemContentWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addCatalogItemContentSection() {
        $this->openCatalogItemContentWrap();
        $view = $this->contact->getPublicProfileDetailView();
        if(!$view){
            return;
        }
        $view->setShowMoreSection(false);
        $view->setOnlyBodyContent(true);
        $this->addHTML($view->getHTMLView());
        $this->closeCatalogItemContentWrap();
    }

}
