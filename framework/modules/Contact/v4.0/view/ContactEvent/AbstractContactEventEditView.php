<?php
/**
 * Description of AbstractContactEventEditView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactEventEditView extends GI_View {
    
    protected $contactEvent;
    protected $form;

    public function __construct(GI_Form $form, AbstractContactEvent $contactEvent) {
        parent::__construct();
        $this->contactEvent = $contactEvent;
        $this->form = $form;
        $this->buildForm();
    }
    
    protected function buildForm() {
        $formView = $this->contactEvent->getFormView($this->form);
        $formView->buildForm();
        $this->form->addHTML('<div class="form_btns">');
            $this->form->addHTML('<span class="submit_btn" tabindex="0" title="Save">Submit</span>');
        $this->form->addHTML('</div>');
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding contact_event_edit">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
