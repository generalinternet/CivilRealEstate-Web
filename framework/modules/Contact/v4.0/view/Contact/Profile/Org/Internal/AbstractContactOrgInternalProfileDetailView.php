<?php

/**
 * Description of AbstractContactOrgInternalProfileDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactOrgInternalProfileDetailView extends AbstractContactOrgProfileDetailView {

    protected $addQuickbooksBar = false;

    protected function addAdvancedSections() {
        $this->addContactSectionAdvancedBlock();
        $this->addAdvancedSectionAdvancedBlock();
        $this->addPeopleSectionAdvancedBlock();
        if ($this->contact->getIsUserLinkedToThis(Login::getUser())) {
            $this->addMySettingsSectionAdvancedBlock();
        }
        $user = Login::getUser();
        if (!empty($user)) {
            $interfacePerspective = $user->getCurrentInterfacePerspective();
            if (!empty($interfacePerspective) && $interfacePerspective->getProperty('ref') === 'admin') {
                $this->addAdminAdvancedSections();
            }
        }
    }

    protected function addAdminAdvancedSections() {
        //   $this->addLabourRatesAdvancedBlock();
        if (dbConnection::isModuleInstalled('order')) {
            $this->addLocationsAdvancedBlock();
        }
        
    }

    protected function addLocationsAdvancedBlock() {
        $isOpenOnLoad = false;
        if ($this->curTab == 'locations') {
            $isOpenOnLoad = true;
        }
        $targetRef = 'locations';
        $headerIcon = 'building';
        $headerTitle = 'Locations';
        $classNames = $this->targetRefPrefix . $targetRef;
        $btnOptionsArray = array();
        
        //TODO - button
        
        $view = new GenericMainWindowView();
        $this->addLinkedContactsSection($view);
        $this->addAdvancedBlock($headerTitle, $view->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $classNames, $headerIcon, false);
    }
    
        protected function addLinkedContactsSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $linkedTypeRefs = array(
            'loc'=>'child'
        );
        $relationshipsDetailView = $this->contact->getContactRelationshipsDetailView($linkedTypeRefs);
        if (!empty($relationshipsDetailView)) {
            $view->addHTML('<div class="content_group ajax_link_wrap">');
            $view->addHTML($relationshipsDetailView->getHTMLView());
            $view->addHTML('</div>');
        }
    }

    protected function addLabourRatesAdvancedBlock() {
        $isOpenOnLoad = false;
        if ($this->curTab == 'labour_rates') {
            $isOpenOnLoad = true;
        }
        $targetRef = 'labour_rates';
        $advClassNames = $this->targetRefPrefix . $targetRef;
        $headerIcon = 'worker';
        $headerTitle = 'Labour Rates';
        $btnOptionsArray = array();
        $linkURL = GI_URLUtils::buildURL(array(
                    'controller' => 'project',
                    'action' => 'addContactLabourRate',
                    'contactId' => $this->contact->getId(),
        ));
        $btnOptionsArray[] = array(
            'type' => 'add',
            'title' => 'Labour Rate',
            'link' => $linkURL,
            'class_names' => 'open_modal_form',
        );

        $labourRateIndexView = ContactLabourRateFactory::getContactLabourRateIndex($this->contact);
        $labourRateIndexView->setAddWrap(false);
        $labourRateIndexView->setAddButtons(false);
        $labourRateIndexView->setAddTitle(false);
        $isAddToSidebar = true;
        //@todo move to add button to the sidebar
        $this->addAdvancedBlock($headerTitle, $labourRateIndexView->getHTMLView(), $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar);
    }

}