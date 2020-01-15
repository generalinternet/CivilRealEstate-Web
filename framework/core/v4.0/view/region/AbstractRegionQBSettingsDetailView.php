<?php
/**
 * Description of AbstractRegionQBSettingsDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    2.0.0
 */
abstract class AbstractRegionQBSettingsDetailView extends GI_View {

    protected $region;
    protected $tabbed = false;
    protected $addEcoFeeSection = true;

    public function __construct(AbstractRegion $region) {
        parent::__construct();
        $this->region = $region;
    }

    public function setIsTabbed($isTabbed) {
        $this->tabbed = $isTabbed;
    }
    
    public function setAddEcoFeeSection($addEcoFeeSection) {
        $this->addEcoFeeSection = $addEcoFeeSection;
    }

    protected function buildView() {
        $this->openViewWrap();
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->closeViewWrap();
    }

    protected function openViewWrap() {
        if (!$this->tabbed) {
            $this->addHTML('<div class="content_padding">');
        }
    }

    protected function closeViewWrap() {
        if (!$this->tabbed) {
            $this->addHTML('</div>');
        }
    }

    protected function buildViewHeader() {
        $this->addButtons();
        $this->addHTML('<h1>' . $this->region->getProperty('region_name') . '</h1>');
        $this->addHTML('<hr />');
    }

    protected function addButtons() {
        $this->addHTML('<div class="right_btns">');
        $this->addEditButton();
        $this->addHTML('</div>');
    }

    protected function addEditButton() {
        $editURL = GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
                    'action' => 'editRegionQBSettings',
                    'id' => $this->region->getProperty('id'),
                    'ajax' => 1,
        ));
        $this->addHTML('<a href="' . $editURL . '" title="Edit" class="custom_btn open_modal_form" data-modal-class="medium_sized"><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit</span></a>');
    }

    protected function buildViewBody() {
        $this->addSalesSettingsSection();
    }

    protected function addSalesSettingsSection() {
        $this->addHTML('<h2>Sales - Default Quickbooks Products/Services</h2>');
        $this->addHTML('<p>These settings are used to pre-populate dropdown fields for Invoice line items, when an invoice is created from a Sales Order.');
        $this->addHeaderRow();
        $this->addInvoiceLineSettingSection();
        if ($this->addEcoFeeSection) {
            $this->addInvoiceLineEcoFeeSection();
        }
    }

    protected function addInvoiceLineSettingSection() {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Invoice Lines</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $product = QBProductFactory::getModelByQBId($this->region->getDefaultSalesOrderLineProductQBId());
        if (empty($product)) {
            $prodName = 'Not Set';
        } else {
            $prodName = $product->getName();
        }
        $this->addHTML('<p>' . $prodName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>' . $this->region->getQBSettingDescription('invoice_line') . '</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addInvoiceLineEcoFeeSection() {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h4>Invoice Lines (Eco Fee)</h4>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $product = QBProductFactory::getModelByQBId($this->region->getDefaultSalesOrderLineACEcoFeeProductQBId());
        if (empty($product)) {
            $prodName = 'Not Set';
        } else {
            $prodName = $product->getName();
        }
        $this->addHTML('<p>' . $prodName . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<p>' . $this->region->getQBSettingDescription('invoice_line_eco') . '</p>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addHeaderRow() {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h3>Setting</h3>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h3>Value</h3>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<h3>Description</h3>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
        $this->addHTML('<br />');
    }

    protected function buildViewFooter() {
        
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}