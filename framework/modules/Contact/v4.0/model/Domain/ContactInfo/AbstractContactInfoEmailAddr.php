<?php
/**
 * Description of AbstractContactInfoEmailAddr
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactInfoEmailAddr extends AbstractContactInfo {

    public function __construct($map) {
        parent::__construct($map);
        $this->setFieldPrefix('contact_info_email_address_');
    }

    public function getFormView(GI_Form $form, $otherData = array()) {
        $formView = new ContactInfoEmailAddrFormView($form, $this, $otherData);
        return $formView;
    }

    public function setPropertiesFromForm(GI_Form $form) {
        $email = filter_input(INPUT_POST, $this->getFieldName('email'));
        $this->setProperty('contact_info_email_addr.email_address', $email);
        return parent::setPropertiesFromForm($form);
    }

    public function getDetailView() {
        $detailView = new ContactInfoEmailAddrDetailView($this);
        return $detailView;
    }
    
    public function setPropertiesFromModel(GI_Model $model) {
        $this->setProperty('contact_info_email_addr.email_address', $model->getProperty('contact_info_email_addr.email_address'));
        return true;
    }

}
