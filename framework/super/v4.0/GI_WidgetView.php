<?php
/**
 * Description of GI_WidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */
abstract class GI_WidgetView extends GI_View {

    protected $ref = NULL;
    protected $useBodyPlaceholder = true;
    protected $contentSourceURL = NULL;
    protected $isViewable = NULL;

    public function __construct($ref) {
        parent::__construct();
        $this->ref = $ref;
    }

    public function setUseBodyPlaceholder($useBodyPlaceholder = true) {
        $this->useBodyPlaceholder = $useBodyPlaceholder;
    }
    
    public function setContentSourceURL($contentSourceURL) {
        $this->contentSourceURL = $contentSourceURL;
    }

    protected function buildView() {
        $this->openViewWrap();
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->closeViewWrap();
    }

    protected function buildViewHeader() {
        
    }

    protected function buildViewBody() {
        if ($this->useBodyPlaceholder) {
            $this->buildBodyPlaceholder();
        } else {
            $this->buildBodyContent();
        }
    }
    
    protected function buildBodyPlaceholder() {
        
    }
    
    protected function buildBodyContent() {
        
    }
    
    protected function buildViewFooter() {
        
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="widget_wrap">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }


    public function getBodyContentHTMLView() {
        $this->buildBodyContent();
        return $this->html;
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
    public function isViewable() {
        if (is_null($this->isViewable)) {
            $this->isViewable = $this->determineIsViewable();
        }
        return $this->isViewable;
    }
    
    protected function determineIsViewable() {
        return true;
    }
    

}
