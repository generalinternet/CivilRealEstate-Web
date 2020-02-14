<?php
/**
 * Description of AbstractUpdateMultiInvItemQBSettingsFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */

abstract class AbstractUpdateMultiInvItemQBSettingsFormView extends GI_View {
    
    protected $form;
    protected $sampleInvItem;
    protected $formBuilt = false;
    protected $fullView = true;
    
    public function __construct(GI_Form $form, AbstractInvItem $sampleInvItem) {
        parent::__construct();
        $this->sampleInvItem = $sampleInvItem;
        $this->form = $form;
    }
    
    public function setFullView($fullView) {
        $this->fullView = $fullView;
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
        $this->form->addHTML('<h1>Update Existing Inventory Item Quickbooks Defaults</h1>');
    }
    
    protected function buildFormBody() {
        $this->addTypeAndBrandSection();
        $this->form->addHTML('<hr />');
        $this->addSettingsSection();
    }
    
    protected function addTypeAndBrandSection() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addTypeField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addBrandField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addTypeField() {
        $this->form->addField('inv_item_type_refs', 'select', array(
            'options' => InvItemFactory::getTypesArray(),
            'value' => NULL,
            'displayName' => 'Apply To Item Type(s)',
            'nullText'=>'All Item Types',
        ));
    }

    protected function addBrandField() {
        $this->form->addField('brand_ids', 'select', array(
            'options' => InvItemBrandFactory::getOptionsArray('title'),
            'value' => NULL,
            'displayName' => 'Apply To Item Brand(s)',
            'nullText'=>'All Item Brands'
        ));
    }

    protected function addSettingsSection() {
        $models = $this->sampleInvItem->getAllQBDefaultModels(true);
        if (!empty($models)) {
            $this->form->addHTML('<br>');
            $this->form->addHTML('<h3>Quickbooks Default Accounts</h3>');
            $this->form->addHTML("<p>These settings override default settings defined in the Admin->Quickbooks->Settings: 'General' section, and will be applied to each Inventory Item of type and brand defined above.");
            foreach ($models as $model) {
                $formView = $model->getFormView($this->form);
                $formView->buildForm();
            }
            $this->form->addHTML('<hr />');
        }
    }

    protected function buildFormFooter() {
        $this->addSubmitButton();
    }
    
    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn">Apply Settings</span>');
    }
    
    protected function buildView() {
        if ($this->fullView) {
            $this->openViewWrap();
        }
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
        if ($this->fullView) {
            $this->closeViewWrap();
        }
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
