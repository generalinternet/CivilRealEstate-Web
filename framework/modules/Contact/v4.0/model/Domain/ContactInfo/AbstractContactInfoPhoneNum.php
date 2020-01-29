<?php
/**
 * Description of AbstractContactInfoPhoneNum
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactInfoPhoneNum extends AbstractContactInfo {

    public function __construct($map) {
        parent::__construct($map);
        $this->setFieldPrefix('contact_info_phone_num_');
    }

    public function getFormView(GI_Form $form, $otherData = array()) {
        $formView = new ContactInfoPhoneNumFormView($form, $this, $otherData);
        return $formView;
    }

    public function setPropertiesFromForm(GI_Form $form) {
        $phoneNum = filter_input(INPUT_POST, $this->getFieldName('phone_num'));
        $this->setProperty('contact_info_phone_num.phone', $phoneNum);
        return parent::setPropertiesFromForm($form);
    }

    public function getDetailView() {
        $detailView = new ContactInfoPhoneNumDetailView($this);
        return $detailView;
    }
    
    public function setPropertiesFromModel(GI_Model $model) {
        $this->setProperty('contact_info_phone_num.phone', $model->getProperty('contact_info_phone_num.phone'));
        return true;
    }

}
