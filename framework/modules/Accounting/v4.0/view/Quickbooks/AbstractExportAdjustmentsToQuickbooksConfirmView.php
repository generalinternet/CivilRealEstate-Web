<?php
/**
 * Description of AbstractExportAdjustmentsToQuickbooksConfirmView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */

abstract class AbstractExportAdjustmentsToQuickbooksConfirmView extends GI_View {
    
    
    protected $form;
    protected $formBuilt = false;
    protected $count = 0;
    
    public function __construct(GI_Form $form) {
        parent::__construct();
        $this->form = $form;
    }
    
    public function setCount($count){
        $this->count = $count;
    }
    
    public function buildForm() {
        $this->buildFormHeader();
        $this->buildFormBody();
        $this->buildFormFooter();
    }
    
    protected function buildFormHeader() {
        $this->form->addHTML('<h2 class="main_head">Export Adjustments</h2>');
    }
    
    protected function buildFormBody() {
        $term = 'adjustments';
        if($this->count == 1){
            $term = 'adjustment';
        }
        
        $this->form->addHTML('<p>You have selected <b>' . $this->count . ' ' . $term . '</b> to export to quickbooks.</p>');
    }
    
    protected function buildFormFooter() {
        $this->addButtons();
    }
    
    protected function addButtons() {
         $this->form->addHTML('<div class="center_btns wrap_btns"><span class="submit_btn" tabindex="0" style="margin-right:10px;">Export</span><span class="other_btn gray close_gi_modal" tabindex="0">Cancel</span></div>');
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->addHTML($this->form->getForm(''));
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}