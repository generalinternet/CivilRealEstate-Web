<?php

/**
 * Description of AbstractPaymentAccountFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentAccountFormView extends GI_View {

    protected $form;
    protected $paymentAccount;
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, AbstractPaymentAccount $paymentAccount) {
        parent::__construct();
        $this->form = $form;
        $this->paymentAccount = $paymentAccount;
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
        $verb = 'Edit';
        if (empty($this->paymentAccount->getProperty('id'))) {
            $verb = 'Add';
        }
        $this->form->addHTML('<h1>'.$verb.' Account</h1>');
    }

    protected function buildFormBody() {
        $this->addNameField();
        $this->addTypeField();
        $this->addCurrencyField();
    }
    
    protected function addNameField() {
        $this->form->addField('name', 'text', array(
            'displayName'=>'Account Name',
            'value'=>$this->paymentAccount->getProperty('name'),
            'required'=>true,
        ));
    }
    
    protected function addTypeField() {
        $options = PaymentAccountFactory::getTypesArray();
        if (isset($options['account'])) {
            unset($options['account']);
        }
        $this->form->addField('type', 'dropdown', array(
            'options'=>$options,
            'value'=>$this->paymentAccount->getTypeRef(),
            'displayName'=>'Account Type',
            'hideNull'=>true,
            'required'=>true,
        ));
    }
    
    protected function addCurrencyField() {
        $options = CurrencyFactory::getOptionsArray('name');
        $this->form->addField('currency_id', 'dropdown', array(
            'options'=>$options,
            'value'=>$this->paymentAccount->getProperty('currency_id'),
            'required'=>true,
            'hideNull'=>true,
            'displayName'=>'Currency'
        ));
    }
    
    protected function buildFormFooter() {
        $this->addSubmitButton();
    }
    
    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }
    
    protected function buildView() {
        $this->buildForm();
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
