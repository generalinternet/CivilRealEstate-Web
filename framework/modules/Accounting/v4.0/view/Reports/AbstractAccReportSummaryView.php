<?php
/**
 * Description of AbstractAccReportSummaryView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccReportSummaryView extends GI_View {
    
    protected $accReport;
    
    public function __construct(AbstractAccReport $accReport) {
        parent::__construct();
        $this->accReport = $accReport;
    }
    
    protected function buildView() {
        $this->addHTML('<div class="report_summary_wrap">');
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->addHTML('</div>');
    }
    
    protected function buildViewHeader() {
        
    }
    
    protected function buildViewBody() {
        $this->addHTML('<div class="flex_row report_summary_row">')
                ->addHTML('<div class="flex_col sml">');
        $colour = $this->accReport->getColour();
        $this->addHTML('<span class="avatar_wrap inline_block '.(GI_Colour::useLightFont($colour)? 'use_light_font':'').'" style="background: #' . $colour . ';">'.$this->accReport->getInitials().'</span>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $title = $this->accReport->getTitle();
        $this->addHTML('<span class="title">'.$title.'</span>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col size_2">');
        $description = $this->accReport->getDescription();
        $this->addHTML($description);
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function buildViewFooter() {
        
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}