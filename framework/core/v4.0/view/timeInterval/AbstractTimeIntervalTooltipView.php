<?php
/**
 * Description of AbstractTimeIntervalTooltipView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractTimeIntervalTooltipView extends GI_View {
    
    /** @var AbstractTimeInterval */
    protected $timeInterval;
    /** start datetime string **/
    protected $start;
    /** end datetime string **/
    protected $end;
    
    public function __construct(AbstractTimeInterval $timeInterval, $start = NULL, $end = NULL) {
        parent::__construct();
        $this->timeInterval = $timeInterval;
        $this->timeInterval->setSpecificDate($start);
        $this->start = $start;
        $this->end = $end;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->buildViewHeader();
        $this->addHTML('<div class="tooltip_box">');
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->addHTML('</div><!--.tooltip_box-->');
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $colour = $this->timeInterval->getColour();
        $this->addHTML('<div class="tooltip_box_wrap ti_project_tooltip" style="background-color:#'.$colour.';" data-color="#'.$colour.'">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    protected function buildViewHeader($classNames = '') {
        $this->addHTML('<div class="tooltip_header '.$classNames.'">');
            $this->addHTML('<span class="icon_wrap close_tooltip"><span class="icon eks gray"></span></span>');
        $this->addHTML('</div>');
    }

    protected function buildViewBody($classNames = '') {
        $this->addHTML('<div class="tooltip_body '.$classNames.'">');
            $this->addTitleSection();
            $this->addDateSection();
            $this->addScheduledContactsSection();
        $this->addHTML('</div><!--.tooltip_body-->');
    }
    
    protected function addTitleSection(){
        $this->addHTML('<p class="tooltip_title">'.$this->timeInterval->getTitle().'</p>');
    }
    
    protected function addDateSection(){
        $this->addHTML('<p class="tooltip_date">');
            if (!empty($this->start)) {
                $this->addHTML(GI_Time::formatDateForDisplay($this->start, 'M jS') . ' ');
            }
            $this->addHTML($this->timeInterval->getDisplayStartTimeAndEndTime());
        $this->addHTML('</p>');
    }
    
    protected function addScheduledContactsSection(){
        $this->addHTML('<p class="tooltip_scheduled_contacts"><strong>Scheduled:</strong><br>'.$this->timeInterval->getScheduledContactsText().'</p>');
    }
    
    protected function buildViewFooter($classNames = '') {
        $this->addHTML('<div class="tooltip_footer'.$classNames.'">');
        $this->addBtns();
        $this->addHTML('</div>');
        
    }
    
    protected function addBtns() {
        $this->addHTML('<div class="btn_wrap">');
        $this->addEditBtn();
        $this->addDeleteBtn();
        $this->addHTML('</div>');
    }
    
    protected function addEditBtn() {
        if ($this->timeInterval->isEditable()) {
            $editURL = $this->timeInterval->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" title="Edit Schedule" class="other_btn open_modal_form close_tooltip"><span class="icon_wrap"><span class="icon pencil"></span></span><span class="btn_text">Edit</span></a>');
        }
    }
    
    protected function addDeleteBtn() {
        if ($this->timeInterval->isDeleteable()) {
            $deleteURL = $this->timeInterval->getDeleteURL();
            $this->addHTML('<a href="' . $deleteURL . '" title="Delete" class="other_btn open_modal_form close_tooltip"><span class="icon_wrap"><span class="icon trash"></span></span><span class="btn_text">Delete</span></a>');
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
