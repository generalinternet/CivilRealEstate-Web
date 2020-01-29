<?php

/**
 * Description of AbstractContactApplication
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactApplication extends GI_Model {

    /** @var AbstractContactOrg */
    protected $contactOrg;

    /** @var AbstractUser */
    protected $user;

    /** @var AbstractContactApplicationStatus */
    protected $currentStatus = NULL;

    /** @var AbstractContactApplicationStatus */
    protected $initialStatus = NULL;

    /** @var AbstractSubscription */
    protected $subscription = NULL;
    protected $defaultContactCatTypeRef = 'client';

    public function getUser() {
        if (empty($this->user)) {
            $this->user = UserFactory::getModelById($this->getProperty('user_id'));
        }
        return $this->user;
    }

    public function getContactOrg() {
        if (empty($this->contactOrg)) {
            $contactOrg = ContactFactory::getModelById($this->getProperty('contact_org_id'));
            if (!empty($contactOrg)) {
                $this->contactOrg = $contactOrg;
                return $this->contactOrg;
            }
            $user = $this->getUser();
            if (!empty($user) && !empty($user->getId())) {
                $search = ContactFactory::search();
                $contactTableName = $search->prefixTableName('contact');
                $search->filterByTypeRef('org');
                $search->join('contact_relationship', 'p_contact_id', $contactTableName, 'id', 'REL')
                        ->join('contact', 'id', 'REL', 'c_contact_id', 'CON');
                $search->filter('CON.source_user_id', $user->getId());
                $results = $search->select();
                if (!empty($results)) {
                    $this->contactOrg = $results[0];
                }
            }
        }
        return $this->contactOrg;
    }
    
    public function setContactOrg(AbstractContactOrg $contactOrg) {
        $this->contactOrg = $contactOrg;
        $this->setProperty('contact_org_id', $contactOrg->getId());
    }

    public function getFormView(GI_Form $form) {
        return new ContactApplicationFormView($form, $this);
    }

    public function getFormId() {
        return 'application';
    }

    public function getSubscription() {
        if (empty($this->subscription)) {
            $this->subscription = SubscriptionFactory::getModelById($this->getProperty('subscription_id'));
        }
        return $this->subscription;
    }
    
    public function getDefaultContactCatTypeRef() {
        return $this->defaultContactCatTypeRef;
    }
    
    public function setDefaultContactCatTypeRef($typeRef) {
        $this->defaultContactCatTypeRef = $typeRef;
    }

    public function handleFormSubmission(GI_Form $form, ContactApplicationStatus $status) {
        if ($form->wasSubmitted() && $form->validate()) {
            if (!$this->handleFormSubmissionByStatus($form, $status)) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    protected function handleFormSubmissionByStatus(GI_Form $form, ContactApplicationStatus $status) {
        
        return true;
    }
    
    public function getCurrentStatus() {
        if (empty($this->currentStatus)) {
            if (empty($this->getProperty('contact_app_status_id'))) {
                $this->setInitialStatus();
            } else {
                $this->currentStatus = ContactApplicationStatusFactory::getModelById($this->getProperty('contact_app_status_id'));
            }
        }
        return $this->currentStatus;
    }
    
    public function setInitialStatus(ContactApplicationStatus $status = NULL) {
        if (empty($this->getProperty('contact_app_status_id'))) {
            if (empty($status)) {
//                $search = ContactApplicationStatusFactory::search();
//                $search->filterByTypeRef($this->getTypeRef())
//                        ->filter('rank', 0);
//                $results = $search->select();
//                if (empty($results)) {
//                    return false;
//                }
//                $status = $results[0];
                $status = $this->getInitialStatus();
            }
            $this->setProperty('contact_app_status_id', $status->getId());
            $this->currentStatus = $status;
            $this->initialStatus = $status;
        }
        return true;
    }

    public function getInitialStatus() {
        if (empty($this->initialStatus)) {
            $search = ContactApplicationStatusFactory::search();
            $search->filterByTypeRef($this->getTypeRef())
                    ->filter('rank', 0);
            $results = $search->select();
            if (empty($results)) {
                return false;
            }
            $status = $results[0];
            $this->initialStatus = $status;
        }
        return $this->initialStatus;
    }

    protected function getSubscriptionTypeRef() {
        $contactOrg = $this->getContactOrg();
        if (!empty($contactOrg)) {
            $paymentProcessor = $contactOrg->getPaymentProcessor();
            if (!empty($paymentProcessor)) {
                return $paymentProcessor->getSubscriptionTypeRef();
            }
        }
        return 'subscription';
    }
    
    public function getSubscriptionOptions() {
        $options = array();
        $search = SubscriptionFactory::search();
        $search->filterByTypeRef($this->getSubscriptionTypeRef());
        $search->orderBy('id', 'ASC');
        $results = $search->select();
        if (!empty($results)) {
            foreach ($results as $subscription) {
                $options[$subscription->getId()] = $subscription;
            }
        }
        return $options;
    }
    
    public function getCompletedRedirectURLAttrs() {
        return array(
            'controller'=>'contact',
            'action'=>'view',
            'id'=>$this->getProperty('contact_org_id'),
        );
    }
    
    public function getCompletedRedirectURL() {
        return GI_URLUtils::buildURL($this->getCompletedRedirectURLAttrs());
    }

    protected function updateStatus() {
        $currentStatus = $this->getCurrentStatus();
        $nextStatus = ContactApplicationStatusFactory::getNextStatusModelByApplication($this);
        if (!empty($nextStatus) && !empty($currentStatus) && empty($currentStatus->getProperty('hold')) && $currentStatus->getId() !== $nextStatus->getId() && $nextStatus->getProperty('rank') >= $currentStatus->getProperty('rank')) {
            $this->setProperty('contact_app_status_id', $nextStatus->getId());
        }
    }

}
