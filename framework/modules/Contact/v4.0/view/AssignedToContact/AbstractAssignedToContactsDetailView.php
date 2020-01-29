<?php
/**
 * Description of AbstractAssignedToContactsDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.2
 */
abstract class AbstractAssignedToContactsDetailView extends GI_View {
    
    protected $assignedToContacts;
    protected $contact;

    
    public function __construct(AbstractContact $contact, $assignedToContacts = NULL) {
        parent::__construct();
        $this->contact = $contact;
        $this->assignedToContacts = $assignedToContacts;
        $this->buildView();
    }
    
   protected function openViewWrap(){
        $this->addHTML('<div class="columns halves top_align">');
            $this->addHTML('<div class="column">');
        return $this;
    }
    
    protected function closeViewWrap(){
            $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openInnerViewWrap(){
        $this->addHTML('<div class="content_block_wrap">');
        return $this;
    }
    
    protected function closeInnerViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();
        
        $this->addButtons();

        $this->addTitle();
        if (!empty($this->assignedToContacts)) {
            foreach ($this->assignedToContacts as $assignedToContact) {
                $userId = $assignedToContact->getProperty('user_id');
                if (!empty($userId)) {
                    $user = UserFactory::getModelById($userId);
                    $sourceContact = ContactFactory::getBySourceUserId($userId);
                    $this->openInnerViewWrap();
                    $this->addHTML('<div class="content_block block_with_right_btns">');
                    if (!empty($sourceContact)) {
                        $this->addHTML('<a href="' . $sourceContact->getViewURL() . '">' . $user->getFullName() . '</a>');
                    } else {
                        $this->addHTML($user->getFullName());
                    }
                        $this->addHTML('<div class="right_btns">');
                                        if ($assignedToContact->isDeleteable()) {
                                            $deleteAssignedURL = GI_URLUtils::buildURL(array(
                                                'controller' => 'contact',
                                                'action' => 'deleteAssignedToContact',
                                                'id' => $assignedToContact->getId(),
                                                'contactId' => $this->contact->getProperty('id'),
                                            ));
                                            $this->addHTML('<a href="'.$deleteAssignedURL.'" title="Delete Assigned to Contact" class="custom_btn open_modal_form">'.GI_StringUtils::getIcon('trash').'</a>');
                                        }
    //                                        if ($assignedToContact->isEditable()) {
    //                                            $editAssignedURL = GI_URLUtils::buildURL(array(
    //                                                'controller'=>'contact',
    //                                                'action'=>'editAssignedToContact',
    //                                                'id'=>$assignedToContact->getProperty('id'),
    //                                            ));
    //                                            $this->addHTML('<a href="'.$editAssignedURL.'" title="Edit Assigned to Contact" class="custom_btn open_modal_form" data-modal-class="medium_sized"><span class="icon_wrap"><span class="icon edit"></span></span></a>');
    //                                        }
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                    $this->closeInnerViewWrap();
                }
            }
        } else {
            $this->addHTML('<p class="no_model_message">No one is assigned</p>');
        }
        $this->closeViewWrap();
    }
    
    public function addButtons() {
        $assignToContactURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contact',
                    'action' => 'addAssignedToContact',
                    'contactId' => $this->contact->getId(),
        ));
        if (Permission::verifyByRef('assign_contacts')) {
            $this->addHTML('<div class="right_btns">');
            $this->addHTML('<a href="' . $assignToContactURL . '" title="Assign User to Contact" class="custom_btn open_modal_form" data-modal-class="medium_sized">'.GI_StringUtils::getIcon('add').'<span class="btn_text">Assign</span></a>');
            $this->addHTML('</div>');
        }
    }
    public function addTitle() {
        $this->addHTML('<h2 class="content_group_title">Assigned To Contact(s)</h2>');
    }
    
}
