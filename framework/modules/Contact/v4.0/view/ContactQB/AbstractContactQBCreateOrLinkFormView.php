<?php
/**
 * Description of AbstractContactQBCreateOrLinkFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */

abstract class AbstractContactQBCreateOrLinkFormView extends GI_View {
    
    protected $contactQB;
    protected $form;
    protected $formBuilt = false;

    public function __construct(GI_Form $form, AbstractContactQB $contactQB) {
        parent::__construct();
        $this->form = $form;
        $this->contactQB = $contactQB;
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }

    protected function buildFormHeader() {
        $this->form->addHTML('<h1>Link/Create Contact(s)</h1>');
    }

    protected function buildFormBody() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->buildOrganizationSection();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->buildIndividualSection();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function buildOrganizationSection() {
        $this->form->addHTML('<h2>Contact - Organization</h2>');
        $companyName = $this->contactQB->getProperty('company');
        if (!empty($companyName)) {
            $options = array(
                'existing' => 'Link Existing',
                'new' => 'Create New',
                'ignore' => 'Ignore'
            );
            $this->form->addField('org_options', 'radio', array(
                'displayName' => '',
                'options' => $options,
                'value' => 'existing',
                'stayOn' => true,
                'fieldClass' => 'radio_toggler'
            ));
            $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="org_options" data-element="existing">');
            $this->addSelectOrganizationField();
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="org_options" data-element="new">');
            $this->addSelectContactCatField('org');
            $this->form->addHTML('</div>');
        } else {
            $this->form->addHTML('<p>There is insufficient data for an organization.</p>');
            $this->form->addField('org_options', 'hidden', array(
                'value' => 'ignore'
            ));
        }
    }

    protected function addSelectOrganizationField($overWriteSettings = array(), $overWriteAutocompProps = array()) {
        $contactOrgId = NULL;
        $contactOrg = $this->contactQB->getContactOrg();
        if (!empty($contactOrg)) {
            $contactOrgId = $contactOrg->getProperty('id');
        }
        $compatibleContactCatTypesArray = $this->contactQB->getCompatibleContactCatTypeRefs();
        $compatibleContactCatTypesArray[] = 'internal';
        $contactCatTypes = implode(',', $compatibleContactCatTypesArray);
        $autocompProps = array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => 'org',
            'ajax' => 1,
            'catTypeRefs' => $contactCatTypes,
        );
        foreach ($overWriteAutocompProps as $prop => $val) {
            $autocompProps[$prop] = $val;
        }
        $autocompURL = GI_URLUtils::buildURL($autocompProps);
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Select Organization',
                    'placeHolder' => 'start typing...',
                    'autocompURL' => $autocompURL,
                    'value'=>$contactOrgId,
                    'hideDescOnError' => false
                        ), $overWriteSettings);
        $this->form->addField('org_contact_id', 'autocomplete', $fieldSettings);
    }

    protected function buildIndividualSection() {
        $this->form->addHTML('<h2>Contact - Individual</h2>');
        $firstName = $this->contactQB->getProperty('first_name');
        if (!empty($firstName)) {
            $options = array(
                'existing' => 'Link Existing',
                'new' => 'Create New',
                'ignore' => 'Ignore'
            );
            $this->form->addField('ind_options', 'radio', array(
                'displayName' => '',
                'options' => $options,
                'value' => 'existing',
                'stayOn' => true,
                'fieldClass' => 'radio_toggler'
            ));
            $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="ind_options" data-element="existing">');
            $this->addSelectIndividualField();
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="ind_options" data-element="new">');
            $this->addSelectContactCatField('ind');
            $this->form->addHTML('</div>');
        } else {
            $this->form->addHTML('<p>There is insufficient data for an individual.</p>');
            $this->form->addField('ind_options', 'hidden', array(
                'value' => 'ignore'
            ));
        }
    }

    protected function addSelectIndividualField($overWriteSettings = array(), $overWriteAutocompProps = array()) {
        $contactIndId = NULL;
        $contactInd = $this->contactQB->getContactInd();
        if (!empty($contactInd)) {
            $contactIndId = $contactInd->getProperty('id');
        }
        $compatibleContactCatTypesArray = $this->contactQB->getCompatibleContactCatTypeRefs();
        $compatibleContactCatTypesArray[] = 'internal';
        $contactCatTypes = implode(',', $compatibleContactCatTypesArray);
        $autocompProps = array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => 'ind',
            'ajax' => 1,
            'catTypeRefs' => $contactCatTypes,
        );
        foreach ($overWriteAutocompProps as $prop => $val) {
            $autocompProps[$prop] = $val;
        }
        $autocompURL = GI_URLUtils::buildURL($autocompProps);
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Contact - Individual',
                    'placeHolder' => 'start typing...',
                    'autocompURL' => $autocompURL,
                    'value'=>$contactIndId,
                    'hideDescOnError' => false
                        ), $overWriteSettings);

        $this->form->addField('ind_contact_id', 'autocomplete', $fieldSettings);
    }

    protected function addSelectContactCatField($fieldPrefix) {
        $compatibleContactCatTypesArray = $this->contactQB->getCompatibleContactCatTypeRefs();
        if (count($compatibleContactCatTypesArray) > 1) {
            $contactCats = array();
            foreach ($compatibleContactCatTypesArray as $contactCatTypeRef) {
                $contactCats[] = ContactCatFactory::buildNewModel($contactCatTypeRef);
            }
            $options = array();
            foreach ($contactCats as $contactCat) {
                $options[$contactCat->getTypeRef()] = $contactCat->getTypeTitle();
            }
            $this->form->addField($fieldPrefix . '_contact_cat', 'radio', array(
                'displayName' => 'Category',
                'options' => $options,
                'stayOn' => true,
            ));
        } else {
            $this->form->addField($fieldPrefix . '_contact_cat', 'hidden', array(
                'value'=>$compatibleContactCatTypesArray[0]
            ));
        }
    }

    protected function buildFormFooter() {
        $this->addSubmitButton();
    }

    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn" tabindex="0" title="Save">Submit</span>');
    }

    protected function buildView() {
        $this->openViewWrap();
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    protected function buildViewHeader() {
        $this->addHTML($this->contactQB->getDetailView()->getHTMLView());
        $this->addHTML('<hr />');
    }
    
    protected function buildViewBody() {
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
    }
    
    protected function buildViewFooter() {
        
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    
}