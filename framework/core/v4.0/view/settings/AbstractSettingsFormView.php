<?php
/**
 * Description of AbstractSettingsFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */

abstract class AbstractSettingsFormView extends GI_View {
    
    /** @var AbstractSettings */
    protected $settings;
    /** @var GI_Form */
    protected $form;
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, AbstractSettings $settings) {
        parent::__construct();
        $this->settings = $settings;
        $this->form = $form;
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
        $this->addBottomButtons();
    }
    
    protected function addBottomButtons() {
        $this->addSubmitButton();
    }

    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }
    
    
    protected function buildView() {
        $this->openViewWrap();
        $this->buildForm();
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