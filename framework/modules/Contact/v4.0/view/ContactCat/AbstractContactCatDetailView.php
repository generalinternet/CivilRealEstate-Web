<?php
/**
 * Description of AbstractContactCatDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactCatDetailView extends GI_View {
    
    protected $contactCat;
    
    public function __construct(AbstractContactCat $contactCat) {
        parent::__construct();
        $this->contactCat = $contactCat;
    }

    protected function buildView() {
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
    }
    
    protected function buildViewHeader() {
        $this->addHTML('<div class="content_block_wrap">');
        $this->addContentBlock($this->contactCat->getTypeTitle(), 'Category');
        $this->addHTML('</div>');
    }
    
    protected function buildViewBody() {
        
    }
    
    protected function buildViewFooter() {
        
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
