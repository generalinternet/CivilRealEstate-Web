<?php
/**
 * Description of AbstractContactManageRelationshipFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.2
 */
abstract class AbstractContactManageRelationshipFormView extends MainWindowView {
    
    protected $form;
    protected $contact;
    protected $linkedTypeRef;
    protected $linkedContact;
    protected $relation;
    
    public function __construct(GI_Form $form, AbstractContact $contact, $linkedTypeRef, $relation) {
        parent::__construct();
        $this->form = $form;
        $this->contact = $contact;
        $this->linkedTypeRef = $linkedTypeRef;
        $this->linkedContact = ContactFactory::buildNewModel($linkedTypeRef);
        $this->relation = $relation;
        
        $contactName = $this->contact->getName();
        $linkedTypeTitle = $this->linkedContact->getViewTitle();
        $this->addSiteTitle($contactName . ' | Manage ' .$linkedTypeTitle);
        
        //Set list URL
        $contactCat = $this->contact->getContactCat();
        if (empty($contactCat)) {
            $contactCat = $this->contact->getDefaultContactCat();
        }
        $this->setListBarURL($contactCat->getListBarURL());
        //Set window area title
         $this->setWindowTitle($contactName.'<span class="sub_head">Manage ' . $linkedTypeTitle . '</span>');
    }
    //View Header
    protected function addCancelBtn(){
        $contactURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'view',
            'id' => $this->contact->getProperty('id'),
        ), false, true);
        
        $this->addHTML('<a href="' . $contactURL . '" class="custom_btn" title="Back to Contact">'.GI_StringUtils::getIcon('void').'<span class="btn_text">Cancel</span></a>');
    }
    
    protected function addWindowBtns() {
        $this->addCancelBtn();
    }
    
    //View Body
    protected function addViewBodyContent(){
        $this->addHTML($this->form->getForm());
    }

    public function buildForm() {
        $this->addRelationRowWrap();
        
        $this->addSubmitBtn();
        
        $this->form->addField('linked_type', 'hidden', array(
            'value' => $this->linkedTypeRef,
        ));
        
        $this->form->addField('relation', 'hidden', array(
            'value' => $this->relation,
        ));
    }
    
    protected function addRelationRowWrap(){
        $this->form->addHTML('<div class="form_rows_group">');
            $this->form->addHTML('<div id="relation_rows">');
                $this->addRelationFormRows();
            $this->form->addHTML('</div>');

            $this->form->addHTML('<div class="wrap_btns">');
                $this->addAddRowBtn();
                $this->form->addHTML('<hr/>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }
    
    protected function addRelationFormRows(){
        $formWasSubmitted = $this->form->wasSubmitted();
        $seqCount = 0;
        $dbRelationships = $this->contact->getContactRelationshipsWithIdKey($this->linkedTypeRef, $this->relation);
        $mergedContactRelationships = $this->contact->getMergeContactRelationships($this->form, $this->relation, $dbRelationships);
        foreach ($mergedContactRelationships as $mergedContactRelationship) {
            if(!$formWasSubmitted){
                $mergedContactRelationship->setSeqNumber($seqCount);
                $seqCount++;
            }
            $formView = new ContactRelationshipFormView($this->form, $this->contact, $mergedContactRelationship, $this->linkedTypeRef);
            $formView->buildForm();
        }
    }
    

    protected function addAddRowBtn() {
        if (Permission::verifyByRef('link_contacts')) {
            $addURL = GI_URLUtils::buildURL(array(
                        'controller' => 'contact',
                        'action' => 'addRelationshipRow',
                        'pId' => $this->contact->getProperty('id'),
                        'relation' => $this->relation,
                            ), false, true);
            $this->form->addHTML('<span class="custom_btn add_form_row" data-add-to="relation_rows" data-add-type="' . $this->linkedTypeRef . '" data-add-url="' . $addURL . '">'.GI_StringUtils::getIcon('add').'<span class="btn_text">Add Link</span></span>');
        }
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<div>');
            $this->form->addHTML('<span class="submit_btn" tabindex="0" title="Save">'.GI_StringUtils::getIcon('check').'<span class="btn_text">Save</span></span>');
        $this->form->addHTML('</div>');
    }
}
