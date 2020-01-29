<?php
/**
 * Description of AbstractContactOrgProfilePublicProfileFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactOrgProfilePublicProfileFormView extends MainWindowView {
    
    protected $form;
    protected $contactOrg;
    protected $logoUploader = NULL;
    protected $formBuilt = false;
    
    protected $profileComplete = false;
    
    public function __construct(GI_Form $form, AbstractContactOrg $contactOrg) {
        parent::__construct();
        $this->form = $form;
        $this->contactOrg = $contactOrg;
        if (!empty($contactOrg->getProperty('profile_complete'))) {
            $this->profileComplete = true;
        }
    }
    
    protected function addViewBodyContent() {
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
    }
    
    public function setLogoUploader(AbstractGI_Uploader $logoUploader) {
        $this->logoUploader = $logoUploader;
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
        
    }

    public function buildFormBody() {        
        $this->form->addHTML('<div class="auto_columns">');
        $this->addLogoUploader();
        $this->addAccentColourPicker();
        $this->addBizNameField();
        $this->addOwnerNameField();
        $this->form->addHTML('<div class="column">');
        $this->addPhoneNumberForm();
        $this->addEmailAddressForm();
        $this->form->addHTML('</div>');
        $this->addAddressForm();
        $this->addWebsiteURLField();
        $this->addVideoURLField();
        $this->addBizDescriptionField();
        $this->form->addHTML('</div>');
    }

    protected function addLogoUploader() {
        if (!empty($this->logoUploader)) {
            $this->form->addHTML($this->logoUploader->getHTMLView());
        }
    }

    protected function addAccentColourPicker($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Accent Colour',
                    'value'=>$this->contactOrg->getPublicProfileAccentColour(),
                        ), $overwriteSettings);
        $this->form->addField('accent_colour', 'colour', $fieldSettings);
    }

    protected function addBizNameField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Business Name',
                    'value' => $this->contactOrg->getPublicProfileBusinessName(!$this->profileComplete),
                    'required' => true,
                        ), $overwriteSettings);
        $this->form->addField('pub_biz_name', 'text', $fieldSettings);
    }

    protected function addOwnerNameField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Owner’s Name',
                    'value' => $this->contactOrg->getPublicProfileOwnerName(!$this->profileComplete),
                        ), $overwriteSettings);
        $this->form->addField('pub_owner_name', 'text', $fieldSettings);
    }

    protected function addWebsiteURLField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Website Link',
                    'value' => $this->contactOrg->getPublicProfileWebsiteURL(),
                        ), $overwriteSettings);
        $this->form->addField('pub_website_url', 'text',$fieldSettings);
    }

    protected function addVideoURLField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Video Link',
                    'value' => $this->contactOrg->getPublicProfileVideoURL(),
                        ), $overwriteSettings);
        $this->form->addField('pub_video_url', 'text',$fieldSettings);
    }

    protected function addBizDescriptionField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Business Description',
            'value' => $this->contactOrg->getPublicProfileBusinessDescription(),
            'formElementClass' => 'full_width'
        ), $overwriteSettings);
        $this->form->addField('pub_biz_description', 'textarea', $fieldSettings);
    }
    
    protected function addEmailAddressForm() {
        $emailModel = $this->contactOrg->getPublicEmailModel(!$this->profileComplete);
        if (!empty($emailModel)) {
            $formView = $emailModel->getFormView($this->form);
            if (!empty($formView)) {
                $formView->hideTypeField(true);
                $this->form->addHTML('<div class="form_element">');
                $this->form->addHTML('<h4>Email</h4>');
                $formView->buildForm();
                $this->form->addHTML('</div>');
            }
        }
    }
    
    protected function addPhoneNumberForm() {
        $phoneModel = $this->contactOrg->getPublicPhoneModel(!$this->profileComplete);
        if (!empty($phoneModel)) {
            $formView = $phoneModel->getFormView($this->form);
            if (!empty($formView)) {
                $formView->hideTypeField(true);
                $this->form->addHTML('<div class="form_element">');
                $this->form->addHTML('<h4>Phone</h4>');
                $formView->buildForm();
                $this->form->addHTML('</div>');
            }
        }
    }
    
    protected function addAddressForm() {
        $addressModel = $this->contactOrg->getPublicAddressModel(!$this->profileComplete);
        if (!empty($addressModel)) {
            $formView = $addressModel->getFormView($this->form);
            if (!empty($formView)) {
                $formView->hideTypeField(true);
                $this->form->addHTML('<div class="form_element">');
                $this->form->addHTML('<h4>Address</h4>');
                $formView->buildForm();
                $this->form->addHTML('</div>');
            }
        }
    }

    protected function buildFormFooter() {
        //TODO - buttons
    }

}
