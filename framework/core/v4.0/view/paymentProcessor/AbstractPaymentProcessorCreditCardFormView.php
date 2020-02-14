<?php
/**
 * Description of AbstractPaymentProcessorCreditCardFormView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */

abstract class AbstractPaymentProcessorCreditCardFormView extends MainWindowView {
    
    /** @var GI_Form*/
    protected $form;
    protected $formBuilt = false;
    protected $contact = NULL;
    protected $isEmbedded = true;
    
    public function __construct(GI_Form $form) {
        parent::__construct();
        $this->form = $form;
    }
    
    public function setIsEmbedded($isEmbedded) {
        $this->isEmbedded = $isEmbedded;
    }
    
    public function setContact(AbstractContact $contact) {
        $this->contact = $contact;
    }
    
    protected function addViewBodyContent() {
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
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
        
    }
    
    protected function buildFormBody() {
        
    }
    
    protected function buildFormFooter() {
        $this->form->addField('card_errors', 'hidden', array());
    }
    
    
    
}