<?php
/**
 * Description of AbstractExportAdjustmentsToQuickbooksContentView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */

abstract class AbstractExportAdjustmentsToQuickbooksContentView extends GI_View {
    
    protected $curTabKey = 'not_yet_exported';
    protected $cogsUITable = NULL;

    protected $returnedURL = NULL;
    protected $damagedURL = NULL;
    protected $wasteURL = NULL;
    
    
    protected $iconColour = 'primary';
    protected $searchStart = NULL;
    protected $searchEnd = NULL;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function setCurTabKey($curTabKey) {
        $this->curTabKey = $curTabKey;
    }
    
    public function setSearchStart($searchStart) {
        $this->searchStart = $searchStart;
    }
    
    public function setSearchEnd($searchEnd) {
        $this->searchEnd = $searchEnd;
    }
    
    public function setCogsUITable(AbstractUITableView $uiTableView) {
        $this->cogsUITable = $uiTableView;
    }

    public function setReturnedURL($returnedURL) {
        $this->returnedURL = $returnedURL;
    }
    
    public function setDamagedURL($damagedURL) {
        $this->damagedURL = $damagedURL;
    }
    
    public function setWasteURL($wasteURL) {
        $this->wasteURL = $wasteURL;
    }
    
    protected function buildView() {
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
    }
    
    protected function buildViewHeader() {
        
    }

    protected function buildViewBody() {
        $this->addSoldStockSection();
        $this->addHTML('<hr/>');
        $this->addReturnedStockSection();
        $this->addHTML('<hr/>');
        $this->addDamagedStockSection();
        $this->addHTML('<hr/>');
        $this->addWastedStockSection();
    }
    
    protected function addSoldStockSection(){
        if (!empty($this->cogsUITable)) {
            $this->addHTML('<h2>Sold Stock</h2>');
            if ($this->curTabKey == 'exported') {
                $exportCogsCSVAttributes = array(
                    'controller'=>'accounting',
                    'action'=>'exportAdjustmentsExportedToQuickbooks',
                    'type' => 'sales_order_lines',
                );
                if (!empty($this->searchStart)) {
                    $exportCogsCSVAttributes['searchStart'] = $this->searchStart;
                }
                if (!empty($this->searchEnd)) {
                    $exportCogsCSVAttributes['searchEnd'] = $this->searchEnd;
                }
                $exportCogsCSVUrl = GI_URLUtils::buildURL($exportCogsCSVAttributes);
                $this->addHTML('<a href="' . $exportCogsCSVUrl . '" class="custom_btn" title="Download Results as CSV" target="_blank"><span class="icon_wrap border circle"><span class="icon ' . $this->iconColour . ' download"></span></span><span class="btn_text">CSV Export</span></a>');
            }
            $this->addHTML($this->cogsUITable->getHTMLView());
        }
    }
    
    protected function addReturnedStockSection(){
        if (!empty($this->returnedURL)) {
            $this->addHTML('<h2>Returned Stock</h2>');
            if ($this->curTabKey == 'exported') {
                $exportInventoryCSVAttributes = array(
                    'controller' => 'accounting',
                    'action' => 'exportAdjustmentsExportedToQuickbooks',
                    'type' => 'returned',
                );
                if (!empty($this->searchStart)) {
                    $exportInventoryCSVAttributes['searchStart'] = $this->searchStart;
                }
                if (!empty($this->searchEnd)) {
                    $exportInventoryCSVAttributes['searchEnd'] = $this->searchEnd;
                }
                $exportInventoryCSVUrl = GI_URLUtils::buildURL($exportInventoryCSVAttributes);
                $this->addHTML('<a href="' . $exportInventoryCSVUrl . '" class="custom_btn" title="Download Results as CSV" target="_blank"><span class="icon_wrap border circle"><span class="icon ' . $this->iconColour . ' download"></span></span><span class="btn_text">CSV Export</span></a>');
            }
            $this->addHTML('<div class="ajaxed_contents auto_load" data-url="'.$this->returnedURL.'"></div>');
        }
    }

    protected function addDamagedStockSection() {
        if (!empty($this->damagedURL)) {
            $this->addHTML('<h2>Damaged Sold Stock</h2>');
            if ($this->curTabKey == 'exported') {
                $exportInventoryCSVAttributes = array(
                    'controller' => 'accounting',
                    'action' => 'exportAdjustmentsExportedToQuickbooks',
                    'type' => 'damaged',
                );
                if (!empty($this->searchStart)) {
                    $exportInventoryCSVAttributes['searchStart'] = $this->searchStart;
                }
                if (!empty($this->searchEnd)) {
                    $exportInventoryCSVAttributes['searchEnd'] = $this->searchEnd;
                }
                $exportInventoryCSVUrl = GI_URLUtils::buildURL($exportInventoryCSVAttributes);
                $this->addHTML('<a href="' . $exportInventoryCSVUrl . '" class="custom_btn" title="Download Results as CSV" target="_blank"><span class="icon_wrap border circle"><span class="icon ' . $this->iconColour . ' download"></span></span><span class="btn_text">CSV Export</span></a>');
            }
            $this->addHTML('<div class="ajaxed_contents auto_load" data-url="' . $this->damagedURL . '"></div>');
        }
    }

    protected function addWastedStockSection() {
        if (!empty($this->wasteURL)) {
            $this->addHTML('<h2>Damaged/Wasted Unsold Stock</h2>');
            if ($this->curTabKey == 'exported') {
                $exportInventoryCSVAttributes = array(
                    'controller' => 'accounting',
                    'action' => 'exportAdjustmentsExportedToQuickbooks',
                    'type' => 'waste',
                );
                if (!empty($this->searchStart)) {
                    $exportInventoryCSVAttributes['searchStart'] = $this->searchStart;
                }
                if (!empty($this->searchEnd)) {
                    $exportInventoryCSVAttributes['searchEnd'] = $this->searchEnd;
                }
                $exportInventoryCSVUrl = GI_URLUtils::buildURL($exportInventoryCSVAttributes);
                $this->addHTML('<a href="' . $exportInventoryCSVUrl . '" class="custom_btn" title="Download Results as CSV" target="_blank"><span class="icon_wrap border circle"><span class="icon ' . $this->iconColour . ' download"></span></span><span class="btn_text">CSV Export</span></a>');
            }
            $this->addHTML('<div class="ajaxed_contents auto_load" data-url="'.$this->wasteURL.'"></div>');
        }
    }

    protected function buildViewFooter() {
        
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}