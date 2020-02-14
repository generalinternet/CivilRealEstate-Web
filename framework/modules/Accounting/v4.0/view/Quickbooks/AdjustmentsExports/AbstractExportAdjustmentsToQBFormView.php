<?php
/**
 * Description of AbstractExportAdjustmentsToQBFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractExportAdjustmentsToQBFormView extends GI_View {
    
   protected $form;
   protected $model;
   protected $values;
   protected $remainingCount = NULL;
   protected $totalCount = NULL;
   protected $formBuilt = false;
   
   public function __construct(GI_Form $form, GI_Model $model, $values) {
       parent::__construct();
       $this->form = $form;
       $this->model = $model;
       $this->values = $values;
   }
   
   public function setRemainingCount($remainingCount) {
       $this->remainingCount = $remainingCount;
   }
   
   public function setTotalCount($totalCount) {
       $this->totalCount = $totalCount;
   }
   
   public function buildForm() {
       if (!$this->formBuilt) {
           $this->buildFormHeader();
           $this->buildFormBody();
           $this->buildFormFooter();
           $this->formBuilt = true;
       }
   }
   
   protected function buildFormHeader() {
       $this->form->addHTML('<h1 class="main_head">Export Adjustments to Quickbooks</h1>');
       $this->addCountSection();
       $this->addExportableModelInfoSection();
   }
   
   protected function addCountSection() {
       if (!empty($this->totalCount)) {
           $totalCount = intval($this->totalCount);
           $remainingCount = intval($this->remainingCount);
           $pos = $totalCount - $remainingCount;
           $string = 'Adjusting Item ';
           $string .= '<span class="highlited_index qb_colour">'.$pos.'</span>';
           $string .= ' of ';
           $string .= '<span class="total_index">'.$totalCount.'</span>';
           $this->form->addHTML('<div class="form_section"><div class="form_section_body"><div class="total_count highlited_count">'.$string.'</div></div></div>');
       }
   }
   
   protected function addExportableModelInfoSection() {
       
   }
   
   protected function buildFormBody() {
       
   }
   
   protected function buildFormFooter() {
        $this->addButtons();
        $this->form->addHTML('<br><br>');
    }

    protected function addButtons() {
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addCloseButton();
        $this->addSubmitButton();
        $this->form->addHTML('</div>');
    }

    protected function addSubmitButton() {
        $label = 'Export';
        if (!empty($this->remainingCount)) {
            $label .= ' and Continue';
        }
        $this->form->addHTML('<span class="submit_btn qb_colour">'.$label.'</span>');
   }
   
   protected function addCloseButton() {
       $this->form->addHTML('<span class="other_btn gray close_gi_modal">Close</span>');
   }
   
   protected function buildView() {
       $this->openViewWrap();
       $this->buildForm();
       $this->addHTML($this->form->getForm());
       $this->closeViewWrap();
   }
   
   protected function openViewWrap() {
       $this->addHTML('<div class="view_wrap no_pad">');
   }
   
   protected function closeViewWrap() {
       $this->addHTML('</div>');
   }
   
   public function beforeReturningView() {
       $this->buildView();
   }
   
   
}