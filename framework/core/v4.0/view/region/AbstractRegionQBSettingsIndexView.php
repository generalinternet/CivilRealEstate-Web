<?php
/**
 * Description of AbstractRegionQBSettingsIndexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    2.0.0
 */
abstract class AbstractRegionQBSettingsIndexView extends GI_View {

    protected $regions;
    protected $currentTabKey = '';
    protected $title = '';

    /**
     * @param AbstractRegion[] $regions
     */
    public function __construct($regions) {
        parent::__construct();
        $this->regions = $regions;
        if (!empty($regions)) {
            $this->title = $regions[0]->getQBSettingsIndexTitle();
            $this->addSiteTitle($this->title);
        }
    }

    /**
     * @param string $currentTabKey
     */
    public function setCurrentTab($currentTabKey) {
        $this->currentTabKey = $currentTabKey;
    }

    protected function buildView() {
    //    $this->addHTML('<div class="content_padding">');
        $this->addHTML('<h1>'.$this->title.'</h1>');
        $sampleRegion = $this->regions[0];
        $mainDescription = '';
        if (!empty($sampleRegion)) {
            $mainDescription = $sampleRegion->getQBSettingDescription('regional_main');
        }
        $this->addHTML('<p>'.$mainDescription.'</p>');
        $this->buildTabs();
  //      $this->addHTML('</div>');
    }

    protected function buildTabs() {
        $tabs = array();
        if (!empty($this->regions)) {
            foreach ($this->regions as $region) {
                $tab = $this->buildTab($region);
                $tabs[$region->getProperty('country_code') . '_' . $region->getProperty('region_code')] = $tab;
            }
        }
        $tabs[$this->currentTabKey]->setCurrent(true);
        $tabWrap = new GenericTabWrapView($tabs);
        $tabWrap->setTabWrapId('regional_tab_wrap');
        $this->addHTML($tabWrap->getHTMLView());
    }

    protected function buildTab(AbstractRegion $region) {
        $qbSettingsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
                    'action' => 'viewRegionQBSettings',
                    'id' => $region->getProperty('id'),
                    'tabbed'=>1
        ));
        $tab = new GenericTabView($region->getProperty('region_name'), $qbSettingsURL, true);
        return $tab;
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
