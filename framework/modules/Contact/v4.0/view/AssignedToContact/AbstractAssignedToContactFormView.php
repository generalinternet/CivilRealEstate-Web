<?php
/**
 * Description of AbstractAssignedToContactFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractAssignedToContactFormView extends GI_View {
    
    /** @var GI_Form */
    protected $form;
    /* @var AbstractAssignedToContact */
    protected $assignedToContact;
    /* @var AbstractContact */
    protected $contact;
    /* @var AbstractUser */
    protected $user;
    
    public function __construct(GI_Form $form, AbstractAssignedToContact $assignedToContact = NULL, AbstractContact $contact = NULL, AbstractUser $user = NULL) {
        parent::__construct();
        $this->form = $form;
        $this->assignedToContact = $assignedToContact;
        $this->contact = $contact;
        $this->user = $user;
        $this->buildForm();
    }

    public function buildForm() {
        $this->form->addHTML('<h2 class="main_head">Assign User to Contact</h2>');
        
        $this->form->addHTML('<div class="columns halves top_align">');
            $this->form->addHTML('<div class="column">');
                $this->form->addField('id', 'hidden', array(
                    'value' => $this->assignedToContact->getId(),
                ));
                $this->addContactAutoCompField();
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="column">');
                $this->addUserAutoCompField();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('<span class="submit_btn" tabindex="0" data-form-id="' . $this->form->getFormId() . '">Submit</span>');
    }
    
    protected function addContactAutoCompField(){
        $autoCompURLProps = array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'ajax' => 1
        );
        $contactId = NULL;
        $contactTypeTitle = 'Contact';
        if(!empty($this->contact)){
            $autoCompURLProps['type'] = $this->contact->getTypeRef();
            $contactId = $this->contact->getId();
            $contactTypeTitle = $this->contact->getTypeTitle();
        }
        $contactAutoCompURL = GI_URLUtils::buildURL($autoCompURLProps);
        if (empty($contactId)) {
            $locked = false;
        } else {
            $locked = true;
        }
        $this->form->addField('contact_id', 'autocomplete', array(
            'displayName' => $contactTypeTitle,
            'placeHolder' => 'start typing...',
            'autocompURL' => $contactAutoCompURL,
            'value' => $contactId,
            'required' => true,
            'readOnly' => $locked,
        ));
    }
    
    protected function addUserAutoCompField(){
        $autoCompURLProps = array(
            'controller' => 'user',
            'action' => 'autocompUser',
            'ajax' => 1
        );
        $userId = NULL;
        $userTypeTitle = 'User';
        if(!empty($this->user)){
            $autoCompURLProps['type'] = $this->user->getTypeRef();
            $userId = $this->user->getId();
            $userTypeTitle = $this->user->getTypeTitle();
        }
        $userAutoCompURL = GI_URLUtils::buildURL($autoCompURLProps);
        if (empty($userId)) {
            $locked = false;
        } else {
            $locked = true;
        }
        $this->form->addField('user_id', 'autocomplete', array(
            'displayName' => $userTypeTitle,
            'placeHolder' => 'start typing...',
            'autocompURL' => $userAutoCompURL,
            'value' => $userId,
            'required' => true,
            'readOnly' => $locked,
        ));
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
}

