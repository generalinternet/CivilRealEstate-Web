<?php
/**
 * Description of AbstractRegionEcoFeeIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractRegionEcoFeeIndexView extends MainWindowView {

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
            $this->title = $regions[0]->getEcoFeeIndexTitle();
            $this->addSiteTitle($this->title);
            $this->setWindowTitle($this->title);
        }
        
    }

    /**
     * @param string $currentTabKey
     */
    public function setCurrentTab($currentTabKey) {
        $this->currentTabKey = $currentTabKey;
    }

    public function addViewBodyContent() {
        $this->buildTabs();
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
        $this->addHTML($tabWrap->getHTMLView());
    }

    protected function buildTab(AbstractRegion $region) {
        $ecoFeesURL = GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'viewEcoFees',
                    'id' => $region->getProperty('id'),
                    'tabbed'=>1
        ));
        $tab = new GenericTabView($region->getProperty('region_name'), $ecoFeesURL, true);
        return $tab;
    }
}
