<?php
/**
 * Description of AbstractRegionQBSettingsFormView
 *
 * @author General Internet
 * @copyright  209 General Internet
 * @version    2.0.0
 */
abstract class AbstractRegionQBSettingsFormView extends GI_View {

    protected $region;
    protected $form;
    protected $addEcoFeeSection = true;

    public function __construct(GI_Form $form, AbstractRegion $region) {
        parent::__construct();
        $this->region = $region;
        $this->form = $form;
    }
    
    /**
     * @param boolean $addEcoFeeSection
     */
    public function setAddEcoFeeSection($addEcoFeeSection) {
        $this->addEcoFeeSection = $addEcoFeeSection;
    }

    public function buildForm() {
        $this->addFormTitle();
        $this->addDefaultSalesSettingsSection();
        $this->addSubmitBtn();
    }

    protected function addFormTitle() {
        $this->form->addHTML('<h1>Quickbooks Settings - ' . $this->region->getProperty('region_name') . '</h1>');
    }

    protected function addDefaultSalesSettingsSection() {
        $this->form->addHTML('<h2>Sales - Default Quickbooks Products/Services</h2>');
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addDefaultSalesOrderLineProductField();
        if ($this->addEcoFeeSection) {
            $this->form->addHTML('<br />');
            $this->addDefaultSalesOrderLineAcEcoFeeProductField();
        }
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addDefaultSalesOrderLineProductField() {
        $value = $this->region->getDefaultSalesOrderLineProductQBId();
        $options = QBProductFactory::getProductOptionsArray($value);
        $this->form->addField('default_sales_order_line_product_qb_id_' . $this->region->getId(), 'dropdown', array(
            'displayName' => 'Invoice Lines',
            'value' => $value,
            'options' => $options,
            'hideNull' => false,
            'formElementClass'=>'autofocus_off'
        ));
    }

    protected function addDefaultSalesOrderLineAcEcoFeeProductField() {
        $value = $this->region->getDefaultSalesOrderLineACEcoFeeProductQBId();
        $options = QBProductFactory::getProductOptionsArray($value);
        $this->form->addField('default_sales_order_line_ac_eco_product_qb_id_' . $this->region->getId(), 'dropdown', array(
            'displayName' => 'Invoice Lines (Eco Fee)',
            'value' => $value,
            'options' => $options,
            'hideNull' => false,
            'formElementClass'=>'autofocus_off'
        ));
    }

    protected function addSubmitBtn() {
        $this->form->addHTML('<br />');
        $this->form->addHTML('<span class="submit_btn" title="Submit" tabindex="0">Submit</span>');
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }

    public function buildView() {
        $this->openViewWrap();
        $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
