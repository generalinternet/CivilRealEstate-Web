<?php
/**
 * Description of AbstractAdminEditCurrencyFormView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
abstract class AbstractAdminEditCurrencyFormView extends GI_View {
    
    protected $form;
    protected $currencies;
    
    public function __construct(GI_Form $form, $currencies) {
        parent::__construct();
        $this->form = $form;
        $this->currencies = $currencies;
        $this->buildForm();
    }
    
    public function buildForm() {
        foreach ($this->currencies as $currency) {
            $currencyRef = $currency->getProperty('ref');
            if ($currencyRef === 'usd') {
                $formLocked = true;
            } else {
                $formLocked = false;
            }
            $currencyId = $currency->getProperty('id');
            $this->form->startFieldset($currency->getProperty('name'));
            $this->form->addField('sys_ex_rate_to_usd_' . $currencyId, 'decimal', array(
                'required'=>true,
                'value'=>$currency->getProperty('sys_ex_rate_to_usd'),
                'displayName'=>'Default Exchange Rate to USD',
                'readOnly'=>$formLocked,
            ));
            $this->form->endFieldset();
        }
        $this->form->addContent('<span class="submit_btn" data-form-id="' . $this->form->getFormId() . '">Submit</span>');
    }
    
    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap()
                ->addHTML($this->form->getForm())
                ->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
