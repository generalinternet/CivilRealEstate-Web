<?php
/**
 * Description of AbstractContactInd
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.4
 */
abstract class AbstractContactLoc extends AbstractContact {
    
    protected $addressModel;
    protected $parentContactOrg;
    protected $accountingLocationTag;

    protected $defaultInfoTypeRefs = array(
        'address'
    );
    
    protected $multiInfoEnabledRefs = array();
    
    public static function addNameFilterToDataSearch($name, \GI_DataSearch $dataSearch, $contactTableAlias = NULL) {
        $filterColumns = array(
            'loc.name'
        );
        $dataSearch->filterGroup()
                ->filterTermsLike($filterColumns, $name)
                ->filter('loc.status', 1)
                ->closeGroup();
        
        $dataSearch->orderByLikeScore($filterColumns, $name);
        
        parent::addNameFilterToDataSearch($name, $dataSearch);
    }
    
    public function setPropertiesFromForm(\GI_Form $form) {
        parent::setPropertiesFromForm($form);
        $locName = filter_input(INPUT_POST, 'loc_name');
        $this->setProperty('contact_loc.name', $locName);
        $internal = filter_input(INPUT_POST, 'internal');
        $this->setProperty('internal', $internal);
        return true;
    }
    
    public function handleFormSubmission($form, $pId = NULL) {
        if(!$this->validateForm($form)){
            return false;
        }

        if (!$this->setPropertiesFromForm($form)) {
            return false;
        }

        if (!$this->handleAccountingLocTagFormField()) {
            return false;
        }
        if (!$this->save()) {
            return false;
        }
        $addressModel = $this->getAddressModel();
        $addressModel->setProperty('contact_id', $this->getProperty('id'));
        if (!$addressModel->handleFormSubmission($form)) {
            return false;
        }
        $parentContactId = filter_input(INPUT_POST, 'p_contact_id');
        $parentContact = ContactFactory::getModelById($parentContactId);
        if (!ContactFactory::linkContactAndContact($parentContact, $this)) {
            return false;
        }


        return true;
    }

    public function validateForm(\GI_Form $form) {
        if ($this->formValidated) {
            return true;
        }
        if ($form->wasSubmitted() && $form->validate()) {
            $this->formValidated = true;
        }
        return $this->formValidated;
    }

    protected function handleAccountingLocTagFormField() {
        $accountingLocTagRef = filter_input(INPUT_POST, 'accounting_loc_tag_ref');
        $accountingLocTag = TagFactory::getModelByRefAndTypeRef($accountingLocTagRef, 'accounting_loc');
        if (!empty($accountingLocTag)) {
            $this->setProperty('contact_loc.accounting_loc_tag_id', $accountingLocTag->getProperty('id'));
        }
        return true;
    }

    public function getFormView(GI_Form $form) {
        $formView = new ContactLocFormView($form, $this);
        $this->setUploadersOnFormView($formView);
        return $formView;
    }

    public function getName() {
        return $this->getProperty('contact_loc.name');
    }
    
    /**
     * @return AbstractContactOrg
     */
    public function getParentContactOrg() {
        //TODO - Use contact relationship factory for this
        $search = new GI_DataSearch('contact_relationship');
        $search->filter('c_contact_id', $this->getProperty('id'));
        $daoArray = $search->select();
        if (!empty($daoArray)) {
            return ContactFactory::getModelById($daoArray[0]->getProperty('p_contact_id'));
        }
        return NULL;
    }
    
    public function getParentContactOrgTitle(){
        $parent = $this->getParentContactOrg();
        if($parent){
            return $parent->getName();
        }
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            //Name
            array(
                'header_title' => 'Name',
                'method_name' => 'getName',
                'cell_url_method_name' => 'getViewURL',
            ),
            array(
                'header_title' => 'Address',
                'method_name' => 'getAddress'
            ),
            array(
                'header_title' => 'Organization',
                'method_name' => 'getParentContactOrgTitle'
            )

        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public function getViewTitle($plural = true) {
        $title = 'Location';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    public function getDetailView() {
        $detailView = new ContactLocDetailView($this);
        return $detailView;
    }

    public function getSummaryView($relationship = NULL) {
        $summaryView = new ContactLocSummaryView($this, $relationship);
        return $summaryView;
    }

//    public function getLinkedContactsDetailView($linkedTypeRefs = NULL) {
//        if (empty($linkedTypeRefs)) {
//            $linkedTypeRefs = array(
//                ''
//            );
//        } else {
//            return parent::getLinkedContactsDetailView($linkedTypeRefs);
//        }
//        return NULL;
//    }
    
    public function getContactRelationshipsDetailView($linkedTypeRefs = NULL) {
        if (empty($linkedTypeRefs)) {
            $linkedTypeRefs = array(
                'org' => 'parent'
            );
        }
        $view = parent::getContactRelationshipsDetailView($linkedTypeRefs);
        $view->setAddLinkBtns(false);
        $view->setAddDeleteLinkBtns(false);
        return $view;
    }

    public function getAllowAddOnIndex() {
        return false;
    }
    
    public function getAddressModel(GI_Form $form = NULL) {
        if (empty($this->addressModel)) {
            $addressModel = $this->getContactInfo('address');
            if (empty($addressModel)) {
                $addressModel = ContactInfoFactory::buildNewModel('address');
            } else {
                $this->addressModel = $addressModel;
            }
        } else {
            $addressModel = $this->addressModel;
        }
        if (!empty($form && $form->wasSubmitted())) {
            $addressModel->setPropertiesFromForm($form);
        }
        return $addressModel;
    }

    /**
     * 
     * @return AbstractTag
     */
    public function getAccountingLocationTag() {
        if (empty($this->accountingLocationTag)) {
            $tagId = $this->getProperty('contact_loc.accounting_loc_tag_id');
            if (!empty($tagId)) {
                $this->accountingLocationTag = TagFactory::getModelById($tagId);
            }
        }
        return $this->accountingLocationTag;
    }
    
    /**
     * Get link buttons on the contact detail page
     * @param type $id
     * @param type $relation
     * @return string
     */
    public function getLinkButtons($id, $relation = NULL) {
        $html = '';
        $addAddWarehouseButton = false;
        if (Permission::verifyByRef('add_warehouses')) {
            if (ProjectConfig::getIsFranchisedSystem()) {
                $currentFranchise = Login::getCurrentFranchise();
                if (!empty($currentFranchise) && $currentFranchise->getId() == $id) {
                    $addAddWarehouseButton = true;
                }
            } else {
                $addAddWarehouseButton = true;
            }
        }
        //Add new warehouse
        if (true || $addAddWarehouseButton) {
            $parentContact = ContactFactory::getModelById($id);
            if (!empty($parentContact) && $parentContact->isInternal()) {
                $addWarehouseURL = GI_URLUtils::buildURL(array(
                            'controller' => 'contact',
                            'action' => 'addWarehouse',
                            'type' => 'warehouse',
                            'pId' => $id,
                            'refresh' => 1
                ));
                $html .= '<a href="' . $addWarehouseURL . '" title="Add New Warehouse" class="custom_btn open_modal_form" data-modal-class="medium_sized">'.GI_StringUtils::getIcon('add').'<span class="btn_text">Warehouse</span></a>';
            }
        }

        //Add new location contact
        $addContactURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contact',
                    'action' => 'add',
                    'type' => $this->getTypeRef(),
                    'pId' => $id,
                    'refresh' => 1
        ));
        $html .= '<a href="' . $addContactURL . '" title="Add New ' . $this->getTypeTitle() . '" class="custom_btn open_modal_form" data-modal-class="medium_sized">'.GI_StringUtils::getIcon('add').'<span class="btn_text">' . $this->getTypeTitle() . '</span></a>';

        return $html;
    }

    /**
     * @return boolean
     */
    public function markAsInternal() {
        if (empty($this->getProperty('internal'))) {
            $this->setProperty('internal', 1);
            if (!$this->save()) {
                return false;
            }
        }
        $childContacts = $this->getChildContacts();
        if (!empty($childContacts)) {
            foreach ($childContacts as $childContact) {
                if (!$childContact->markAsInternal()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * @return boolean
     */
    public function markAsNotInternal() {
        if (!empty($this->getProperty('internal'))) {
            $this->setProperty('internal', 0);
            if (!$this->save()) {
                return false;
            }
        }
        $childContacts = $this->getChildContacts();
        if (!empty($childContacts)) {
            foreach ($childContacts as $childContact) {
                if (!$childContact->markAsNotInternal()) {
                    return false;
                }
            }
        }
        return true;
    }

    public function isLocation() {
        return true;
    }

    public function isQuickbooksExportable() {
        return false;
    }

    public function isQuickbooksImportable() {
        return false;
    }

}
