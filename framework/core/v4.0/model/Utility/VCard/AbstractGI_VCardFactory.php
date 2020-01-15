<?php
/**
 * Description of AbstractGI_VCardFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractGI_VCardFactory {
    
    /**
     * @return AbstractGI_VCard
     */
    public static function buildVCard($fileName = NULL){
        $vCard = new GI_VCard($fileName);
        return $vCard;
    }
    
    /**
     * @param AbstractContact $contact
     * @return AbstractGI_VCard
     */
    public static function buildVCardFromContact(AbstractContact $contact){
        $vCard = static::buildVCard();
        //@todo set properties of vCard from $contact
        return $vCard;
    }
    
}
