<?php
/**
 * Description of AbstractRegionEcoFeeFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractRegionEcoFeeFormView extends MainWindowView {

    protected $region;
    protected $form;
    protected $addEcoFeeSections = true;

    public function __construct(GI_Form $form, AbstractRegion $region) {
        parent::__construct();
        $this->region = $region;
        $this->form = $form;
        $this->setWindowTitle($this->region->getProperty('region_name'));
    }
    
    /**
     * @param boolean $addEcoFeeSections
     */
    public function setAddEcoFeeSections($addEcoFeeSections) {
        $this->addEcoFeeSections = $addEcoFeeSections;
    }

    public function buildForm() {
        if (dbConnection::isModuleInstalled('accounting')) {
            $this->addDefaultTaxesSection();
            $this->form->addHTML('<hr />');
        }
        if ($this->addEcoFeeSections) {
            $this->addEcoFeesSection();
        }
        $this->addSubmitBtn();
    }
    
    protected function addDefaultTaxesSection() {
        $this->form->addHTML('<h2>Default Taxes</h2>');
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addDefaultPurchaseTaxField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addDefaultSalesTaxField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addDefaultPurchaseTaxField() {
        $this->form->addField('default_tax_purchase_qb_id', 'dropdown', array(
            'displayName'=>'Default Tax Code (Purchases)',
            'value'=>$this->region->getDefaultTaxCodeQBId('purchase'),
            'options'=> QBTaxCodeFactory::getOptionsArray(),
            'hideNull'=>true,
        ));
    }

    protected function addDefaultSalesTaxField() {
        $this->form->addField('default_tax_sales_qb_id', 'dropdown', array(
            'displayName' => 'Default Tax Code (Sales)',
            'value' => $this->region->getDefaultTaxCodeQBId('sales'),
            'options' => QBTaxCodeFactory::getOptionsArray(),
            'hideNull' => true,
        ));
    }

    protected function addDefaultSalesOrderLineProductField() {
        $value = $this->region->getDefaultSalesOrderLineProductQBId();
        $options = QBProductFactory::getProductOptionsArray($value);
        $this->form->addField('default_sales_order_line_product_qb_id', 'dropdown', array(
            'displayName' => 'Invoice Lines',
            'value' => $value,
            'options' => $options,
            'hideNull' => false,
        ));
    }

    protected function addDefaultSalesOrderLineAcEcoFeeProductField() {
        $value = $this->region->getDefaultSalesOrderLineACEcoFeeProductQBId();
        $options = QBProductFactory::getProductOptionsArray($value);
        $this->form->addField('default_sales_order_line_ac_eco_product_qb_id', 'dropdown', array(
            'displayName' => 'Invoice Lines (created from Eco Fee Sales Order Lines)',
            'value' => $value,
            'options' => $options,
            'hideNull' => false,
        ));
    }

    protected function addEcoFeesSection() {
        $this->form->addHTML('<h2>Eco Fees</h2>');
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addContactField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        $this->form->addHTML('<div class="form_rows_group">');
        $this->form->addHTML('<div id="eco_fees_rows" class="form_rows">'); //labels_on_first_row
        $this->addEcoFees();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="wrap_btns">');
        $this->addAddByUnitEcoFeeBtn();
        $this->addAddByContainerEcoFeeBtn();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addContactField() {
        $autocompURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => 'org,ind',
            'ajax' => 1,
            'catTypeRefs' => 'vendor',
        ));
        $this->form->addField('eco_fee_contact_id', 'autocomplete', array(
            'displayName' => 'Default Vendor Contact',
            'placeHolder' => 'start typing...',
            'autocompURL' => $autocompURL,
            'value' => $this->region->getProperty('eco_fee_contact_id'),
            'required' => false,
            'hideDescOnError' => false
        ));
    }

    protected function addEcoFees() {
        $formWasSubmitted = $this->form->wasSubmitted();
        $seqCount = 0;
        $ecoFees = $this->region->getEcoFees($this->form);
        foreach ($ecoFees as $ecoFee) {
            if (!$formWasSubmitted) {
                $ecoFee->setFieldSuffix($seqCount);
                $seqCount++;
            }
            $formView = $ecoFee->getFormView($this->form);
            $formView->buildForm();
        }
    }

    protected function addAddByUnitEcoFeeBtn() {
        $addURL = GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'addEcoFeeRow'
                        ), false, true);
        $this->form->addHTML('<span class="custom_btn add_form_row" data-add-to="eco_fees_rows" data-add-type="by_unit" data-add-url="' . $addURL . '"><span class="icon_wrap"><span class="icon primary plus"></span></span><span class="btn_text">By Unit</span></span>');
    }

    protected function addAddByContainerEcoFeeBtn() {
        $addURL = GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'addEcoFeeRow'
                        ), false, true);
        $this->form->addHTML('<span class="custom_btn add_form_row" data-add-to="eco_fees_rows" data-add-type="by_container_size" data-add-url="' . $addURL . '"><span class="icon_wrap"><span class="icon primary plus"></span></span><span class="btn_text">By Container Size</span></span>');
    }

    protected function addSubmitBtn() {
        $this->form->addHTML('<br />');
        $this->form->addHTML('<span class="submit_btn" title="Submit" tabindex="0">Submit</span>');
    }

    public function addViewBodyContent() {
        $this->addHTML($this->form->getForm());
    }
}
