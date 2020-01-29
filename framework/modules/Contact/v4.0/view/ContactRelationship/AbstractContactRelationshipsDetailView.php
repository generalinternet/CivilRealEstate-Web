<?php
/**
 * Description of AbstractContactRelationshipsDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.3
 */
abstract class AbstractContactRelationshipsDetailView extends GI_View {
    
    protected $contact;
    protected $relationshipsByTypeRef;
    protected $linkedTypeRefs;
    protected $addLinkBtns = true;
    protected $addDeleteLinkBtns = true;
    
    public function __construct(AbstractContact $contact, $relationshipsByTypeRef, $linkedTypeRefs) {
        parent::__construct();
        $this->contact = $contact;
        $this->relationshipsByTypeRef = $relationshipsByTypeRef;
        $this->linkedTypeRefs = $linkedTypeRefs;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    public function setAddLinkBtns($addLinkBtns){
        $this->addLinkBtns = $addLinkBtns;
        return $this;
    }
    
    public function setAddDeleteLinkBtns($deleteLinkBtns){
        $this->addDeleteLinkBtns = $deleteLinkBtns;
        return $this;
    }

    public function buildView() {
        if (!empty($this->relationshipsByTypeRef)) {
            
            $columnCount = 0;
            $columnsOpen = false;
            
            foreach ($this->relationshipsByTypeRef as $typeRef => $relationshipModelArray) {
                /**Columizer**/
                if($columnCount == 0){
                    $columnsOpen = true;
                    $this->addHTML('<div class="columns contact_columns halves top_align">');
                }
                $columnCount++;
                /*************/
                
                $relation = $this->linkedTypeRefs[$typeRef];
                $contactModel = ContactFactory::buildNewModel($typeRef);
                if (!empty($contactModel)) {
                    $typeTitle = $contactModel->getTypeTitle();
                } else {
                    $typeTitle = 'Contact';
                }
                /**Columizer**/
                $this->addHTML('<div class="column">');
                /*************/
                
                $this->addHTML('<div class="right_btns">');
                    if($this->addLinkBtns && Permission::verifyByRef('link_contacts')){
                        $this->addHTML($contactModel->getLinkButtons($this->contact->getProperty('id'), $relation));
                    }
                $this->addHTML('</div>');
                $this->addHTML('<h2 class="content_group_title">')
                        ->addHTML($typeTitle . 's')
                        ->addHTML('</h2>');
                if (!empty($relationshipModelArray)) {
                    foreach ($relationshipModelArray as $relationshipModel) {
                        if ($relation === 'child') {
                            $contactId = $relationshipModel->getProperty('c_contact_id');
                        } else if ($relation === 'parent') {
                            $contactId = $relationshipModel->getProperty('p_contact_id');
                        }
                        if (!empty($contactId)) {
                            $contact = ContactFactory::getModelById($contactId);
                           $this->addHTML('<div class="column">');
                                $this->addHTML('<div class="content_block block_with_right_btns">');
                                $this->addHTML($contact->getSummaryView($relationshipModel)->getHTMLView());
                                
                                $this->addHTML('<div class="right_btns">');
                                    if ($relationshipModel->isDeleteable() && $this->addDeleteLinkBtns) {
                                        $deleteRelationURL = GI_URLUtils::buildURL(array(
                                            'controller'=>'contact',
                                            'action'=>'deleteRelationship',
                                            'id'=>$relationshipModel->getProperty('id'),
                                            'pId'=>$this->contact->getProperty('id'),
                                        ));
                                            $this->addHTML('<a href="'.$deleteRelationURL.'" title="Delete Relationship" class="custom_btn open_modal_form">'.GI_StringUtils::getIcon('trash').'</a>');
                                    }
    //                                        if ($relationshipModel->isEditable()) {
    //                                            $editRelationURL = GI_URLUtils::buildURL(array(
    //                                                'controller'=>'contact',
    //                                                'action'=>'editRelationship',
    //                                                'id'=>$relationshipModel->getProperty('id'),
    //                                                'pId'=>$this->contact->getProperty('id'),
    //                                            ));
    //                                            $this->addHTML('<a href="'.$editRelationURL.'" title="Edit Relationship" class="custom_btn open_modal_form" data-modal-class="medium_sized"><span class="icon_wrap"><span class="icon edit"></span></span></a>');
    //                                        }
                                    $this->addHTML('</div>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                        }
                    }
                } else {
                    $this->addHTML('<p class="no_model_message">There are no ' . $typeTitle . 's</p>');
                }
                
                /**Columizer**/
                $this->addHTML('</div>');
                if($columnCount == 2){
                    $columnsOpen = false;
                    $this->addHTML('</div>');
                    $columnCount = 0;
                }
                /*************/
            }
            
            /**Columizer**/
            if ($columnsOpen) {
                $this->addHTML('</div>');
            }
            /*************/
        }
    }

}
