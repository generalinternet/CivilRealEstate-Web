<?php
/**
 * Description of AbstractContactIndProfileDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactIndProfileDetailView extends AbstractContactProfileDetailView {
    
    protected $parentContactOrg;
    
    public function setParentContactOrg(AbstractContactOrg $parentContactOrg) {
        $this->parentContactOrg = $parentContactOrg;
    }
    
    protected function addViewBodyContent() {
        $this->buildRow();
    }

    protected function buildRow() {
        $primaryIndividual = false;
        if (!empty($this->parentContactOrg) && $this->parentContactOrg->getProperty('contact_org.primary_individual_id') === $this->contact->getId()) {
            $primaryIndividual = true;
        }
        $user = $this->contact->getUser();

        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML($this->contact->getName());
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addEmailAddresses($primaryIndividual);
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addPhoneNumbers($primaryIndividual);      
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        //Temp - replace w/ symbol
        $systemAccess = 'No System Access';
        if (!empty($user) && !empty($user->getId())) {
            if (!empty($user->getProperty('confirm_code_sent_date')) && empty($user->getProperty('confirmed'))) {
                $systemAccess = 'Invited';
            } else if (!empty($user->getProperty('pass')) && !empty($user->getProperty('salt'))) {
                $systemAccess = 'Has Access';
            }
        }
        $this->addHTML($systemAccess);
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        //Login Email
        $loginEmail = $this->contact->getLoginEmail();
        if (!empty($loginEmail)) {
            $loginEmail .= ' (Login Email)';
        } else {
            $loginEmail = '--';
        }
        $this->addHTML($loginEmail);
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col sml">');
        if (!empty($user) && (Login::getUserId() !== $user->getId()) && !empty($this->parentContactOrg)) {
            $confirmEmailURL = GI_URLUtils::buildURL(array(
                        'controller' => 'contactprofile',
                        'action' => 'sendConfirmationEmail',
                        'id' => $this->contact->getId(),
                        'pId'=>$this->parentContactOrg->getId(),
            ));
            $this->addHTML('<a href="' . $confirmEmailURL . '" class="custom_btn open_modal_form" data-modal-class="medium_sized" title="Send Invitation Email">'.GI_StringUtils::getIcon('email').'</a>');
        }
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col sml">');
        if ($this->contact->isEditable()) {
            $editURL = GI_URLUtils::buildURL(array(
                        'controller' => 'contactprofile',
                        'action' => 'edit',
                        'id' => $this->contact->getId(),
            )); 
            $this->addHTML('<a href="' . $editURL . '" class="custom_btn ajax_link" title="Edit '.$this->contact->getName().'">'.GI_StringUtils::getIcon('pencil').'</a>');
        }
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addEmailAddresses($primaryIndividual = false) {
        if ($primaryIndividual) {
            $emailAddressArray = $this->parentContactOrg->getContactInfoArray('email_address');
        } else {
            $emailAddressArray = $this->contact->getContactInfoArray('email_address');
        }

        if (!empty($emailAddressArray)) {
            foreach ($emailAddressArray as $typeRef => $modelArray) {
                if (!empty($modelArray)) {
                    foreach ($modelArray as $model) {
                        $value = $model->getProperty('contact_info_email_addr.email_address');
                        $this->addHTML($value . ' (' . $model->getTypeTitle() . ')<br/>');
                    }
                }
            }
        }
    }

    protected function addPhoneNumbers($primaryIndividual = false) {
        if ($primaryIndividual) {
            $phoneNumberArray = $this->parentContactOrg->getContactInfoArray('phone_num');
        } else {
            $phoneNumberArray = $this->contact->getContactInfoArray('phone_num');
        }

        if (!empty($phoneNumberArray)) {
            foreach ($phoneNumberArray as $typeRef => $modelArray) {
                if (!empty($modelArray)) {
                    foreach ($modelArray as $model) {
                        $value = $model->getProperty('contact_info_phone_num.phone');
                        $this->addHTML($value . ' (' . $model->getTypeTitle() . ')<br/>');
                    }
                }
            }
        }
    }

}
