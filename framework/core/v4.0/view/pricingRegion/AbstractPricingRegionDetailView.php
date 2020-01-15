<?php
/**
 * Description of AbstractPricingRegionDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractPricingRegionDetailView extends MainWindowView {
    
    protected $pricingRegion;
    
    public function __construct(AbstractPricingRegion $pricingRegion) {
        parent::__construct();
        $this->pricingRegion = $pricingRegion;
        $this->addSiteTitle('Pricing Regions');
        $this->addSiteTitle($this->pricingRegion->getProperty('title'));
        $this->setWindowTitle($this->pricingRegion->getProperty('title'));
        $listBarURL = $pricingRegion->getListBarURL();
        $this->setListBarURL($listBarURL);
    }
    
    protected function addEditBtn(){
        if (Permission::verifyByRef('edit_pricing_regions')) {
            $editURL = $this->pricingRegion->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" title="edit pricing region" class="custom_btn" ><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit</span></a>');
        }
    }
    
    protected function addWindowBtns(){
        $this->addEditBtn();
    }
    
    public function addViewBodyContent() {
        $includedCountriesAndRegions = $this->pricingRegion->getIncludedCountriesAndRegionsNamesArray();
        if (empty($includedCountriesAndRegions)) {
            $this->addHTML('<div class="columns halves">')
                    ->addHTML('<div class="column">');
            $this->addHTML('<h3>All Countries</h3>');
            $this->addHTML('</div>')
                    ->addHTML('<div class="column">');
            $this->addHTML('<h4>All States/Provinces/Territories</h4>');
            $this->addHTML('</div>')
                    ->addHTML('</div>');
        } else {
            foreach ($includedCountriesAndRegions as $countryName => $regionNameArray) {
                $this->addHTML('<div class="columns halves">')
                        ->addHTML('<div class="column">');
                $this->addHTML('<h3>' . $countryName . '</h3>');
                $this->addHTML('</div>')
                        ->addHTML('<div class="column">');
                if (empty($regionNameArray)) {
                    $this->addHTML('<h4>Entire Country</h4>');
                } else {
                    foreach ($regionNameArray as $regionName) {
                        $this->addHTML('<h4>'.$regionName.'</h4>');
                    }
                }
                $this->addHTML('</div>')
                        ->addHTML('</div>');
            }
        }
    }
    
}