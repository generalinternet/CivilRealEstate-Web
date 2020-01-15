<?php
/**
 * Description of AbstractGenericTabWrapView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.2
 */
class AbstractGenericTabWrapView extends GI_View {
    
    /**
     * @var GenericTabView[]
     */
    protected $tabViews = array();
    protected $buildView = false;
    protected $sideLabels = false;
    protected $sideLabelsOnLeft = true;
    protected $tabWrapClass = array();
    protected $curTabIndex = 0;
    protected $tabWrapId = '';
    
    public function __construct($tabViews = array()) {
        $this->setTabViews($tabViews);
    }
    
    public function setTabViews($tabViews){
        foreach($tabViews as $tabView){
            $this->addTabView($tabView);
        }
        return $this;
    }
    
    public function addTabView(GenericTabView $tabView){
        $this->tabViews[$this->curTabIndex] = $tabView;
        $tabViewCount = $this->getTabViewCount();
        $tabView->setTabIndex($this->curTabIndex);
        $this->curTabIndex++;
        if($tabViewCount > 6){
            $this->setSideLabels(true);
        }
        return $this;
    }
    
    public function setSideLabels($sideLabels){
        $this->sideLabels = $sideLabels;
        return $this;
    }
    
    public function setSideLabelsOnLeft($sideLabelsOnLeft){
        $this->sideLabelsOnLeft = $sideLabelsOnLeft;
        return $this;
    }

    public function addTabWrapClass($class) {
        if (!in_array($class, $this->tabWrapClass)) {
            array_push($this->tabWrapClass, $class);
        }
        return $this;
    }
    
    public function setTabWrapId($tabWrapId){
        $this->tabWrapId = $tabWrapId;
        return $this;
    }
    
    public function getTabViewCount(){
        return count($this->tabViews);
    }
    
    protected function buildView(){
        if(!$this->buildView){
            $this->addTabWrapHTML();
        }
        $this->buildView = true;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    public function setCurrentTabByIndex($index){
        foreach($this->tabViews as $tabIndex => $tabView){
            if($tabIndex == $index){
                $tabView->setCurrent(true);
            } else {
                $tabView->setCurrent(false);
            }
        }
    }
    
    protected function getTabWrapClass(){
        $tabsWrapClass = implode(' ', $this->tabWrapClass);
        if($this->sideLabels){
            $tabsWrapClass .= ' side_labels';
        }
        if(!$this->sideLabelsOnLeft){
            $tabsWrapClass .= ' right_side_labels';
        }
        return $tabsWrapClass;
    }
    
    protected function addTabWrapHTML(){
        $tabsWrapClass = $this->getTabWrapClass();
        $tabWrapIdAttr = '';
        if($this->tabWrapId){
            $tabWrapIdAttr = 'id="' . $this->tabWrapId . '"';
        }
        $this->addHTML('<div ' . $tabWrapIdAttr . ' class="tabs_wrap ' . $tabsWrapClass . '">');
            $this->addTabsHTML();
        $this->addHTML('</div>');
    }
    
    protected function addTabsHTML(){
        if($this->sideLabels){
            if($this->sideLabelsOnLeft){
                $this->addSideTabLabels();
            }
            
            $this->addHTML('<div class="tab_contents">');
            foreach($this->tabViews as $tabView){
                $tabView->setAddLabel(false);
                $this->addHTML($tabView->getHTMLView());
            }
            $this->addHTML('</div>');
            
            if(!$this->sideLabelsOnLeft){
                $this->addSideTabLabels();
            }
        } else {
            foreach($this->tabViews as $tabView){
                $this->addHTML($tabView->getHTMLView());
            }
        }
    }
    
    protected function addSideTabLabels(){
        $this->addHTML('<div class="tab_labels">');
        foreach($this->tabViews as $tabView){
            $this->addHTML($tabView->getTabLabel());
        }
        $this->addHTML('</div>');
    }
    
}
