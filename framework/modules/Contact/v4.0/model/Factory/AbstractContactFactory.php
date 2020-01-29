<?php
/**
 * Description of AbstractContactFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class AbstractContactFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact';
    protected static $models = array();
    protected static $deleteFKTableNameExceptions = array(
        'contact_link_to_tag', //Deprecated - TODO - remove in v5
        'contact_link_to_tax_link_to_region' //Deprecated - TODO - remove in v5
    );

    public static function validateModelFranchise(\GI_Model $model) {
        if(is_a($model, 'AbstractContactOrgFranchise')){
            return true;
        }
        return parent::validateModelFranchise($model);
    }

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractContact
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'org':
                $model = new ContactOrg($map);
                break;
            case 'franchise':
                $model = new ContactOrgFranchise($map);
                break;
            case 'ind':
                $model = new ContactInd($map);
                break;
            case 'loc':
                $model = new ContactLoc($map);
                break;
            case 'warehouse':
                $model = new ContactLocWarehouse($map);
                break;
            case 'contact':
            default:
                $model = new Contact($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param string $typeRef
     * @return array
     */
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'contact':
                $typeRefs = array('contact');
                break;
            case 'ind':
                $typeRefs = array('ind', 'ind');
                break;
            case 'org':
                $typeRefs = array('org', 'org');
                break;
            case 'franchise':
                $typeRefs = array('org', 'franchise');
                break;
            case 'loc':
                $typeRefs = array('loc', 'loc');
                break;
            case 'warehouse':
                $typeRefs = array('loc', 'warehouse');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * @param string $typeRef
     * @return AbstractContact
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return AbstractContact
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param integer $sourceUserId
     * @return AbstractContact
     */
    public static function getBySourceUserId($sourceUserId, $typeRef = NULL){
        $contactSearch = static::search()
                ->filter('source_user_id', $sourceUserId);
        if (!empty($typeRef)) {
            $contactSearch->filterByTypeRef($typeRef);
        }
        $contactResult = $contactSearch->select();
        if($contactResult){
            return $contactResult[0];
        }
        return NULL;
    }
    
    /**
     * @param AbstractContact $pContact
     * @param AbstractContact $cContact
     * @return boolean
     */
    public static function linkContactAndContact(AbstractContact $pContact, AbstractContact $cContact, $relationshipTypeRef = 'relationship') {
        $pContactId = $pContact->getProperty('id');
        $cContactId = $cContact->getProperty('id');
        
        $contacRelationshipSearch = ContactRelationshipFactory::search();
        $contactLinkResult = $contacRelationshipSearch->filter('p_contact_id', $pContactId)
                ->filter('c_contact_id', $cContactId)
                ->filterNotNull('status')
                ->select();
        
        if (!empty($contactLinkResult)) {
            $existingContactRelationship = $contactLinkResult[0];
            if(!$existingContactRelationship->getProperty('status')){
                $existingContactRelationship->setProperty('status', 1);
                return $existingContactRelationship->save();
            }
            return true;
        }
        $contactRelationship = ContactRelationshipFactory::buildNewModel($relationshipTypeRef);
        $contactRelationship->setProperty('p_contact_id', $pContactId);
        $contactRelationship->setProperty('c_contact_id', $cContactId);
        if (!$contactRelationship->save()) {
            return false;
        }
        return true;
    }
    
    /**
     * @param AbstractContact $pContact
     * @param AbstractContact $cContact
     * @return boolean
     */
    public static function unlinkContactAndContact(AbstractContact $pContact, AbstractContact $cContact) {
        $pContactId = $pContact->getProperty('id');
        $cContactId = $cContact->getProperty('id');
        
        $contactLinkSearch = new GI_DataSearch('contact_relationship');
        $contactLinkResult = $contactLinkSearch->filter('p_contact_id', $pContactId)
                ->filter('c_contact_id', $cContactId)
                ->select();
        
        if (!empty($contactLinkResult)) {
            foreach ($contactLinkResult as $contactLink) {
                if (!$contactLink->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * @param AbstractContact $contact
     * @param AbstractTag $tag
     * @return boolean
     */
    public static function linkContactAndTag(AbstractContact $contact, AbstractTag $tag) {
        $contactId = $contact->getProperty('id');
        $tagId = $tag->getProperty('id');
        
        $tagLinkSearch = new GI_DataSearch('item_link_to_tag');
        $tagLinkResult = $tagLinkSearch->filter('item_id', $contactId)
                ->filter('table_name', 'contact')
                ->filter('tag_id', $tagId)
                ->filterNotNull('status')
                ->select();
        
        if (!empty($tagLinkResult)) {
            $existingTagLink = $tagLinkResult[0];
            if(!$existingTagLink->getProperty('status')){
                $existingTagLink->setProperty('status', 1);
                return $existingTagLink->save();
            }
            return true;
        }
        
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $tagLink = new $defaultDAOClass('item_link_to_tag');
        $tagLink->setProperty('item_id', $contactId);
        $tagLink->setProperty('table_name', 'contact');
        $tagLink->setProperty('tag_id', $tagId);
        if ($tagLink->save()) {
            return true;
        }
        return false;
    }

    /**
     * @param AbstractContact $contact
     * @param AbstractTag $tag
     * @return boolean
     */
    public static function unlinkContactAndTag(AbstractContact $contact, AbstractTag $tag) {
        $contactId = $contact->getProperty('id');
        $tagId = $tag->getProperty('id');
        
        $tagLinkSearch = new GI_DataSearch('item_link_to_tag');
        $tagLinkResult = $tagLinkSearch->filter('item_id', $contactId)
                ->filter('table_name', 'contact')
                ->filter('tag_id', $tagId)
                ->select();
        
        if($tagLinkResult){
            foreach($tagLinkResult as $tagLink){
                if(!$tagLink->softDelete()){
                    return false;
                }
            }
        }
        return true;
    }
    
    
    public static function verifyContactOrg($typeRef, $title, $dba) {
//        $pType = ContactFactory::getPTypeRef($typeRef);
//        if (empty($pType)) {
//            $pType = 'org';
//        }
        $existingContactOrgArray = ContactFactory::search()
                ->filterByTypeRef($typeRef)
                ->filter('org.title', $title)
                ->filter('org.doing_bus_as', $dba)
                ->select();

        if (!empty($existingContactOrgArray)) {
            $contactOrg = $existingContactOrgArray[0];
        } else {
            $contactOrg = ContactFactory::buildNewModel('org');
            $contactOrg->setProperty('contact_org.title', $title);
            $contactOrg->setProperty('contact_org.doing_bus_as', $dba);
            if (!$contactOrg->save()) {
                return NULL;
            }
        }
        return $contactOrg;
    }

    public static function verifyContactIndLinkedToContactOrg(ContactOrg $contactOrg, $typeRef, $firstName, $lastName) {
        $contactTableName = dbConfig::getDbPrefix() . 'contact';
//        $pTypeRef = ContactFactory::getPTypeRef($typeRef);
//        if (empty($pTypeRef)) {
//            $pTypeRef = 'ind';
//        }
        $contactIndSearch = ContactFactory::search()
                ->filterByTypeRef($typeRef)
                ->join('contact_relationship', 'c_contact_id', $contactTableName, 'id', 'CR')
                ->filter('CR.p_contact_id', $contactOrg->getProperty('id'))
                ->filter('ind.first_name', $firstName);
        if (!empty($lastName)) {
            $contactIndSearch->filter('ind.last_name', $lastName);
        }
        $contactIndArray = $contactIndSearch->select();
        if (!empty($contactIndArray)) {
            $contactInd = $contactIndArray[0];
        } else {
            $contactInd = ContactFactory::buildNewModel('ind');
            $contactInd->setProperty('contact_ind.first_name', $firstName);
            $contactInd->setProperty('contact_ind.last_name', $lastName);
            if (!$contactInd->save()) {
                return NULL;
            }
            if (!ContactFactory::linkContactAndContact($contactOrg, $contactInd)) {
                return NULL;
            }
        }
        return $contactInd;
    }
    
    /** @return AbstractContactOrgFranchise[] */
    public static function getFranchises(){
        $search = static::search();
        $search->filterByTypeRef('franchise')
                ->orderBy('id');
        static::addFranchiseFiltersForFranchiseList($search);
        return $search->select();
    }
    
    public static function getFranchiseOptionsArray() {
        $options = array();
        $search = static::search();
        $search->filterByTypeRef('franchise')
                ->orderBy('id');
        static::addFranchiseFiltersForFranchiseList($search);
        $array = $search->select();
        if (!empty($array)) {
            foreach ($array as $franchise) {
                $options[$franchise->getId()] = $franchise->getProperty('contact_org.title');
            }
        }
        return $options;
    }
    
    public static function getChildContactArrayByParent(AbstractContact $parentContact) {
        $contactTableName = static::getDbPrefix() . 'contact';
        $search = static::search();
        $search->join('contact_relationship', 'c_contact_id', $contactTableName, 'id', 'REL')
                ->filter('REL.p_contact_id', $parentContact->getProperty('id'));
        return $search->select();
    }
    
    public static function getParentContactArrayByChild(AbstractContact $childContact) {
        $contactTableName = static::getDbPrefix() . 'contact';
        $search = static::search();
        $search->join('contact_relationship', 'p_contact_id', $contactTableName, 'id', 'REL')
                ->filter('REL.c_contact_id', $childContact->getProperty('id'));
        return $search->select();
    }

    /**
     * @param integer $userId
     * @param string $assignmentTypeRef
     * @return AbstractContact[]
     */
    public static function getAssignedContacts($userId = NULL, $assignmentTypeRef = NULL){
        if(empty($userId)){
            $userId = Login::getUserId();
        }
        $contactTableName = static::getDbPrefix() . 'contact';
        $search = static::search();
        $search->join('assigned_to_contact', 'contact_id', $contactTableName, 'id', 'ASS')
                ->filter('ASS.user_id', $userId);
        
        if(!empty($assignmentTypeRef)){
            $search->join('assigned_to_contact_type', 'id', 'ASS', 'assigned_to_contact_type_id', 'ASSTYPE')
                    ->filter('ASSTYPE.ref', $assignmentTypeRef);
        }
        $search->groupBy('id')
                ->orderBy('id');
        return $search->select();
    }

    public static function addFranchiseFiltersForFranchiseList(GI_DataSearch $search){
        $search->ignoreFranchise('contact');
        if(!Permission::verifyByRef('franchise_head_office')){
            $curUser = Login::getUser();
            if($curUser){
                $franchiseId = $curUser->getProperty('franchise_id');
                $search->filter('id', $franchiseId);
            }
        }
        return true;
    }
    
    public static function getModelArrayByContactQB(AbstractContactQB $contactQB, $typeRef = '') {
        $search = static::search();
        $search->filter('contact_qb_id', $contactQB->getProperty('id'));
        if (!empty($typeRef)) {
            $search->filterByTypeRef($typeRef);
        }
        return $search->orderBy('id')
                ->select();
    }
    
    public static function getIndividualByParentOrgAndUser(AbstractContactOrg $contactOrg, AbstractUser $user) {
        $search = static::search();
        $search->filterByTypeRef('ind');
        $search->filter('source_user_id', $user->getId());
        $tableName = $search->prefixTableName('contact');
        $search->join('contact_relationship', 'c_contact_id', $tableName, 'id', 'REL');
        $search->filter('REL.p_contact_id', $contactOrg->getId());
        
        $search->setItemsPerPage(1)
                ->setPageNumber(1)
                ->groupBy('id')
                ->orderBy('id', 'ASC');
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }
    
}
