<?php
/**
 * Description of AbstractTimeIntervalDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractTimeIntervalDetailView extends GI_View {
    
    /** @var AbstractTimeInterval */
    protected $timeInterval;
    
    public function __construct(AbstractTimeInterval $timeInterval) {
        parent::__construct();
        $this->timeInterval = $timeInterval;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_ti_detail">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    protected function buildViewHeader($classNames = '') {
    }
    
    protected function buildViewBody($classNames = '') {
        $this->addHTML('<div class="content_ti_box '.$classNames.'">');
            $this->addHTML('<p class="ti_title">'.$this->timeInterval->getTitle().'</p>');
            $this->addHTML('<p class="ti_date">');
            if ($this->timeInterval->isAllDay()) {
                //Show only start date
                $this->addHTML($this->timeInterval->getStartDate());
            } else {
                $this->addHTML($this->timeInterval->getStartDateTime() . ' ~ ' . $this->timeInterval->getEndDateTime());
            }
            $this->addHTML('</p>');
        $this->addHTML('</div>');
    }
    
    protected function buildViewFooter($classNames = '') {
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
