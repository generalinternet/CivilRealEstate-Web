<?php
class RealtyModifyListingView extends GI_View{
    
    protected $form;
    protected $reListing;
    protected $type;
    public $uploader;
    
    public function __construct($form, $reListing) {
        $this->form = $form;
        $this->reListing = $reListing;
        $this->type = $reListing->getTypeRef();
        parent::__construct();

        $this->addCSS('resources/css/forms_admin.css');
        $this->addCSS('resources/css/files_admin.css');
        $this->addCSS('resources/css/admin.css');
    }
    
    public function setUploader(GI_Uploader $uploader){
        $this->uploader = $uploader;
        return $this;
    }
    
    public function buildView(){
        $this->addHTML('<div class="content_padding">
                            <h1 class="page_title">Modify Listing</h1>
                            ' . $this->form->getForm() . '
                        </div>');
    }
    
    public function buildForm(){
        $form = $this->form;
        $reListing = $this->reListing;
        
        $statusModels = REListingStatusFactory::search()->filter('selectable', 1)->select();
        $statusOptions = array();
        foreach($statusModels as $status){
            $statusOptions[$status->getProperty('id')] = $status->getProperty('title');
        }
        
        $form->addHTML('<div class="col-sm-6">');
        $form->addField('re_listing_status_id', 'dropdown', array(
            'displayName' => 'Status',
            'required' => true,
            'options' => $statusOptions,
            'value' => $reListing->getProperty('re_listing_status_id')
        ));
        
//        $mlsListing = $reListing->getMLSListing();
//        $mlsNumber = '';
//        if(!empty($mlsListing)){
//            $mlsNumber = $mlsListing->getProperty('mls_number');
//        }
        
//        $form->addField('mls_number', 'text', array(
//            'displayName' => 'MLS Number',
//            'required' => true,
//            'options' => $statusOptions,
//            'value' => $mlsNumber
//        ));
        
        $form->addField('sold_price', 'money', array(
            'displayName' => 'Sold Price',
            'value' => $reListing->getProperty('sold_price')
        ));
        
        $form->addField('sold_date', 'date', array(
            'displayName' => 'Sold Date',
            'value' => $reListing->getProperty('sold_date')
        ));
        
        $form->addField('list_price', 'money', array(
            'displayName' => 'List Price',
            'value' => $reListing->getProperty('list_price')
        ));
        $form->addHTML('</div>');
        
        $form->addHTML('<div class="col-sm-6">');
        $form->addField('public_remarks', 'textarea', array(
            'displayName' => 'Public Remarks',
            'value' => $reListing->getProperty('public_remarks')
        ));
        
        $form->addField('virtual_tour_url', 'text', array(
            'displayName' => 'Virtual Tour URL',
            'value' => $reListing->getProperty('virtual_tour_url')
        ));
        
        if($this->type == 'com'){
            $this->buildComForm();
        }
        else{
            $this->buildResForm();
        }
        
        if(!empty($this->uploader)){
            $form->addHTML($this->uploader->getHTMLView());
        }
        
        $form->addHTML('<a class="submit_btn">Submit</a>');
        $form->addHTML('</div>');
    }
    
    public function buildResForm(){
        $form = $this->form;
        $reListing = $this->reListing;

    }
    
    public function buildComForm(){
        $form = $this->form;
        $reListing = $this->reListing;

    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}
