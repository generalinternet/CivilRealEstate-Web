<?php

/**
 * Description of AbstractContactOrgVendorProfileDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactOrgVendorProfileDetailView extends AbstractContactOrgProfileDetailView {

    protected $addQuickbooksBar = true;
    protected $addPublicProfileSection = false;

    public function __construct(\AbstractContact $contact) {
        parent::__construct($contact);
        $contactCat = $contact->getContactCat();
        if (!empty($contactCat) && $contactCat->getUsesPublicProfile()) {
            $this->addPublicProfileSection = true;
        }
    }

    protected function addAdvancedSections() {
        $this->addContactSectionAdvancedBlock();
        $this->addAdvancedSectionAdvancedBlock();
        $this->addPeopleSectionAdvancedBlock();
        if ($this->addPublicProfileSection) {
            $this->addPublicProfileSectionAdvancedBlock();
        }
        if ($this->contact->getIsUserLinkedToThis(Login::getUser())) {
            $this->addMySettingsSectionAdvancedBlock();
        }
        $user = Login::getUser();
        if (!empty($user)) {
            $interfacePerspective = $user->getCurrentInterfacePerspective();
            if (!empty($interfacePerspective) && $interfacePerspective->getProperty('ref') === 'admin') {
                $this->addAdminAdvancedSections();
            }
        }
    }

    protected function addAdminAdvancedSections() {
        if (dbConnection::isModuleInstalled('order')) {
            $this->addPurchaseOrdersAdvancedBlock();
        }
    }

    protected function addPurchaseOrdersAdvancedBlock() {
        $isOpenOnLoad = false;
        if ($this->curTab == 'purchase_orders') {
            $isOpenOnLoad = true;
        }
        $poType = 'purchase';
        $targetRef = 'purchase_orders';
        $headerIcon = 'purchase_orders';
        $headerTitle = 'Purchase Orders';
        $classNames = $this->targetRefPrefix . $targetRef;
        $btnOptionsArray = array();
        $sampleOrder = OrderFactory::buildNewModel($poType);
        if ($sampleOrder->isAddable()) {
            $linkURL = GI_URLUtils::buildURL(array(
                        'controller' => 'order',
                        'action' => 'add',
                        'type' => $poType,
                        'contactId' => $this->contact->getId(),
            ));
            $linkType = 'add';
            $linkTitle = '';
            $linkIcon = 'plus';
            $btnOptionsArray[] = array(
                'type' => $linkType,
                'title' => $linkTitle,
                'icon' => $linkIcon,
                'link' => $linkURL,
            );
        }
        $view = new GenericMainWindowView();
        $this->addOrdersSection($poType, $view);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $classNames, $headerIcon, false);
    }

}

