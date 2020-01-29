<?php
/**
 * Description of AbstractContactQBLinkFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */

abstract class AbstractContactQBLinkFormView extends GI_View {
    
    protected $form = NULL;
    protected $contact = NULL;
    protected $contactQBTypeRef = NULL;
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, AbstractContact $contact, $contactQBTypeRef) {
        parent::__construct();
        $this->form = $form;
        $this->contact = $contact;
        $this->contactQBTypeRef = $contactQBTypeRef;
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }
    
    protected function buildView() {
        $this->buildForm();
        $this->openViewWrap();
        $this->addHTML($this->form->getForm(''));
        $this->closeViewWrap();
    }

    protected function buildFormHeader() {
        $label = 'Contact';
        if ($this->contactQBTypeRef == 'supplier') {
            $label = 'Supplier';
        } else if ($this->contactQBTypeRef == 'customer') {
            $label = 'Customer';
        }
        $this->addHTML('<h1>Link to existing Quickbooks ' . $label . '</h1>');
    }

    protected function buildFormBody() {
        $this->addContactQBField();
    }

    protected function addContactQBField() {
        $autoCompURLProps = array(
            'controller' => 'contact',
            'action' => 'autocompContactQB',
            'type'=>$this->contactQBTypeRef,
            'ajax' => 1
        );
        $userAutoCompURL = GI_URLUtils::buildURL($autoCompURLProps);
        $label = 'Contact';
        if ($this->contactQBTypeRef == 'supplier') {
            $label = 'Supplier';
        } else if ($this->contactQBTypeRef == 'customer') {
            $label = 'Customer';
        }
        $this->form->addField('contact_qb_id', 'autocomplete', array(
            'displayName' => 'Quickbooks ' . $label,
            'placeHolder' => 'start typing...',
            'autocompURL' => $userAutoCompURL,
            'required'=>true,
        ));
    }

    protected function buildFormFooter() {
        $this->addButtons();
    }

    protected function addButtons() {
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addSubmitBtn();
        $this->addCancelBtn();
        $this->form->addHTML('</div>');
    }

    public function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn">Link</span>');
    }

    public function addCancelBtn(){
        $this->form->addHTML('<span class="other_btn gray close_gi_modal">Cancel</span>');
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
