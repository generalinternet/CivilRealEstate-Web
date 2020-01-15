<?php
/**
 * Description of AbstractRegionEcoFeeDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.3
 */
abstract class AbstractRegionEcoFeeDetailView extends GI_View {
    
    protected $region;
    protected $tabbed = false;
    
    public function __construct(AbstractRegion $region) {
        parent::__construct();
        $this->region = $region;
    }
    
    public function setIsTabbed($isTabbed) {
        $this->tabbed = $isTabbed;
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
        $this->addHTML('<h1>'.$this->region->getProperty('region_name').'</h1>');
    }
    
    protected function addButtons() {
        $this->addHTML('<div class="right_btns">');
        $this->addEditButton();
        $this->addHTML('</div>');
    }

    protected function addEditButton() {
        if (Permission::verifyByRef('edit_eco_fees')) {
          //  $title = $this->region->getEcoFeeIndexTitle();
            $editURL = GI_URLUtils::buildURL(array(
                        'controller' => 'admin',
                        'action' => 'editEcoFees',
                        'id' => $this->region->getProperty('id')
            ));
            $this->addHTML('<a href="' . $editURL . '" title="Edit" class="custom_btn" ><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit</span></a>');
        }
    }

    protected function buildViewBody() {
        $this->addDefaultTaxesSection();
        $this->addHTML('<hr />');
        $this->addEcoFeesSection();
    }

    protected function addDefaultTaxesSection() {
        $this->addHTML('<h2>Default Taxes</h2>');
        $this->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $purchaseTaxName = $this->region->getDefaultTaxCodeName('purchase');
        if (empty($purchaseTaxName)) {
            $purchaseTaxName = 'Not Set';
        }
        $this->addContentBlock($purchaseTaxName, 'Default Tax Code (Purchases)');
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        $salesTaxName = $this->region->getDefaultTaxCodeName('sales');
        if (empty($salesTaxName)) {
            $salesTaxName = 'Not Set';
        }
        $this->addContentBlock($salesTaxName, 'Default Tax Code (Sales)');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addEcoFeesSection() {
        $this->addHTML('<h2>Eco Fees</h2>');
        $this->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addEcoFeeContactSection();
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        //Do Nothing
        $this->addHTML('</div>')
                ->addHTML('</div>');
        $this->addEcoFessTable();
    }

    protected function addEcoFeeContactSection() {
        $defaultEcoFeeContact = $this->region->getDefaultEcoFeeContact();
        if (!empty($defaultEcoFeeContact)) {
            $this->addContentBlock($defaultEcoFeeContact->getName(), 'Default Vendor Contact');
        }
    }

    protected function addEcoFessTable() {
        $uiTable = $this->region->getEcoFeeUITableView();
        if (!empty($uiTable)) {
            $this->addHTML($uiTable->getHTMLView());
        } else {
            $this->addHTML('<p>There are no eco fees defined.</p>');
        }
    }

    protected function buildViewFooter() {
        
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}