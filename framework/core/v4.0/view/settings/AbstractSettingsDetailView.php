<?php
/**
 * Description of AbstractSettingsDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractSettingsDetailView extends GI_View {
    
    /** @var AbstractSettings */
    protected $settings;
    
    public function __construct(AbstractSettings $settings) {
        parent::__construct();
        $this->settings = $settings;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    protected function buildViewHeader() {
        
    }
    
    protected function buildViewBody() {
        
    }
    
    protected function buildViewFooter() {
        
    }


    public function beforeReturningView() {
        $this->buildView();
    }
}