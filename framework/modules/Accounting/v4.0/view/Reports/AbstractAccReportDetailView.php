<?php
/**
 * Description of AbstractAccReportDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccReportDetailView extends GI_View {

    /** @var AbstractAccReport */
    protected $accReport;

    public function __construct(AbstractAccReport $accReport) {
        parent::__construct();
        $this->accReport = $accReport;
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
        $this->addHTML('<h2>'.$this->accReport->getTitle().'</h2>');
    }
    
    protected function buildViewBody() {
        
    }
    
    protected function buildViewFooter() {
        
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
