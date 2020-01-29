<?php
/**
 * Description of AbstractContactOrgProfileFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactOrgProfileFormView extends AbstractContactProfileFormView {
    
    public function __construct(\GI_Form $form, \GI_Model $model = NULL) {
        parent::__construct($form, $model);
        $this->setListBarURL($model->getProfileListBarURL());
    }

    protected function buildSteps() {
        $this->addStepTitle(10, GI_StringUtils::getSVGIcon('contact_details', '26px', '26px') . 'Contact');
        //   $this->addStepTitle(3, GI_StringUtils::getSVGIcon('contacts', '26px', '26px') . 'People'); //TODO - for future feature where additional people can be linked
        $this->addStepTitle(20, GI_StringUtils::getSVGIcon('star', '26px', '26px') . 'Advanced');
        $contactCat = $this->model->getContactCat();
        if (!empty($contactCat) && $contactCat->getUsesPublicProfile()) {
            $this->addStepTitle(30, GI_StringUtils::getSVGIcon('megaphone', '26px', '26px') . 'Public Profile');
        }
        if ($this->model->getIsProfileComplete() && $this->model->getIsUserLinkedToThis(Login::getUser())) {
            $this->addStepTitle(40, GI_StringUtils::getSVGIcon('account', '26px', '26px') . 'My Settings');
        }
    }

    protected function buildFormBody() {
        switch ($this->curStep) {
            case 10:
                $this->buildContactForm();
                break;
            case 20:
                $this->buildAdvancedForm();
                break;
            case 30:
                $this->buildPublicProfileForm();
                break;
            case 40:
                $this->buildMySettingsForm();
                break;
            default:
        }
    }

    protected function buildPublicProfileForm() {
        $view = $this->model->getPublicProfileFormView($this->form);
        if (!empty($view)) {
            $uploader = $this->model->getPublicLogoUploader($this->form);
            $view->setLogoUploader($uploader);
            $view->buildFormBody();
        }
    }

    protected function buildContactForm() {
        $view = new ContactOrgProfileBasicFormView($this->form, $this->model);
        $view->buildFormBody(); 
    }
    
    protected function buildAdvancedForm() {
        $this->form->addHTML('<div class="auto_columns thirds">');
        $this->addCurrencyField();
        $this->addSubcategoryField();
        $this->form->addHTML('</div>');
    }
    
    protected function addCurrencyField($overwriteSettings = array()) {
        $value = $this->model->getProperty('default_currency_id');
        if (empty($value)) {
            $value = ProjectConfig::getDefaultCurrencyId();
        } 
        if (ProjectConfig::getHasMultipleCurrencies()) {
            $fieldSettings = GI_Form::overWriteSettings(array(
                'displayName' => 'Default Currency',
                'options' => CurrencyFactory::getOptionsArray('name'),
                'value' => $value,
                'required' => true,
                'hideNull' => true,
            ), $overwriteSettings);
            if (!empty($this->model->getProperty('contact_qb_id')) || $this->model->hasBills() || $this->model->hasInvoices()) {
                $fieldSettings['readOnly'] = true;
            } 
            $this->form->addField('default_currency_id', 'dropdown', $fieldSettings);
        } else {
            $this->form->addDefaultCurrencyField($value, 'default_currency_id');
        }
    }

    protected function addSubcategoryField($overwriteSettings = array()) {
        $tagFieldName = 'sub_cat_tag_id';
        $acURL = GI_URLUtils::buildURL(array(
                    'controller' => 'tag',
                    'action' => 'autocompTag',
                    'ajax' => 1,
                    'type' => 'contact_sub_cat',
                    'valueColumn' => 'id',
                    'autocompField' => $tagFieldName
                        ), false, true);
        $tagIdsString = '';
        $tag = $this->model->getSubCategoryTag();
        if (!empty($tag)) {
            $tagIdsString = $tag->getId();
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => Lang::getString('contact_sub_category'),
                    'placeHolder' => 'SELECT',
                    'autocompURL' => $acURL,
                    'autocompMinLength' => 0,
                    'autocompMultiple' => false,
                    'autocompLimit' => 1,
                    'value' => $tagIdsString
                        ), $overwriteSettings);
        $this->form->addField($tagFieldName, 'autocomplete', $fieldSettings);
    }



    protected function buildMySettingsForm() {
        $user = Login::getUser();
        if (empty($user)) {
            return;
        }
        $contactInd = ContactFactory::getIndividualByParentOrgAndUser($this->model, $user);
        if (empty($contactInd)) {
            return;
        }
        $contactInd->setParentContactOrg($this->model);
        if (empty($contactInd)) {
            return;
        }
        $formView = $contactInd->getProfileFormView($this->form, false);
        if (empty($formView)) {
            return;
        }
        $formView->buildFormBody();
    }

}
