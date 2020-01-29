<?php
/**
 * Description of AbstractREFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractREFormView extends MainWindowView {
    
    /**
     * @var GI_Form 
     */
    protected $form;
    /**
     * @var AbstractREListing
     */
    protected $reListing;
    /** @var AbstractGI_Uploader */
    protected $uploader = NULL;
    
    public function __construct(GI_Form $form, AbstractREListing $reListing) {
        parent::__construct();
        $this->form = $form;
        $this->reListing = $reListing;
        
        //Set title
        $viewTitle = $this->reListing->getViewTitle(false);
        if($this->reListing->getId()){
            $windowTitle = 'Edit ' . $viewTitle;
        } else {
            $windowTitle = 'Add ' . $viewTitle;
        }
        $this->addSiteTitle($windowTitle);
        $this->setWindowTitle('<span class="inline_block">' . $windowTitle . '</span>');
        
        //Set list URL
        $this->setListBarURL($this->reListing->getListBarURL());
    }

    public function setUploader(GI_Uploader $uploader = NULL) {
        $this->uploader = $uploader;
        return $this;
    }
    
    public function buildForm() {
        $this->buildFormBody();
        $this->buildFormFooter();
    }
    
    protected function buildFormBody() {
        $this->openFormBody();
        $this->addGeneralInfoFields();
        $this->addLocationFields();
        $this->addDetailInfoFields();
        $this->closeFormBody();
    }

    protected function openFormBody() {
        $this->form->addHTML('<div class="auto_columns thirds">');
    }
    
    protected function closeFormBody() {
        $this->form->addHTML('</div>');
    }
    
    protected function addGeneralInfoFields() {
        $this->form->addHTML('<div class="auto_column">');
        $this->addStatusField();
        $this->addPropertyTypeField();
        $this->addListPriceField();
        $this->addLotSizeField();
        $this->addYearBuiltField();
        $this->addSoldPriceField();
        $this->addSoldDateField();
        $this->form->addHTML('</div>');
    }
    
    protected function addStatusField($overWriteSettings = array()){
        $statusOptions = REListingStatusFactory::getOptionsArray();
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Status',
            'required' => true,
            'formElementClass' => 'autofocus_off',
            'options' => $statusOptions,
            'value' => $this->reListing->getProperty('re_listing_status_id')
        ), $overWriteSettings);
        
        $this->form->addField('re_listing_status_id', 'dropdown', $fieldSettings);
    }
    
    protected function addPropertyTypeField($overWriteSettings = array()){
        $propertyTypeOptions = $this->reListing->getPropertyTypeTagsOptionArray();
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Property Type',
            'required' => true,
            'options' => $propertyTypeOptions,
            'value' => $this->reListing->getLinkedPropertyTypeTagId(),
        ), $overWriteSettings);
        
        $this->form->addField('property_type', 'dropdown', $fieldSettings);
    }
    
    protected function addLocationFields() {
        $this->form->addHTML('<div class="auto_column">');
        $this->addAddressField();
        $this->addStreetNameField();
        $this->addPostalCodeField();
        $this->addCityField();
        $this->addSubAreaField();
        $this->addAreaField();
        $this->addRegionField();
        $this->form->addHTML('</div>');
    }
    
    protected function addAddressField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Address',
            'value' => $this->reListing->getProperty('addr')
        ), $overWriteSettings);
        
        $this->form->addField('addr', 'text', $fieldSettings);
    }
    
    protected function addStreetNameField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Street Name',
            'value' => $this->reListing->getProperty('street_name')
        ), $overWriteSettings);
        
        $this->form->addField('street_name', 'text', $fieldSettings);
    }
    
    protected function addPostalCodeField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Postal Code',
            'value' => $this->reListing->getProperty('postal_code')
        ), $overWriteSettings);
        
        $this->form->addField('postal_code', 'text', $fieldSettings);
    }
    
    protected function addCityField($overWriteSettings = array()) {
        $acFieldName = 'mls_city_id';
        $acURL = GI_URLUtils::buildURL(array(
                    'controller' => 'mls',
                    'action' => 'autocompMLSCity',
                    'ajax' => 1,
                    //'valueColumn' => 'id',
                    'autocompField' => $acFieldName
                        ), false, true);
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'City',
                    'placeHolder' => 'City Name',
                    'autocompURL' => $acURL,
                    'autocompMinLength' => 0,
                    'autocompMultiple' => false,
                    'autocompLimit' => 1,
                    'value' => $this->reListing->getProperty($acFieldName)
                        ), $overWriteSettings);
        $this->form->addField($acFieldName, 'autocomplete', $fieldSettings);
    }
    
    protected function addSubAreaField($overWriteSettings = array()) {
        $acFieldName = 'mls_sub_area_id';
        $acURL = GI_URLUtils::buildURL(array(
                    'controller' => 'mls',
                    'action' => 'autocompMLSSubArea',
                    'ajax' => 1,
                    //'valueColumn' => 'id',
                    'autocompField' => $acFieldName
                        ), false, true);
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Sub Area',
                    'placeHolder' => 'Sub Area Name',
                    'autocompURL' => $acURL,
                    'autocompMinLength' => 0,
                    'autocompMultiple' => false,
                    'autocompLimit' => 1,
                    'value' => $this->reListing->getProperty($acFieldName)
                        ), $overWriteSettings);
        $this->form->addField($acFieldName, 'autocomplete', $fieldSettings);
    }
    
    protected function addAreaField($overWriteSettings = array()) {
        $acFieldName = 'mls_area_id';
        $acURL = GI_URLUtils::buildURL(array(
                    'controller' => 'mls',
                    'action' => 'autocompMLSArea',
                    'ajax' => 1,
                    //'valueColumn' => 'id',
                    'autocompField' => $acFieldName
                        ), false, true);
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Area',
                    'placeHolder' => 'Area Name',
                    'autocompURL' => $acURL,
                    'autocompMinLength' => 0,
                    'autocompMultiple' => false,
                    'autocompLimit' => 1,
                    'value' => $this->reListing->getProperty($acFieldName)
                        ), $overWriteSettings);
        $this->form->addField($acFieldName, 'autocomplete', $fieldSettings);
    }
    protected function addRegionField($overWriteSettings = array()){
        $regionOptions = GeoDefinitions::getRegions(false);
        $province = $this->reListing->getProperty('province');
        if (empty($province)) {
            $province = 'BC';
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Province',
            'required' => true,
            'optionGroups' => $regionOptions,
            'value' => $province,
        ), $overWriteSettings);
        
        $this->form->addField('province', 'dropdown', $fieldSettings);
    }
    
    protected function addListPriceField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'List Price',
            'value' => $this->reListing->getProperty('list_price')
        ), $overWriteSettings);
        
        $this->form->addField('list_price', 'money', $fieldSettings);
    }
    
    protected function addLotSizeField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Lot Size (sq ft)',
            'value' => $this->reListing->getProperty('lot_size_sqft')
        ), $overWriteSettings);
        
        $this->form->addField('lot_size_sqft', 'integer_pos', $fieldSettings);
    }
    
    protected function addYearBuiltField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Year Built',
            'value' => $this->reListing->getProperty('year')
        ), $overWriteSettings);
        
        $this->form->addField('year', 'integer_pos', $fieldSettings);
    }
    
    protected function addSoldPriceField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Sold Price',
            'value' => $this->reListing->getProperty('sold_price')
        ), $overWriteSettings);
        
        $this->form->addField('sold_price', 'money', $fieldSettings);
    }
    
    protected function addSoldDateField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Sold Date',
            'value' => $this->reListing->getProperty('sold_date')
        ), $overWriteSettings);
        
        $this->form->addField('sold_date', 'date', $fieldSettings);
    }
    
    protected function addDetailInfoFields() {
        $this->form->addHTML('<div class="auto_column">');
        $this->addPublicRemarksField();
        $this->addVirtualTourURLField();
        $this->addImageUploaderField();
        $this->form->addHTML('</div>');
    }
    
    protected function addPublicRemarksField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Public Remarks',
            'value' => $this->reListing->getProperty('public_remarks')
        ), $overWriteSettings);
        
        $this->form->addField('public_remarks', 'textarea', $fieldSettings);
    }
    
    protected function addVirtualTourURLField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Virtual Tour URL',
            'value' => $this->reListing->getProperty('virtual_tour_url')
        ), $overWriteSettings);
        
        $this->form->addField('virtual_tour_url', 'text', $fieldSettings);
    }
    
    protected function addImageUploaderField() {
        if(!empty($this->uploader)){
            $this->form->addHTML($this->uploader->getHTMLView());
        }
    }
    
    
    protected function buildFormFooter() {
        $this->openFormFooter();
        $this->addButtons();
        $this->closeFormFooter();
    }
    
     protected function openFormFooter($class ='') {
        $this->form->addHTML('<div class="form_footer'.$class.'">');
    }
    
    protected function closeFormFooter() {
        $this->form->addHTML('</div><!--.form_footer-->');
    }
    
    protected function addButtons() {
        $this->addSubmitBtn();
    }

    protected function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn" tabindex="0" title="Submit">Submit</span>');
    }

    protected function addViewBodyContent(){
        $this->addHTML($this->form->getForm());
    }   

}
