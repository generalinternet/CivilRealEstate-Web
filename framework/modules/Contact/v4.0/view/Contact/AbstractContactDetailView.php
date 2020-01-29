<?php
/**
 * Description of AbstractContactDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactDetailView extends SidebarView {
    
    /** @var Contact */
    protected $contact;
    protected $addButtons = true;
    protected $addHeader = true;
    protected $addContactInfoAndTags = true;
    protected $addLinkedContacts = true;
    protected $addAssignedToContacts = true;
    protected $addFiles = true;
    protected $addCategories = true;
    protected $addContactEvents = true;
    protected $addInterestRates = false;
    protected $addInternal = true;
    protected $addDefaultCurrency = true;
    protected $addDiscounts = false;
    protected $addSalesOrders = true;
    protected $addPurchaseOrders = true;
    protected $addUserInfo = true;
    protected $addInfoTab = true;
    protected $addConnections = true;
    protected $addLabourRates = true;
    protected $addQuickbooksBar = true;
    protected $sidebarTitle = 'Contacts';
    
    public function __construct(AbstractContact $contact) {
        parent::__construct();
        $this->contact = $contact;
        $this->addSiteTitle('Contacts');
        $typeTitle = $this->contact->getViewTitle();
        if(!empty($this->contact->getTypeRef()) && $typeTitle != 'Contact'){
            $this->addSiteTitle($typeTitle);
        }
        $this->addSiteTitle($this->contact->getName());
        $this->addJS('framework/modules/Contact/' . MODULE_CONTACT_VER . '/resources/contacts.js');
        $this->addCSS('framework/modules/Contact/' . MODULE_CONTACT_VER . '/resources/contacts.css');
        $contactCat = $contact->getContactCat();
        if (empty($contactCat)) {
            $contactCat = $contact->getDefaultContactCat();
        }
        $this->setListBarURL($contactCat->getListBarURL());
        $this->setPrimaryViewModel($contact);
    }
    
    /**
     * @param boolean $addButtons
     * @return \AbstractContactDetailView
     */
    public function setAddButtons($addButtons) {
        $this->addButtons = $addButtons;
        //Temp: assume if there is no buttons, the sidebar is not neccesarry either
        $this->addSidebar = $addButtons;
        return $this;
    }

    /**
     * @param boolean $addHeader
     * @return \AbstractContactDetailView
     */
    public function setAddHeader($addHeader) {
        $this->addHeader = $addHeader;
        return $this;
    }
    
    /**
     * @param boolean $addContactInfoAndTags
     * @return \AbstractContactDetailView
     */
    public function setAddContactInfoAndTags($addContactInfoAndTags) {
        $this->addContactInfoAndTags = $addContactInfoAndTags;
        return $this;
    }

    /**
     * @param boolean $addDefaultCurrency
     * @return \AbstractContactDetailView
     */
    public function setAddDefaultCurrency($addDefaultCurrency) {
        $this->addDefaultCurrency = $addDefaultCurrency;
        return $this;
    }

    /**
     * @param boolean $addLinkedContacts
     * @return \AbstractContactDetailView
     */
    public function setAddLinkedContacts($addLinkedContacts) {
        $this->addLinkedContacts = $addLinkedContacts;
        return $this;
    }
    
    /**
     * @param boolean addAssignedToContacts
     * @return \AbstractContactDetailView
     */
    public function setAddAssignedToContacts($addAssignedToContacts) {
        $this->addAssignedToContacts = $addAssignedToContacts;
        return $this;
    }
    
    /**
     * @param boolean $addCategories
     * @return \AbstractContactDetailView
     */
    public function setAddCategories($addCategories) {
        $this->addCategories = $addCategories;
        return $this;
    }
    
    /**
     * @param boolean $addContactEvents
     * @return \AbstractContactDetailView
     */
    public function setAddContactEvents($addContactEvents) {
        $this->addContactEvents = $addContactEvents;
        return $this;
    }
    
    /**
     * @param boolean $addDiscounts
     * @return \AbstractContactDetailView
     */
    public function setAddDiscounts($addDiscounts) {
        $this->addDiscounts = $addDiscounts;
        return $this;
    }
    
    /**
     * @param boolean $addLabourRates
     * @return \AbstractContactDetailView
     */
    public function setAddLabourRates($addLabourRates) {
        $this->addLabourRates = $addLabourRates;
        return $this;
    }
    
    /**
     * @param boolean $addInterestRates
     * @return \AbstractContactDetailView
     */
    public function setAddInterestRates($addInterestRates) {
        $this->addInterestRates = $addInterestRates;
        return $this;
    }
    
    /**
     * @param boolean $addInternal
     * @return \AbstractContactDetailView
     */
    public function setAddInternal($addInternal) {
        $this->addInternal = $addInternal;
        return $this;
    }
    
    /**
     * @param boolean $addSalesOrders
     * @return \AbstractContactDetailView
     */
    public function setAddSalesOrders($addSalesOrders) {
        $this->addSalesOrders = $addSalesOrders;
        return $this;
    }
    
    /**
     * @param boolean $addPurchaseOrders
     * @return \AbstractContactDetailView
     */
    public function setAddPurchaseOrders($addPurchaseOrders) {
        $this->addPurchaseOrders = $addPurchaseOrders;
        return $this;
    }
    
    /**
     * @param boolean $addFiles
     * @return \AbstractContactDetailView
     */
    public function setAddFiles($addFiles){
        $this->addFiles = $addFiles;
        return $this;
    }
    
    /**
     * @param boolean $addUserInfo
     * @return \AbstractContactDetailView
     */
    public function setAddUserInfo($addUserInfo) {
        $this->addUserInfo = $addUserInfo;
        return $this;
    }

    protected function addQuickbooksBar(){
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
    
    /**
     * Overlay
     */
    protected function buildOverlayView() {
        $name = $this->contact->getName();
        $avatarString = $this->contact->getAvatarHTML();
        $overlayTitle = $avatarString . '<span class="inline_block">' . $name. '</span>';
        $overlayWrap = new GenericOverlayWrapView($overlayTitle);
        
        if (!empty($this->curTab)) {
            $overlayWrap->isOpenOnLoad(false);
        }
        
        if($this->addInfoTab){
            $this->addOverlayInformationBtn($overlayWrap);
        }
        if($this->addConnections){
            $this->addOverlayConnectionsBtn($overlayWrap);
        }
        if($this->addContactEvents){
            $this->addOverlayEventsBtn($overlayWrap);
        }
        if($this->addFiles){
            $this->addOverlayFilesBtn($overlayWrap);
        }
        if(dbConnection::isModuleInstalled('inventory') && $this->addDiscounts && $this->contact->isClient()){
            $this->addOverlayDiscountsBtn($overlayWrap);
        }
        if(dbConnection::isModuleInstalled('project') && $this->addLabourRates && $this->contact->isInternal()){
            $this->addOverlayLabourRatesBtn($overlayWrap);
        }
        if(dbConnection::isModuleInstalled('order')){
            if ($this->addSalesOrders && $this->contact->isClient()) {
                $this->addOverlaySalesOrdersBtn($overlayWrap);
            }
            if ($this->addPurchaseOrders && $this->contact->isVendor()) {
                $this->addOverlayPurchaseOrdersBtn($overlayWrap);
            }
        }
        
        $this->addHTML($overlayWrap->getHTMLView());
    }
    
    protected function addOverlayInformationBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'General Details';
        $targetRef = 'info';
        $icon = 'contact_details';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }
    
    protected function addOverlayConnectionsBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'Connections';
        $targetRef = 'connections';
        $icon = 'handshake';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }
    
    protected function addOverlayEventsBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'Contact History';
        $targetRef = 'events';
        $icon = 'event';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }
    
    protected function addOverlayDiscountsBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'Discounts';
        $targetRef = 'discounts';
        $icon = 'discount';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }
    
    protected function addOverlayLabourRatesBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'Labour Rates';
        $targetRef = 'labour_rates';
        $icon = 'worker';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }
    
    protected function addOverlaySalesOrdersBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'SOs';
        $targetRef = 'sales_orders';
        $icon = 'sales_orders';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }
    
    protected function addOverlayPurchaseOrdersBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'POs';
        $targetRef = 'purchase_orders';
        $icon = 'purchase_orders';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }
    
    protected function addOverlayBillsBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'Bills';
        $targetRef = 'bills';
        $icon = 'bill';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }
    
    protected function addOverlayTimesheetsBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'Timesheets';
        $targetRef = 'timesheets';
        $icon = 'timesheet';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }
    
    protected function addOverlayFilesBtn(GenericOverlayWrapView $overlayWrap){
        $title = 'Files';
        $targetRef = 'files';
        $icon = 'file';
        $gridView = new GenericOverlayGridView($title, $targetRef, $icon);
        $overlayWrap->addOverlayView($gridView);
    }

    public function buildView() {
        if ($this->addQuickbooksBar) {
            $this->addQuickbooksBar();
        }
        parent::buildView();
    }

    protected function addViewBodyContent(){
        $this->addAdvancedBlocks();
    }
    
    protected function addAdvancedBlocks(){
        if($this->addInfoTab){
            $this->addInfoSectionAdvancedBlock();
        }
        
        if($this->addConnections){
            $this->addConnectionsAdvancedBlock();
        }

        if($this->addContactEvents){
            $this->addContactEventsAdvancedBlock();
        }

        if($this->addFiles){
            $this->addFilesAdvancedBlock();
        }

        if(dbConnection::isModuleInstalled('inventory') && $this->addDiscounts && $this->contact->isClient()){
            $this->addDiscountsAdvancedBlock();
        }
        
        if(dbConnection::isModuleInstalled('project') && $this->addLabourRates && $this->contact->isInternal()){
            $this->addLabourRatesAdvancedBlock();
        }

        if(dbConnection::isModuleInstalled('order')){
            if ($this->addSalesOrders && $this->contact->isClient()) {
                $this->addSalesOrdersAdvancedBlock();
            }
            if ($this->addPurchaseOrders && $this->contact->isVendor()) {
                $this->addPurchaseOrdersAdvancedBlock();
            }
        }
    }

    protected function addDeleteBtn(){
        if ($this->contact->isDeleteable()) {
            $deleteURL = $this->contact->getDeleteURL();
            $this->addHTML('<a href="' . $deleteURL . '" class="custom_btn open_modal_form" title="Delete">'.GI_StringUtils::getIcon('trash').'<span class="btn_text">Delete</span></a>');
        }
    }
    
    protected function addEditBtn(){
        if ($this->contact->isEditable()) {
            $editURL = $this->contact->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" title="Edit" class="custom_btn">'.GI_StringUtils::getIcon('edit').'<span class="btn_text">Edit</span></a>');
        }
    }

    protected function addSidebarGeneralInfoBtns() {
        $this->addDeleteBtn();
        $this->addEditBtn();
    }

    protected function getGeneralBtnOptions() {
        $btnOptionsArray = array(array('type' => 'details'));
        $this->addDeleteBtnOptions($btnOptionsArray);
        $this->addEditBtnOptions($btnOptionsArray);
        return $btnOptionsArray;
    }
    
    protected function addDeleteBtnOptions(&$btnOptionsArray){
        if ($this->contact->isDeleteable()) {
            $linkURL = $this->contact->getDeleteURL();
            $linkType = 'delete';
            $linkTitle = 'Delete';
            $linkIcon = 'trash';
            $btnOptionsArray[] = array(
                'type' => $linkType,
                'title' => $linkTitle,
                'icon' => $linkIcon,
                'link' => $linkURL,
                'class_names' => 'open_modal_form',
            );
        }
    }
    
    protected function addEditBtnOptions(&$btnOptionsArray){
        if ($this->contact->isEditable()) {
            $linkURL = $this->contact->getEditURL();
            $linkType = 'edit';
            $linkTitle = 'Edit';
            $linkIcon = 'pencil';
            $btnOptionsArray[] = array(
                'type' => $linkType,
                'title' => $linkTitle,
                'icon' => $linkIcon,
                'link' => $linkURL,
                'class_names' => 'ajax_link',
            );
        }
    }

    protected function addWindowTitle(){
        $mainTitle = '';
        $avatarString = $this->contact->getAvatarHTML();
        $mainTitle .= $avatarString;
 
        $title = $this->contact->getName();
        $mainTitle .= '<span class="inline_block">' . $title . '</span>';
        $this->addMainTitle($mainTitle, 'main_head has_avatar');
        return $this;
    }
    
    protected function addWindowBtns(){
        if ($this->hasOverlay) {
            $this->addShowOverlayBtn();
        }
        return $this;
    }

    protected function addInfoSectionAdvancedBlock() {
        $isOpenOnLoad = false;
        if($this->curTab == 'info' || (empty($this->curTab) && !$this->hasOverlay)){
            $isOpenOnLoad = true;
        }
        $targetRef = 'info';
        $headerIcon = 'contact_details';
        $headerTitle = 'General Details';
        $classNames = 'has_right_btns '.$this->targetRefPrefix.$targetRef;
        $btnOptionsArray = $this->getGeneralBtnOptions();
        $this->openAdvancedBlockWrap($headerTitle, $headerIcon, $targetRef, $isOpenOnLoad, $classNames);
        $this->addInfoSection();
        $this->closeAdvancedBlockWrap();
        $this->addAdvancedBlockToSidebar($headerTitle, $targetRef, $btnOptionsArray, $headerIcon);
    }

    protected function addConnectionsAdvancedBlock() {
        $isOpenOnLoad = false;
        if($this->curTab == 'connections'){
            $isOpenOnLoad = true;
        }
        $targetRef = 'connections';
        $headerIcon = 'handshake';
        $headerTitle = 'Connections';
        $classNames = $this->targetRefPrefix.$targetRef;
        $this->openAdvancedBlockWrap($headerTitle, $headerIcon, $targetRef, $isOpenOnLoad, $classNames);
        $this->addConnectionsSection();
        $this->closeAdvancedBlockWrap();
        $this->addAdvancedBlockToSidebar($headerTitle, $targetRef, array(array('type' => 'details')), $headerIcon);
    }
    
    protected function addConnectionsSection(GI_View $view = NULL){
        if(is_null($view)){
            $view = $this;
        }

        if($this->addLinkedContacts){
            $this->addLinkedContactsSection($view);
        }
                    
        if($this->addAssignedToContacts){
            $this->addAssignedToContactsSection($view);
        }
    }
    
    protected function addContactEventsAdvancedBlock() {
        $isOpenOnLoad = false;
        if($this->curTab == 'events'){
            $isOpenOnLoad = true;
        }
        $btnOptionsArray = array();
//        if (!empty($this->contact) && $this->contact->isEventAddable()) {
//            $addEventURL = $this->contact->getAddEventURL();
//            $btnOptionsArray[] = array(
//                    'type' => 'add',
//                    'link' => $addEventURL,
//                    'title' => 'Add Event',
//                    'icon' => 'plus',
//                    'class_names' => 'open_modal_form',
//                    'other_data' =>  'data-modal-class="medium_sized"',
//                );
//        }
//        
        $btnOptionsArray[] = array('type' => 'details');
        
        $targetRef = 'events';
        $advClassNames = 'has_right_btns  '.$this->targetRefPrefix.$targetRef;
        $headerIcon = 'event';
        $headerTitle = 'Contact History';
        $isAddToSidebar = true;
        $view = $this->contact->getEventListView();
        $view->setShowBtns(true);
        $view->setContact($this->contact);
        $view->setAddWrap(false);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar);
    }
    
    protected function addFilesAdvancedBlock() {
        $isOpenOnLoad = false;
        if($this->curTab == 'files'){
            $isOpenOnLoad = true;
        }
        
        $targetRef = 'files';
        $headerIcon = 'file';
        $headerTitle = 'Files';
        $classNames = $this->targetRefPrefix.$targetRef;
        $this->openAdvancedBlockWrap($headerTitle, $headerIcon, $targetRef, $isOpenOnLoad, $classNames);
        $this->addFilesSection();
        $this->closeAdvancedBlockWrap();
        $this->addAdvancedBlockToSidebar($headerTitle, $targetRef, array(array('type' => 'details')), $headerIcon);
    }
    
    protected function addSalesOrdersAdvancedBlock() {
        $isOpenOnLoad = false;
        if($this->curTab == 'sales_orders'){
            $isOpenOnLoad = true;
        }
        
        $targetRef = 'sales_orders';
        $headerIcon = 'sales_orders';
        $headerTitle = 'Sales Orders';
        $classNames = 'has_right_btns '.$this->targetRefPrefix.$targetRef;
        $this->openAdvancedBlockWrap($headerTitle, $headerIcon, $targetRef, $isOpenOnLoad, $classNames);
        if($this->addSalesOrders && $this->contact->isClient()){
            $this->addOrdersSection('sales');
        }
        $this->closeAdvancedBlockWrap();
        
        $btnOptionsArray = $this->getOrdersBtnOptions('sales');
        $this->addAdvancedBlockToSidebar($headerTitle, $targetRef, $btnOptionsArray, $headerIcon);
    }
    
    protected function addPurchaseOrdersAdvancedBlock() {
        $isOpenOnLoad = false;
        if($this->curTab == 'purchase_orders'){
            $isOpenOnLoad = true;
        }
        
        $targetRef = 'purchase_orders';
        $headerIcon = 'purchase_orders';
        $headerTitle = 'Purchase Orders';
        $classNames = 'has_right_btns '.$this->targetRefPrefix.$targetRef;
        $this->openAdvancedBlockWrap($headerTitle, $headerIcon, $targetRef, $isOpenOnLoad, $classNames);
        if($this->addPurchaseOrders && $this->contact->isVendor()){
            $this->addOrdersSection('purchase');
        }
        $this->closeAdvancedBlockWrap();
        
        $btnOptionsArray = $this->getOrdersBtnOptions('purchase');
        $this->addAdvancedBlockToSidebar($headerTitle, $targetRef, $btnOptionsArray, $headerIcon);
    }
    
    protected function getOrdersBtnOptions($type) {
        $btnOptionsArray = array();
        $this->addAddOrderBtnOptions($btnOptionsArray, $type);
        //$this->addAddFilterWarehouseBtnOptions($btnOptionsArray);
        return $btnOptionsArray;
    }
    protected function addAddOrderBtnOptions(&$btnOptionsArray, $type){
        $sampleOrder = OrderFactory::buildNewModel($type);
        if ($sampleOrder->isAddable()) {
            $linkURL = GI_URLUtils::buildURL( array (
                'controller' => 'order',
                'action' => 'add',
                'type' => $type,
                'contactId' => $this->contact->getId(),
            ));
            $linkType = 'add';
            $linkTitle = 'Add '.$sampleOrder->getViewTitle();
            $linkIcon = 'plus';
            $btnOptionsArray[] = array(
                'type' => $linkType,
                'title' => $linkTitle,
                'icon' => $linkIcon,
                'link' => $linkURL,
            );
        }
    }
//    protected function addAddFilterWarehouseBtnOptions(&$btnOptionsArray){
//        $filterBtnView = new InvFilterWarehouseBtnView();
//        $btnOptionsArray[] = array(
//            'type' => 'filter',
//            'view' => $filterBtnView->getHTMLView(),
//        );
//    }
    
    protected function addDiscountsAdvancedBlock() {
        $isOpenOnLoad = false;
        if($this->curTab == 'discounts'){
            $isOpenOnLoad = true;
        }
        $targetRef = 'discounts';
        $advClassNames = 'has_right_btns '.$this->targetRefPrefix.$targetRef;
        $headerIcon = 'discount';
        $headerTitle = 'Discounts';
        $btnOptionsArray = array();
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'inventory',
                'action' => 'addDiscount',
                'type' => 'discount',
                'contactId' => $this->contact->getId(),
            ));
        $btnOptionsArray[] = array (
            'type' => 'add',
            'title' => 'Add Item Discount',
            'icon' => 'plus',
            'link' => $linkURL,
            'class_names' => 'open_modal_form',
        );
        
        $discountIndexView = InvDiscountFactory::getContactDiscountIndex($this->contact);
        $discountIndexView->setAddWrap(false);
        $discountIndexView->setAddTitle(false);
        $isAddToSidebar = true;
        $this->addAdvancedBlock($headerTitle, $discountIndexView->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar);
    }
    
    protected function addLabourRatesAdvancedBlock() {
        $isOpenOnLoad = false;
        if($this->curTab == 'labour_rates'){
            $isOpenOnLoad = true;
        }
        $targetRef = 'labour_rates';
        $advClassNames = $this->targetRefPrefix.$targetRef;
        $headerIcon = 'worker';
        $headerTitle = 'Labour Rates';
        $btnOptionsArray = array();
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'project',
                'action' => 'addContactLabourRate',
                'contactId' => $this->contact->getId(),
            ));
        $btnOptionsArray[] = array (
            'type' => 'add',
            'title' => 'Labour Rate',
            'link' => $linkURL,
            'class_names' => 'open_modal_form',
        );
            
        $labourRateIndexView = ContactLabourRateFactory::getContactLabourRateIndex($this->contact);
        $labourRateIndexView->setAddWrap(false);
        $labourRateIndexView->setAddButtons(false);
        $labourRateIndexView->setAddTitle(false);
        $isAddToSidebar = true;
        //@todo move to add button to the sidebar
        $this->addAdvancedBlock($headerTitle, $labourRateIndexView->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar);
    }
    
    protected function addInfoSection(GI_View $view = NULL){
     
        if(is_null($view)){
            $view = $this;
        }
        
        $this->addDetailsAdvancedBlockBtns($view);
        
        if($this->addContactInfoAndTags){
            $this->addContactInfoAndTagsSection($view);
        }
        if(!$this->addConnections){
            if($this->addLinkedContacts){
                $this->addLinkedContactsSection($view);
            }

            if($this->addAssignedToContacts){
                $this->addAssignedToContactsSection($view);
            }
        }
    }
    
    protected function addDetailsAdvancedBlockBtns(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        
        $view->addHTML('<div class="right_btns advanced_content_btns ajax_link_wrap">');
        $this->addDeleteBtn();
        $this->addEditBtn();
        $view->addHTML('</div>');
    }
    
    protected function addUserInfoSection(GI_View $view = NULL) {
        //Do Nothing
    }
    
    protected function addContactInfoAndTagsSection(GI_View $view = NULL, $class = '') {
        if(is_null($view)){
            $view = $this;
        }
        
        $view->addHTML('<div class="columns halves '.$class.'">');
            $view->addHTML('<div class="column">');
            $view->addHTML('<h2 class="content_group_title">General</h2>');
            if ($this->addUserInfo) {
                $this->addUserInfoSection($view);
            }
        
            $this->addContactInfoBlock($view);
            
            if($this->addCategories){
                $this->addCategorySection($view);
            }
            
            $this->addTagsBlock($view);

            if ($this->addDefaultCurrency) {
                $this->addDefaultCurrencySection($view);
            }
            
            $this->addContactSubCategoryTagSection();
        
            $view->addHTML('</div>');
            
            //Google Map
            $view->addHTML('<div class="column">');
                $this->addGoogleMapBlock();
            $view->addHTML('</div>');
        $view->addHTML('</div>');
        
        $this->addNotesBlock();
    }
    
    protected function addContactInfoBlock(GI_View $view = NULL) {
        if(is_null($view)){
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
        if(is_null($view)){
            $view = $this;
        }
        $view->addHTML('<div class="google_map_wrap">');
            $view->addHTML('<h2 class="content_group_title">Google Map</h2>');
            $name = $this->contact->getRealName();
            $address = $this->contact->getAddress();
            $view->addHTML('<div id="google_map" class="org_map" data-title="'.$name.'" data-addr="'.$address.'"></div>');
        $view->addHTML('</div>');
    }
    
    protected function addNotesBlock(GI_View $view = NULL){
        if(is_null($view)){
            $view = $this;
        }
        $notes = $this->contact->getProperty('notes');
        if(!empty($notes)){
            $view->addHTML('<div class="content_block_wrap">');
            $view->addContentBlock(GI_StringUtils::nl2brHTML($notes), 'Notes');
            $view->addHTML('</div>');
        }
    }
    
    protected function addTagsBlock(GI_View $view = NULL) {
        if(is_null($view)){
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

    protected function addLinkedContactsSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $relationshipsDetailView = $this->contact->getContactRelationshipsDetailView();
        if (!empty($relationshipsDetailView)) {
            $view->addHTML('<div class="content_group ajax_link_wrap">');
            $view->addHTML($relationshipsDetailView->getHTMLView());
            $view->addHTML('</div>');
        }
    }
    
    protected function addAssignedToContactsSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $assignedUsersDetailView = $this->contact->getAssignedToContactsDetailView();
        if (!empty($assignedUsersDetailView)) {
            $view->addHTML('<div class="content_group ajax_link_wrap">');
            $view->addHTML($assignedUsersDetailView->getHTMLView());
            $view->addHTML('</div>');
        }
    }
    
    protected function addFilesSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $view->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">')
                ->addHTML('<h3>Images</h3>');
        $this->addImageSection($view);
        $view->addHTML('</div>')
                ->addHTML('<div class="column">')
                ->addHTML('<h3>Files</h3>');
        $this->addFileSection($view);
        $view->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addImageSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $folder = $this->contact->getImageFolder();
        if($folder){
            $view->addHTML('<div class="content_files">');
            $files = $folder->getFiles();
            if(!empty($files)){
                foreach($files as $file){
                    $fileView = $file->getView();
                    $fileView->setIsDeleteable(false);
                    $fileView->setIsRenamable(false);
                    $view->addHTML($fileView->getHTMLView());
                }
            } else {
                $view->addHTML('<p class="no_model_message content_block">No images found.</p>');
            }
            $view->addHTML('</div>');
        }
    }
    
    protected function addFileSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $folder = $this->contact->getFolder(false);
        if($folder){
            $view->addHTML('<div class="content_files">');
            $files = $folder->getFiles();
            if(!empty($files)){
                foreach($files as $file){
                    $fileView = $file->getView();
                    $fileView->setIsDeleteable(false);
                    $fileView->setIsRenamable(false);
                    $view->addHTML($fileView->getHTMLView());
                }
            } else {
                $view->addHTML('<p class="no_model_message content_block">No files found.</p>');
            }
            $view->addHTML('</div>');
        }
    }
    
    /**
     * Add Contact categories
     */
    protected function addCategorySection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $contactCat = $this->contact->getContactCat();
        if (!empty($contactCat)) {
            $contactCatDetailView = $contactCat->getDetailView();
            if (!empty($contactCatDetailView)) {
                $view->addHTML($contactCatDetailView->getHTMLView());
            }
        }
    }
    
    /**
     * Add Contact events
     */
    protected function addContactEventSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $view->addHTML('<div class="columns">');
            if (!empty($this->contact) && $this->contact->isEventAddable()) {
                $view->addHTML('<div class="right_btns">');
                    $addEventURL = $this->contact->getAddEventURL();
                    $view->addHTML('<a href="' . $addEventURL . '" title="New Event" class="custom_btn open_modal_form" data-modal-class="medium_sized">'.GI_StringUtils::getIcon('add').'<span class="btn_text">Event</span></a>');
                $view->addHTML('</div>');
            }
            
            $view->addHTML('<h2>Contact History</h2>');
            
            $view->addHTML('<div class="events">');
                $eventListView = $this->contact->getEventListView();
                $eventListView->setAddWrap(false);
                $view->addHTML($eventListView->getHTMLView());
            $view->addHTML('</div>');
        $view->addHTML('</div>');
    }
    
    /**
     * Add interest rates
     */
    protected function addInterestRateSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        
        $terms = $this->contact->getTerms();
        $interestRatePercent = $this->contact->getProperty('contact_org.interest_rate');
        $cmpdXDays = $this->contact->getProperty('contact_org.cmpd_x_days');
        
        $view->addHTML('<hr/>');
        $view->addHTML('<div class="columns halves">');
        if (!empty($terms)) {
            $view->addHTML('<div class="column">');
                $view->addContentBlock($terms->getProperty('terms'), 'Terms');
            $view->addHTML('</div>');
        }

        if (!empty($interestRatePercent) || !empty($cmpdXDays)) {
            $view->addHTML('<div class="column">');
                $view->addContentBlock('Interest Rate : '.($interestRatePercent * 100).' % , Compounded every '.$cmpdXDays.' day(s)', 'Interest Rates');
            $view->addHTML('</div>');
        } else {
            $settingsDefIntRate = $this->contact->getSettingsDefIntRate();
            if (!empty($settingsDefIntRate)) {
                $defaultRate = $settingsDefIntRate->getProperty('interest_rate');
                $defaultCompoundDays = $settingsDefIntRate->getProperty('cmpd_x_days');
            } else {
                $defaultRate = 0.0;
                $defaultCompoundDays = 0;
            }

            $view->addHTML('<div class="column">');
                $view->addContentBlock('Interest Rate : '.($defaultRate * 100).' %, Compounded every '.$defaultCompoundDays.' day(s)', 'Interest Rates');
            $view->addHTML('</div>');
        }
        $view->addHTML('</div>');
    }
    
    protected function addOutstandingInvoiceBalanceSection(GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }
        $outstandingBalance = $this->contact->getOutstandingFinalizedInvoiceBalance();
        $view->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $view->addContentBlock('$' . GI_StringUtils::formatMoney($outstandingBalance, true) . ' (CAD)', 'Outstanding Balance of Invoices');
        $view->addHTML('</div>')
                ->addHTML('<div class="column">');
        
        $view->addHTML('</div>')
                ->addHTML('</div>');
    }

    /**
     * Add Contact categories
     */
    protected function addInternalSection(GI_View $view = NULL) {
        if (Permission::verifyByRef('mark_contact_as_internal')) {
            if (is_null($view)) {
                $view = $this;
            }
            $internal = $this->contact->getProperty('internal');
            if (!empty($internal) && $internal == 1) {
                $internalText = 'Yes';
            } else {
                $internalText = 'No';
            }
            $view->addHTML('<div class="columns halves"><div class="column">');
            $view->addContentBlock($internalText, 'Internal');
            $view->addHTML('</div></div>');
        }
    }

    protected function addDefaultCurrencySection(GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }
        $defaultCurrency = $this->contact->getDefaultCurrency();
        if (!empty($defaultCurrency)) {
            $value = $defaultCurrency->getProperty('name');
        } else {
            $value = '--';
        }
        $view->addHTML('<div class="content_block_wrap">');
        $view->addContentBlock($value, 'Currency');
        $view->addHTML('</div>');
    }

    protected function addContactSubCategoryTagSection(GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }
        $subcatTag = $this->contact->getSubCategoryTag();
        if (!empty($subcatTag)) {
            $value = $subcatTag->getProperty('title');
        } else {
            $value = '--';
        }
        $view->addHTML('<div class="content_block_wrap">');
        $view->addContentBlock($value, Lang::getString('contact_sub_category'));
        $view->addHTML('</div>');
    }

    protected function addOrdersSection($typeRef, GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        if (dbConnection::isModuleInstalled('order')) {
            $orderIndexView = OrderFactory::getContactOrderIndex($this->contact, $typeRef);
            if (!empty($orderIndexView)) {
                $orderIndexView->setAddWrap(false);
                $view->addHTML('<div class="contact_order_table_wrap ajax_link_wrap inner_view_wrap">');
                $view->addHTML($orderIndexView->getHTMLView());
                $view->addHTML('</div>');
            }
        }
    }
    
}
