<?php
/**
 * Description of AbstractPricingRegionFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractPricingRegionFormView extends MainWindowView {
    
    protected $form;
    protected $pricingRegion;
    protected $formBuilt = false;
    protected $pageTitle = 'Add Pricing Region';
    
    public function __construct(GI_Form $form, AbstractPricingRegion $pricingRegion) {
        parent::__construct();
        $this->form = $form;
        $this->pricingRegion = $pricingRegion;
        if (!empty($pricingRegion->getProperty('id'))) {
            $this->pageTitle = 'Edit Pricing Region';
        } else {
            $this->pageTitle = 'Add Pricing Region';
        }
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/pricing_regions.js');
        
        $this->buildForm();
        $this->addSiteTitle($this->pageTitle);
        $this->setWindowTitle($this->pageTitle);
        $listBarURL = $pricingRegion->getListBarURL();
        $this->setListBarURL($listBarURL);
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->form->addHTML('<div class="columns thirds">')
                    ->addHTML('<div class="column">');
            $this->form->addField('title', 'text', array(
                'required'=>true,
                'value'=>$this->pricingRegion->getProperty('title'),
            ));
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="column">');
            //Countries Dropdown
            $countries = GeoDefinitions::getCountries();
            $this->form->addField('country_refs', 'select', array(
                'options'=>$countries,
                'value'=>$this->pricingRegion->getCountryRefs(),
                'displayName'=>'Countries'
            ));
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="column">');
            //Regions Dropdown
            $regions = GeoDefinitions::getRegions(true);
            $this->form->addField('region_refs', 'select', array(
                'optionGroups'=>$regions,
                'value'=>$this->pricingRegion->getRegionRefs(),
                'displayName'=>'States/Provinces/Territories',
                'formElementClass'=>'hide_disabled',
            ));
            $this->form->addHTML('</div>')
                    ->addHTML('</div>');
            if (empty($this->pricingRegion->getProperty('id'))) {
                $cancelURL = GI_URLUtils::buildURL(array(
                    'controller'=>'admin',
                    'action'=>'index'
                ));
            } else {
                $cancelURL = $this->pricingRegion->getViewURL();
            }
            $this->form->addHTML('<div class="center_btns wrap_btns">');
            $this->form->addHTML('<a href="'.$cancelURL.'" class="other_btn gray">Cancel</a>');
            $this->form->addHTML('<span class="submit_btn">Submit</span>');
            $this->form->addHTML('</div>');
          
            $this->formBuilt = true;
        }
    }
    
    public function addViewBodyContent() {
        $this->addHTML($this->form->getForm());
    }

}