<?php
/**
 * Description of AbstractContactInfoFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactInfoFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_info';
    protected static $models = array();

    /**
     * @param type $typeRef
     * @param type $map
     * @return AbstractContactInfo
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'address':
            case 'mailing_address':
            case 'shipping_address':
            case 'billing_address':
                $model = new ContactInfoAddress($map);
                break;
            case 'phone_num':
            case 'fax_num':
            case 'toll_free_phone_num':
            case 'direct_phone_num':
            case 'mobile_phone_num':
            case 'other_phone_num':
                $model = new ContactInfoPhoneNum($map);
                break;
            case 'email_address':
            case 'primary_email':
            case 'sales_email':
            case 'support_email':   
                $model = new ContactInfoEmailAddr($map);
                break;
            case 'contact_info':
            default:
                $model = new ContactInfo($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'contact_info':
                $typeRefs = array('contact_info');
                break;
            case 'address':
                $typeRefs = array('address', 'address');
                break;
            case 'mailing_address':
                $typeRefs = array('address', 'mailing_address');
                break;
            case 'shipping_address':
                $typeRefs = array('address', 'shipping_address');
                break;
            case 'billing_address':
                $typeRefs = array('address', 'billing_address');
                break;
            case 'phone_num':
                $typeRefs = array('phone_num', 'phone_num');
                break;
            case 'fax_num':
                $typeRefs = array('phone_num', 'fax_num');
                break;
            case 'toll_free_phone_num':
                $typeRefs = array('phone_num', 'toll_free_phone_num');
                break;
            case 'direct_phone_num':
                $typeRefs = array('phone_num', 'direct_phone_num');
                break;
            case 'mobile_phone_num':
                $typeRefs = array('phone_num', 'mobile_phone_num');
                break;
            case 'other_phone_num':
                $typeRefs = array('phone_num', 'other_phone_num');
                break;
            case 'email_address':
                $typeRefs = array('email_address', 'email_address');
                break;
            case 'primary_email':
                $typeRefs = array('email_address', 'primary_email');
                break;
            case 'sales_email':
                $typeRefs = array('email_address', 'sales_email');
                break;
            case 'support_email':
                $typeRefs = array('email_address', 'support_email');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * @param string $typeRef
     * @return AbstractContactInfo
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return AbstractContactInfo
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    public static function getContactInfosByContact(AbstractContact $contact, $typeRef = NULL, $idAsKey = false, $filterTypeGeneral = true, $qbLinkedOnly = false) {
        $contactId = $contact->getId();
        $contactInfoSearch = ContactInfoFactory::search()
               ->filter('contact_id', $contactId);
        
        if (!empty($typeRef)) {
            $contactInfoSearch->filterByTypeRef($typeRef, $filterTypeGeneral);
        }
        if ($qbLinkedOnly) {
            $contactInfoSearch->filter('qb_linked', 1);
        }
        $contactInfoSearch->orderBy('pos', 'ASC')
                ->orderBy('id', 'ASC');
        $contactInfoArray = $contactInfoSearch->select($idAsKey);
        return $contactInfoArray;
    }
    
    /**
     * @param AbstractContact $contact
     * @param string $preferedTypeRef
     * @return ContactInfoAddress[]
     */
    public static function getContactAddresses(AbstractContact $contact, $preferedTypeRef = NULL){
        $contactId = $contact->getId();
        $search = ContactInfoFactory::search()
                ->filter('contact_id', $contactId);
        $contactInfoTable = $search->prefixTableName('contact_info');
                $search->join('contact_info_address', 'parent_id', $contactInfoTable, 'id', 'ADDR')
                        ->join('contact_info_address_type', 'id', 'ADDR', 'contact_info_address_type_id', 'ADDRTYPE');
        
        if (!empty($preferedTypeRef)) {
            $typeCase = $search->newCase();
            $typeCase->filter('ADDRTYPE.ref', $preferedTypeRef)
                    ->setThen(1)
                    ->setElse(0);
            $search->orderByCase($typeCase, 'DESC');
        }
        $search->orderBy('pos', 'ASC');
        $addresses = $search->select();
        return $addresses;
    }

    public static function verifyEmailAddressLinkedToContact(AbstractContact $contact, $typeRef, $email) {
        $contactId = $contact->getId();
        $emailAddressResult = ContactInfoFactory::search()
                ->filterByTypeRef($typeRef)
                ->filter('contact_id', $contactId)
                ->filter('email_address.email_address', $email)
                ->select();
        if (!empty($emailAddressResult)) {
            $emailAddress = $emailAddressResult[0];
        } else {
            $emailAddress = ContactInfoFactory::buildNewModel($typeRef);
            $emailAddress->setProperty('contact_info_email_addr.email_address', $email);
            $emailAddress->setProperty('contact_id', $contactId);
            if (!$emailAddress->save()) {
                return NULL;
            }
        }
        return $emailAddress;
    }

    public static function verifyAddressLinkedToContact(AbstractContact $contact, $typeRef, $addrStreet, $addrCity, $addrRegion, $addrCountry, $addrCode, $addrStreetTwo) {
        $contactId = $contact->getId();
        $addressSearch = ContactInfoFactory::search()
                ->filterByTypeRef($typeRef)
                ->filter('contact_id', $contactId)
                ->filter('address.addr_region', $addrRegion);
        if (!empty($addrStreet)) {
            $addressSearch->filter('address.addr_street', $addrStreet);
        }
        if (!empty($addrStreetTwo)) {
            $addressSearch->filter('address.addr_street_two', $addrStreetTwo);
        }
        if (!empty($addrCity)) {
            $addressSearch->filter('address.addr_city', $addrCity);
        }
        if (!empty($addrCountry)) {
            $addressSearch->filter('address.addr_country', $addrCountry);
        }
        if (!empty($addrCode)) {
            $addressSearch->filter('address.addr_code', $addrCode);
        }
        $addressResult = $addressSearch->select();
        if (!empty($addressResult)) {
            $address = $addressResult[0];
        } else {
            $address = ContactInfoFactory::buildNewModel($typeRef);
            $address->setProperty('contact_info_address.addr_street', $addrStreet);
            $address->setProperty('contact_info_address.addr_street_two', $addrStreetTwo);
            $address->setProperty('contact_info_address.addr_city', $addrCity);
            $address->setProperty('contact_info_address.addr_region', $addrRegion);
            $address->setProperty('contact_info_address.addr_country', $addrCountry);
            $address->setProperty('contact_info_address.addr_code', $addrCode);
            $address->setProperty('contact_id', $contactId);
            if (!$address->save()) {
                return NULL;
            }
        }
        return $address;
    }

    public static function createBasicContactInfosForContact(AbstractContact $contact, $contactInfoTypeRefs) {
        foreach ($contactInfoTypeRefs as $contactInfoTypeRef) {
            $contactHasContactInfo = $contact->getHasAtLeastOneContactInfo($contactInfoTypeRef);
            if (!$contactHasContactInfo) {
                $contactInfo = ContactInfoFactory::buildNewModel($contactInfoTypeRef);
                if (empty($contactInfo)) {
                    return false;
                }
                $contactInfo->setRequiredDefaultProperties();
                $contactInfo->setProperty('contact_id', $contact->getId());
                if (!($contactInfo->save())) {
                    return false;
                }
            }
        }
        return true;
    }

}
