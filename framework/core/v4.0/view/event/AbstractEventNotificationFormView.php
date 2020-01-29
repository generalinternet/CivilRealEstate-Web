<?php

abstract class AbstractEventNotificationFormView extends GI_View {
    
    protected $form;
    protected $event;
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, AbstractEvent $event) {
        parent::__construct();
        $this->form = $form;
        $this->event = $event;
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
    
    protected function buildFormBody() {
        $this->addSystemRolesField();
        $this->addContextRolesField();
        $this->addUsersField();
    }

    protected function addSystemRolesField() {
        $subjectModel = $this->event->getSubjectModel();
        if (empty($subjectModel)) {
            return;
        }
        $value = $this->event->getLinkedRolesIdString(true);
        $fieldName = 'role_ids';
        $acURLAttrs = array(
            'controller' => 'role',
            'action' => 'autocompRole',
            'autocompField' => $fieldName,
            'ajax' => 1
        );
        $acURL = GI_URLUtils::buildURL($acURLAttrs, false, true);
        $this->form->addField($fieldName, 'autocomplete', array(
            'displayName' => 'System Role(s)',
            'placeHolder' => 'SELECT',
            'autocompURL' => $acURL,
            'autocompMinLength' => 0,
            'autocompMultiple' => true,
            'value' => $value,
            'fieldClass' => 'autofocus_off',
        ));
    }

    protected function addContextRolesField() {
        $subjectModel = $this->event->getSubjectModel();
        if (empty($subjectModel)) {
            return;
        }
        $value = $this->event->getLinkedContextRolesIdString(true);
        $fieldName = 'context_role_ids';
        $acURLAttrs = array(
            'controller' => 'core',
            'action' => 'autocompContextRole',
            'autocompField' => $fieldName,
            'tableName' => $subjectModel->getTableName(),
            'ajax' => 1
        );
        if (!empty($subjectModel->getId())) {
            $acURLAttrs['itemId'] = $subjectModel->getId();
        }
        $acURL = GI_URLUtils::buildURL($acURLAttrs, false, true);
        $this->form->addField($fieldName, 'autocomplete', array(
            'displayName' => $this->event->getTypeTitle() . ' Role(s)',
            'placeHolder' => 'SELECT',
            'autocompURL' => $acURL,
            'autocompMinLength' => 0,
            'autocompMultiple' => true,
            'value' => $value,
            'fieldClass'=>'autofocus_off',
        ));
    }

    protected function addUsersField() {
        $subjectModel = $this->event->getSubjectModel();
        if (empty($subjectModel)) {
            return;
        }
        $value = $this->event->getLinkedUsersIdString(true);
        $fieldName = 'user_ids';
        $acURLAttrs = array(
            'controller' => 'user',
            'action' => 'autocompUser',
            'autocompField' => $fieldName,
            'ajax' => 1
        );
        $acURL = GI_URLUtils::buildURL($acURLAttrs, false, true);
        $this->form->addField($fieldName, 'autocomplete', array(
            'displayName' => 'Name(s)',
            'placeHolder' => 'SELECT',
            'autocompURL' => $acURL,
            'autocompMinLength' => 0,
            'autocompMultiple' => true,
            'value' => $value,
            'fieldClass' => 'autofocus_off',
        ));
    }

    protected function buildFormFooter() {
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addSubmitBtn();
        $this->addCancelBtn();
        $this->form->addHTML('</div>');
    }

    public function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }

    public function addCancelBtn() {
        $this->form->addHTML('<span class="other_btn gray">Cancel</span>');
    }

    protected function buildView() {
        $this->openViewWrap();
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }


    public function beforeReturningView() {
        $this->buildView();
    }
    
    
    
}