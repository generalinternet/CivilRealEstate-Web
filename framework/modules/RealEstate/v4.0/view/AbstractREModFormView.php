<?php
/**
 * Description of AbstractREFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractREModFormView extends AbstractREFormView {
   
    /**
     * @var AbstractMLSListing
     */
    protected $mlsListing;
    
    public function setMLSListing(AbstractMLSListing $mlsListing) {
        $this->mlsListing = $mlsListing;
    }

    protected function buildViewHeader(){
        //Don't buidl view header if MLS listing hasn't been selected, because buildSearchMLSForm has the view header instead
        if (empty($this->mlsListing->getId())) {
            return $this;
        }
        return parent::buildViewHeader();
    }

    protected function openFormBody() {
        $this->form->addHTML('<div class="auto_columns halves">');
    }
    
    public function buildForm() {
        if (empty($this->mlsListing->getId())) {
            $this->buildSearchMLSForm();
        } else {
            $this->buildFormBody();
            $this->buildFormFooter();
        }
    }
    
    protected function buildFormBody() {
        $this->openFormBody();
        $this->addGeneralInfoFields();
        $this->addDetailInfoFields();
        $this->closeFormBody();
    }
    
    protected function buildSearchMLSForm() {
        $this->addHTML('<div class="view_wrap">');
            $this->addHTML('<div class="view_header">');
                $this->addHTML('<h2 class="main_head">Search MLS Listing</h2>');
            $this->addHTML('</div>');
            $this->addHTML('<div class="view_body" id="search_mls_wrap">'); 
                $this->addSearchMLSBox();
                $this->addSearchMLSTable();
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    protected function addSearchMLSBox() {
        $urlAttrs = array(
            'controller'=>'mls',
            'action'=>'index',
            'type'=>$this->mlsListing->getTypeRef(),
            'search'=>1,
            'targetId'=>'mls_listing_list',
            'modify'=>1,
        );
        if(GI_URLUtils::getAttribute('queryId')){
            $urlAttrs['queryId'] = GI_URLUtils::getAttribute('queryId');
        }
        $url = GI_URLUtils::buildURL($urlAttrs, false, true);

        $this->addHTML('<div class="ajaxed_contents auto_load hide_advanced_search hide_close_btn" data-url="'.$url.'" id="search-box-form-wrap"></div>');
    }
    
    protected function addSearchMLSTable() {
        $this->addHTML('<div id="mls_listing_list"></div>');
    }
    
    protected function addGeneralInfoFields() {
        $this->form->addHTML('<div class="auto_column">');
        $this->addStatusField();
        $this->addListPriceField();
        $this->addSoldPriceField();
        $this->addSoldDateField();
        $this->addMLSListingIdField();
        $this->form->addHTML('</div>');
    }
    
    protected function addMLSListingIdField(){
        $mlsListingId = '';
        if (!empty($this->mlsListing)) {
            $mlsListingId = $this->mlsListing->getId();
        }
        $this->form->addField('mls_listing_id', 'hidden', array(
            'value' => $mlsListingId,
        ));
    }
}
