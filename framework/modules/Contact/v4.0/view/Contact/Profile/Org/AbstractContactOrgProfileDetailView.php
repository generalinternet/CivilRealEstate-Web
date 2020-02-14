<?php

/**
 * Description of AbstractContactOrgProfileDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactOrgProfileDetailView extends AbstractContactProfileDetailView {

    /** @var AbstractContactOrg */
    protected $contact = NULL;
    protected $addPublicProfileSection = false;
    protected $addPaymentSection = false;

    public function __construct(\AbstractContact $contact) {
        parent::__construct($contact);
        $this->setListBarURL($contact->getProfileListBarURL());
        $contactCat = $contact->getContactCat();
        if (!empty($contactCat) && $contactCat->getUsesPublicProfile()) {
            $this->addPublicProfileSection = true;
        }
        if (!empty($contactCat) && $contactCat->getUsesPayment()) {
            $this->addPaymentSection = true;
        }
        $title = $this->contact->getDisplayName();
        if (Login::interfacePerspectiveRefEquals('admin')) {
            $statusHTML = $this->getSuspendedStatusHeaderHTML();
            $title .= $statusHTML;
        }

        $this->setWindowTitle($title);
    }

    protected function getSuspendedStatusHeaderHTML() {
        $html = '';
        if ($this->contact->isSuspendable()) {
            $status = $this->contact->getSuspendedStatus();
            $html .= '<div class="right_btns">';
            $html .= $status;
            $html .= '<div>';
        }
        return $html;
    }

    public function buildView() {
        if ($this->addQuickbooksBar) {
            $this->addQuickbooksBar();
        }
        parent::buildView();
    }

    protected function addViewBodyContent() {
        $this->addAdvancedSections();
    }

    protected function addAdvancedSections() {
        $this->addContactSectionAdvancedBlock();
        $this->addAdvancedSectionAdvancedBlock();
        $this->addPeopleSectionAdvancedBlock();
        if ($this->contact->getIsUserLinkedToThis(Login::getUser())) {
            $this->addMySettingsSectionAdvancedBlock();
        }
    }

    protected function addContactSectionAdvancedBlock($classNames = '') {
        $targetRef = 'contact';
        $isOpenOnLoad = false;
        if (!$this->hasOverlay || $this->curTab === $targetRef) {
            $isOpenOnLoad = true;
        }

        $btnOptionsArray = array(
            'type' => 'basic',
        );
        if ($this->contact->isEditable()) {
            $editAttrs = $this->contact->getEditProfileURLAttrs();
            $editAttrs['step'] = 10;
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
        
        $advClassNames = $classNames . ' contact_detail_advanced ' . $this->targetRefPrefix . $targetRef;
        $headerIcon = 'contact_details';
        $headerTitle = 'Contact';
        $isAddToSidebar = false;
        $view = new GenericMainWindowView();
        $view->setOnlyBodyContent(true);
        $this->addContactSectionContent($view);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar, NULL, NULL);
    }

    protected function addContactSectionContent(GI_View $view = NULL, $class = '') {
        if (is_null($view)) {
            $view = $this;
        }
        $view->addHTML('<div class="columns halves ' . $class . '">');
        $view->addHTML('<div class="column">');
        $view->addHTML('<h2 class="content_group_title">General</h2>');

        $view->addContentBlock($this->contact->getName(), 'Company');
        $view->addContentBlock($this->contact->getPrimaryIndividualName(), 'Name');

        $this->addContactInfoBlock($view);


        $view->addHTML('</div>');

        //Google Map
        $view->addHTML('<div class="column">');
        $this->addGoogleMapBlock($view);
        $view->addHTML('</div>');
        $view->addHTML('</div>');

    //    $this->addNotesBlock();
    }
    
    //TODO - REVISE
    protected function addNotesBlock(GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }
        $notes = $this->contact->getProperty('notes');
        if (!empty($notes)) {
            $view->addHTML('<div class="content_block_wrap">');
            $view->addContentBlock(GI_StringUtils::nl2brHTML($notes), 'Notes');
            $view->addHTML('</div>');
        }
    }

    //TODO - REVISE
    protected function addTagsBlock(GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }

        $tagListView = $this->contact->getTagListView(false);
        if (!empty($tagListView)) {
            $view->addHTML('<div class="content_block_wrap">');
            $view->addHTML($tagListView->getHTMLView());
            $view->addHTML('</div>');
        }
        //QnA tags
        if (dbConnection::isModuleInstalled('qna')) {
            $qnaTagListView = TagFactory::getTagListView($this->contact);
            if (!empty($qnaTagListView)) {
                $qnaTagListView->setListTitle('QnA Tags');
                $view->addHTML('<div class="content_block_wrap">');
                $view->addHTML($qnaTagListView->getHTMLView());
                $view->addHTML('</div>');
            }
        }
    }

    protected function addContactInfoBlock(GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }
        $contactInfoAllTypesArray = $this->contact->getContactInfoArray();
        foreach ($contactInfoAllTypesArray as $contactInfoArray) {
            foreach ($contactInfoArray as $contactInfo) {
                $contactInfoDetailView = $contactInfo->getDetailView();
                $detailContent = $contactInfoDetailView->getHTMLView();
                if (!empty($detailContent)) {
                    $view->addHTML('<div class="content_block_wrap">');
                    $view->addHTML($detailContent);
                    $view->addHTML('</div>');
                }
            }
        }
    }

    protected function addGoogleMapBlock(GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }
        $view->addHTML('<div class="google_map_wrap">');
        $view->addHTML('<h2 class="content_group_title">Google Map</h2>');
        $name = $this->contact->getRealName();
        $address = $this->contact->getAddress();
        $view->addHTML('<div id="google_map" class="org_map" data-title="' . $name . '" data-addr="' . $address . '"></div>');
        $view->addHTML('</div>');
    }

    protected function addAdvancedSectionAdvancedBlock($classNames = '') {
        $targetRef = 'advanced';
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

        $advClassNames = $classNames . ' contact_detail_advanced ' . $this->targetRefPrefix . $targetRef;
        $headerIcon = 'star';
        $headerTitle = 'Advanced';
        $isAddToSidebar = false;
        $view = new GenericMainWindowView();
        $view->setOnlyBodyContent(true);
        $this->addAdvancedSectionContent($view);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar, NULL, NULL);
    }
    
    protected function addAdvancedSectionContent(GI_View $view = NULL, $class = '') {
        $defaultCurrencyModel = $this->contact->getDefaultCurrency();
        if (!empty($defaultCurrencyModel)) {
            $currency = $defaultCurrencyModel->getProperty('name');
        } else {
            $currency = 'Not Set';
        }
        $view->addContentBlock($currency, 'Currency');

        $subcategoryTagModel = $this->contact->getSubCategoryTag();
        if (!empty($subcategoryTagModel)) {
            $tag = $subcategoryTagModel->getProperty('title');
        } else {
            $tag = '--';
        }
        $view->addContentBlock($tag, 'Subcategory');
    }

    protected function addMySettingsSectionAdvancedBlock($classNames = '') {
        if (!$this->contact->getIsUserLinkedToThis(Login::getUser())) {
            return;
        }
        $targetRef = 'my_settings';
        $isOpenOnLoad = false;
        if (!$this->hasOverlay || $this->curTab === $targetRef) {
            $isOpenOnLoad = true;
        }
        $btnOptionsArray = array(
            'type' => 'basic',
        );
        if ($this->contact->isEditable()) {
            $editAttrs = $this->contact->getEditProfileURLAttrs();
            $editAttrs['step'] = 40;
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
        $advClassNames = $classNames . ' contact_detail_advanced ' . $this->targetRefPrefix . $targetRef;
        $headerIcon = 'account';
        $headerTitle = 'My Settings';
        $isAddToSidebar = false;
//        $view = new GenericMainWindowView();
//        $view->setOnlyBodyContent(true);
//        $this->addContactSectionContent($view);
        $contactInd = ContactFactory::getIndividualByParentOrgAndUser($this->contact, Login::getUser());
        if (empty($contactInd)) {
            return;
        }
        $view = $contactInd->getProfileMySettingsDetailView();
        if (empty($view)) {
            return;
        }
        $view->setOnlyBodyContent(true);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar, NULL, NULL);
    }

    protected function addPeopleSectionAdvancedBlock($classNames = '') {
        $targetRef = 'people';
        $isOpenOnLoad = false;
        if (!$this->hasOverlay || $this->curTab === $targetRef) {
            $isOpenOnLoad = true;
        }

        $btnOptionsArray = array(
            'type' => 'basic',
        );
        if ($this->contact->isAddable()) {
            $addAttrs = $this->contact->getAddPersonURLAttrs();
            $linkURL = GI_URLUtils::buildURL($addAttrs);
            $linkType = 'add';
            $linkTitle = '';
            $linkIcon = 'plus';
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
        $advClassNames = $classNames . ' contact_detail_advanced ' . $this->targetRefPrefix . $targetRef;
        $headerIcon = 'contacts';
        $headerTitle = 'People';
        $isAddToSidebar = false;
        $view = new GenericMainWindowView();
        $view->setOnlyBodyContent(true);
        $this->addPeopleSectionContent($view);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar, NULL, NULL);
    }
    
    protected function addPeopleSectionContent(GI_View $view) {
        if (empty($view)) {
            $view = $this;
        }
        $childContactInds = $this->contact->getChildContactInds();
        if (!empty($childContactInds)) {
            foreach ($childContactInds as $contactInd) {
                $tempView = $contactInd->getProfileDetailView();
                $tempView->setAddOuterWrap(false);
                $tempView->setOnlyBodyContent(true);
                $tempView->setParentContactOrg($this->contact);
                $view->addHTML($tempView->getHTMLView());
            }
        }
    }

    protected function addQuickbooksBar() {
        if ($this->contact->isQuickbooksExportable()) {
            $qbBar = QBConnection::getQuickbooksBarView();
            $connected = false;
            if (!empty(QBConnection::getInstance())) {
                $connected = true;
            }
            if ($qbBar) {
                $qbBar->setExportableModel($this->contact);
                $contactQB = $this->contact->getContactQB();
                $importRequired = false;
                $importURL = NULL;
                $exportRequired = false;
                $exportURL = NULL;
                if (!empty($contactQB)) {
                    if (!empty($contactQB->getProperty('import_required'))) {
                        $importRequired = true;
                    }
                    if (!empty($contactQB->getProperty('export_required'))) {
                        $exportRequired = true;
                    }
                }
                if ($connected && !empty($contactQB) && Permission::verifyByRef('unlink_contacts_from_qb_contacts')) {
                    $unlinkURL = GI_URLUtils::buildURL(array(
                        'controller'=>'contact',
                        'action'=>'unlinkQBContact',
                        'id'=>$this->contact->getId()
                    ));
                    $unlinkBtnHTML = '<a href="' . $unlinkURL . '" class="qb_btn open_modal_form" title="Unlink from Quickbooks contact">Unlink</a>';
                    $qbBar->setUnLinkBtnHTML($unlinkBtnHTML);
                }
                if ($connected && empty($contactQB) && Permission::verifyByRef('link_contacts_to_qb_contacts')) {
                    $type = NULL;
                    if ($this->contact->isClient()) {
                        $type = 'customer';
                    } else if ($this->contact->isVendor() || $this->contact->isShipper()) {
                        $type = 'supplier';
                    }
                    if (!empty($type)) {
                        $linkURL = GI_URLUtils::buildURL(array(
                                    'controller' => 'contact',
                                    'action' => 'linkQBContact',
                                    'id' => $this->contact->getId(),
                                    'type' => $type,
                        ));
                        $linkBtnHTML = '<a href="' . $linkURL . '" class="qb_btn open_modal_form" title="Link to an existing QB Contact">Link to Existing</a>';
                        $qbBar->setLinkBtnHTML($linkBtnHTML);
                    }
                }
                if ($connected && $this->contact->isQuickbooksImportable() && Permission::verifyByRef('import_contacts_from_quickbooks')) {
                    $importURL = GI_URLUtils::buildURL(array(
                                'controller' => 'contact',
                                'action' => 'importFromQB',
                                'id' => $this->contact->getId(),
                    ));
                    $importRequiredClass = '';
                    if ($importRequired) {
                        $importRequiredClass = ' required';
                    }
                    $importButtonHTML = '<a href="' . $importURL . '" class="qb_btn load_in_element' . $importRequiredClass . '" title="Import from Quickbooks" data-load-in-id="qb_info_section" data-hide-btn="0">Import</a>';
                    $qbBar->setImportBtnHTML($importButtonHTML);
                }
                if ($connected && Permission::verifyByRef('export_contacts_to_quickbooks')) {
                    $exportURL = GI_URLUtils::buildURL(array(
                                'controller' => 'contact',
                                'action' => 'exportToQB',
                                'id' => $this->contact->getId()
                    ));
                    $exportRequiredClass = '';
                    if ($exportRequired) {
                        $exportRequiredClass = ' required';
                    }
                    $exportTitle = 'Export to Quickbooks';
                    $exportBtnText = 'Export';
                    if (empty($contactQB)) {
                        $exportBtnText .= ' as New';
                        $exportTitle .= ' as a new contact';
                    }
                    $exportButtonHTML = '<a href="' . $exportURL . '" class="qb_btn load_in_element' . $exportRequiredClass . '" title="'.$exportTitle.'" data-load-in-id="qb_info_section" data-hide-btn="0">'.$exportBtnText.'</a>';
                    $qbBar->setExportBtnHTML($exportButtonHTML);
                }

                

                if ($connected) {
                    $zipped = 1;
                    $infoSectionClass = 'collapsed';
                    $infoSectionURL = '';
                    if (($importRequired && !$exportRequired) && !empty($importURL)) {
                        $zipped = 0;
                        $infoSectionClass .= ' ajaxed_contents auto_load';
                        $infoSectionURL = 'data-url="' . $importURL . '"';
                    } else if ((!$importRequired && $exportRequired) && !empty($exportURL)) {
                        $zipped = 0;
                        $infoSectionClass .= ' ajaxed_contents auto_load';
                        $infoSectionURL = 'data-url="' . $exportURL . '"';
                    }
                    $html = '<div id="qb_info_section" class="' . $infoSectionClass . '" data-zipped="' . $zipped . '" ' . $infoSectionURL . '>';

                    if (!empty($contactQB)) {
                        if (empty($infoSectionURL)) {
                            $contactQBDetailView = $contactQB->getDetailView();
                            if (!empty($contactQBDetailView)) {

                                $contactQBDetailViewHTML = $contactQBDetailView->getHTMLView();
                                $html .= $contactQBDetailViewHTML;
                            }
                        }
                    }
                    $html .= '</div>';
                    $qbBar->setFooterHTML($html);
                }
                $this->addHTML($qbBar->getHTMLView());
            }
        }
    }

    protected function addOrdersSection($typeRef, GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }
        if (dbConnection::isModuleInstalled('order')) {
            $orderIndexView = OrderFactory::getContactOrderIndex($this->contact, $typeRef);
            if (!empty($orderIndexView)) {
                $orderIndexView->setAddWrap(false);
                $orderIndexView->setAddButtons(false);
                $orderIndexView->setAddHeader(false);
                $view->addHTML('<div class="contact_order_table_wrap ajax_link_wrap inner_view_wrap">');
                $view->addHTML($orderIndexView->getHTMLView());
                $view->addHTML('</div>');
            }
        }
    }

    protected function addPaymentsSectionAdvancedBlock($classNames = '') {
        if (!$this->addPaymentSection) {
            return;
        }
        $this->addJS("https://js.stripe.com/v3/");
        $this->addJS('framework/core/' . FRMWK_CORE_VER . '/resources/js/payments/stripe_custom.js');
        $this->addCSS('https://cdnjs.cloudflare.com/ajax/libs/paymentfont/1.1.2/css/paymentfont.min.css');

        $targetRef = 'payments';
        $isOpenOnLoad = false;
        if (!$this->hasOverlay || $this->curTab === $targetRef) {
            $isOpenOnLoad = true;
        }
        $btnOptionsArray = array(
            'type' => 'basic',
        );
        $advClassNames = $classNames . ' contact_detail_advanced ' . $this->targetRefPrefix . $targetRef;
        $headerIcon = 'cards';
        $headerTitle = 'Payments';
        $isAddToSidebar = false;
        $view = $this->contact->getPaymentsDetailView();
        if (empty($view)) {
            return;
        }
        $view->setOnlyBodyContent(true);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar, NULL, NULL);
    }

    protected function addAccountStatusSectionAdvancedBlock($classNames = '') {
        if (!$this->contact->isSuspendable() || !Permission::verifyByRef('view_suspensions')) {
            return;
        }
        $targetRef = 'system_status';
        $isOpenOnLoad = false;
        if (!$this->hasOverlay || $this->curTab === $targetRef) {
            $isOpenOnLoad = true;
        }
        $btnOptionsArray = array(
            'type' => 'basic',
        );
        $exampleSuspension = SuspensionFactory::buildNewModel('suspension');
        $exampleSuspension->setContact($this->contact);
        if ($exampleSuspension->isAddable()) {
            $linkURL = $exampleSuspension->getAddURL();
            $linkType = '';
            $linkTitle = 'Suspend';
            $linkIcon = 'locked';
            $btnOptionsArray[] = array(
                'type' => $linkType,
                'title' => $linkTitle,
                'icon' => $linkIcon,
                'link' => $linkURL,
                'class_names' => 'open_modal_form',
              //  'class_names' => 'ajax_link',
            );
        }
        if ($this->contact->isSuspended() && Permission::verifyByRef('delete_suspensions')) {
            //TODO - add remove all suspensions button
        } 
        $advClassNames = $classNames . ' contact_detail_advanced ' . $this->targetRefPrefix . $targetRef;
        $headerIcon = 'padlock';
        $headerTitle = 'Account Status';
        $isAddToSidebar = false;
        $view = $this->contact->getSuspensionSummaryView();
        if (empty($view)) {
            return;
        }
        $view->setOnlyBodyContent(true);
        //TODO - change this to ajax load
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar, NULL, NULL);
    }
    
}
