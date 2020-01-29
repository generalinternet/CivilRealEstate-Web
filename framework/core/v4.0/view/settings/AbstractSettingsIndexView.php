<?php
/**
 * Description of AbstractSettingsIndexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractSettingsIndexView extends MainWindowView {

    protected $currentTabKey = '';
    protected $title = 'Settings';

    public function __construct() {
        parent::__construct();
        $this->addSiteTitle('Settings');
        $this->setWindowTitle('Settings');
    }
    
    /**
     * @param string $currentTabKey
     */
    public function setCurrentTab($currentTabKey) {
        $this->currentTabKey = $currentTabKey;
    }


    protected function addViewBodyContent(){
        $this->buildTabs();
    }
 
    protected function buildTabs() {
        $tabs = array();
        $tabs['general'] = $this->buildGeneralSettingsTab();
        if (dbConnection::isModuleInstalled('project')) {
            $tabs['projects'] = $this->buildProjectsTab();
        }
        $tabWrap = new GenericTabWrapView($tabs);
        $this->addHTML($tabWrap->getHTMLView());
    }

    protected function buildGeneralSettingsTab() {
        $generalSettingsURL = GI_URLUtils::buildURL(array(
                    'controller' => '',
                    'action' => '',
                    'tabbed' => 1,
        ));
        $tab = new GenericTabView('General', $generalSettingsURL, true);
        return $tab;
    }
    
    protected function buildProjectsTab() {
        $accountsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'project',
                    'action' => 'viewNotificationSettings',
                    'tabbed' => 1,
        ));
        $tab = new GenericTabView('Projects', $accountsURL, true);
        return $tab;
    }

 
}
