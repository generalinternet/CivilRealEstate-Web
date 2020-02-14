<?php

/**
 * Description of AbstractContactOrgClientProfileDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactOrgClientProfileDetailView extends AbstractContactOrgProfileDetailView {

    protected $addQuickbooksBar = true;
    
    public function __construct(\AbstractContact $contact) {
        parent::__construct($contact);
    }

    protected function addAdvancedSections() {
        $this->addContactSectionAdvancedBlock();
        $this->addAdvancedSectionAdvancedBlock();
        $this->addAccountStatusSectionAdvancedBlock();
        $this->addPeopleSectionAdvancedBlock();
        $this->addPublicProfileSectionAdvancedBlock();
        $this->addMySettingsSectionAdvancedBlock();
        $this->addPaymentsSectionAdvancedBlock();
        $this->addAdminAdvancedSections();
    }

    protected function addAdminAdvancedSections() {
        $currentInterfacePerspectiveRef = Login::getCurrentInterfacePerspectiveRef();
        if (empty($currentInterfacePerspectiveRef) || $currentInterfacePerspectiveRef !== 'admin') {
            return;
        }
        $this->addEventsAdvancedBlock();
        if (dbConnection::isModuleInstalled('inventory')) {
            $this->addDiscountsAdvancedBlock();
        }
        if (dbConnection::isModuleInstalled('order')) {
            $this->addSalesOrdersAdvancedBlock();
        }
    }

    protected function addPublicProfileSectionAdvancedBlock($classNames = '') {
        if (!$this->addPublicProfileSection) {
            return;
        }
        $targetRef = 'public_profile';
        $isOpenOnLoad = false;
        if (!$this->hasOverlay || $this->curTab === $targetRef) {
            $isOpenOnLoad = true;
        }

        $btnOptionsArray = array(
            'type' => 'basic',
        );
        if ($this->contact->isEditable()) {
            $editAttrs = $this->contact->getEditProfileURLAttrs();
            $editAttrs['step'] = 30;
            $linkURL = GI_URLUtils::buildURL($editAttrs);
            $linkType = 'edit';
            $linkTitle = '';
            $linkIcon = 'pencil';
            $btnOptionsArray[] = array(
                'type' => $linkType,
                'title' => $linkTitle,
                'icon' => $linkIcon,
                'link' => $linkURL,
                // 'class_names' => 'open_modal_form',
            );
        }
        $advClassNames = $classNames . ' contact_detail_advanced ' . $this->targetRefPrefix . $targetRef;
        $headerIcon = 'megaphone';
        $headerTitle = 'Public Profile';
        $isAddToSidebar = false;
        $view = $this->contact->getPublicProfileDetailView();
        if (!empty($view)) {
            $view->setOnlyBodyContent(true);
            $html = $view->getHTMLView();
        } else {
            $html = '';
        }
        $this->addAdvancedBlock($headerTitle, $html, $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar, NULL, NULL);
    }

    protected function addEventsAdvancedBlock() {
        $isOpenOnLoad = false;
        if ($this->curTab == 'events') {
            $isOpenOnLoad = true;
        }
        $btnOptionsArray = array();
        if (!empty($this->contact) && $this->contact->isEventAddable()) {
            $addEventURL = $this->contact->getAddEventURL();
            $btnOptionsArray[] = array(
                'type' => 'add',
                'link' => $addEventURL,
                'title' => '',
                'icon' => 'plus',
                'class_names' => 'open_modal_form',
                'other_data' => 'data-modal-class="medium_sized"',
            );
        }
//        
        $btnOptionsArray[] = array('type' => 'details');

        $targetRef = 'events';
        $advClassNames = $this->targetRefPrefix . $targetRef;
        $headerIcon = 'event';
        $headerTitle = 'Contact History';
        $isAddToSidebar = false;
        $view = $this->contact->getEventListView();
        $view->setShowBtns(false);
        $view->setAddTitle(false);
        $view->setContact($this->contact);
        $view->setAddWrap(false);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar);
    }

    protected function addDiscountsAdvancedBlock() {
        $isOpenOnLoad = false;
        if ($this->curTab == 'discounts') {
            $isOpenOnLoad = true;
        }
        $targetRef = 'discounts';
        $advClassNames = $this->targetRefPrefix . $targetRef;
        $headerIcon = 'discount';
        $headerTitle = 'Discounts';
        $btnOptionsArray = array();
        $linkURL = GI_URLUtils::buildURL(array(
                    'controller' => 'inventory',
                    'action' => 'addDiscount',
                    'type' => 'discount',
                    'contactId' => $this->contact->getId(),
        ));
        $btnOptionsArray[] = array(
            'type' => 'add',
            'title' => '',
            'icon' => 'plus',
            'link' => $linkURL,
            'class_names' => 'open_modal_form',
        );

        $discountIndexView = InvDiscountFactory::getContactDiscountIndex($this->contact);
        $discountIndexView->setAddWrap(false);
        $discountIndexView->setAddButtons(false);
        $discountIndexView->setAddTitle(false);
        $isAddToSidebar = true;
        $this->addAdvancedBlock($headerTitle, $discountIndexView->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar);
    }

    protected function addSalesOrdersAdvancedBlock() {
        $isOpenOnLoad = false;
        if ($this->curTab == 'sales_orders') {
            $isOpenOnLoad = true;
        }

        $targetRef = 'sales_orders';
        $headerIcon = 'sales_orders';
        $headerTitle = 'Sales Orders';
        $classNames = $this->targetRefPrefix . $targetRef;
        $btnOptionsArray = array();
        $sampleOrder = OrderFactory::buildNewModel('sales');
        if ($sampleOrder->isAddable()) {
            $linkURL = GI_URLUtils::buildURL(array(
                        'controller' => 'order',
                        'action' => 'add',
                        'type' => 'sales',
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
        $this->addOrdersSection('sales', $view);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $classNames, $headerIcon, false);
    }
    
    protected function addTagSectionAdvancedBlock(){
        $targetRef = 'tags';
        $isOpenOnLoad = false;
        if (!$this->hasOverlay || $this->curTab === $targetRef) {
            $isOpenOnLoad = true;
        }

        $btnOptionsArray = array(
            'type' => 'basic',
        );
        if ($this->contact->isEditable()) {
            $editAttrs = $this->contact->getEditProfileURLAttrs();
            $editAttrs['step'] = 20;
            $linkURL = GI_URLUtils::buildURL($editAttrs);
            $linkType = 'edit';
            $linkTitle = '';
            $linkIcon = 'pencil';
            $btnOptionInfo = array(
                'type' => $linkType,
                'title' => $linkTitle,
                'icon' => $linkIcon,
                'link' => $linkURL
            );
            if (Login::interfacePerspectiveRefEquals('admin')) {
                $btnOptionInfo['class_names'] = 'ajax_link';
            }
            $btnOptionsArray[] = $btnOptionInfo;
        }

        $classNames = $this->targetRefPrefix . $targetRef;
        $advClassNames = $classNames . ' contact_detail_advanced ' . $this->targetRefPrefix . $targetRef;
        $headerIcon = 'check';
        $headerTitle = 'Categories';
        $isAddToSidebar = false;
        $view = new GenericMainWindowView();
        $view->setOnlyBodyContent(true);
        $this->addTagSectionContent($view);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar, NULL, NULL);
    }

}
