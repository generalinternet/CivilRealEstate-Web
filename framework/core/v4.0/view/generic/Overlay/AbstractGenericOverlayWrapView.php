<?php
/**
 * Description of AbstractGenericOverlayWrapView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.2
 */
class AbstractGenericOverlayWrapView extends GI_View {
    
    /**
     * @var GenericOverlayGridView[]
     */
    protected $overlayGridViews = array();
    protected $overlayTitle = '';
    protected $buildView = false;
    protected $overlayWrapClass = array();
    protected $curOverlayGridIndex = 0;
    protected $openOnLoad = false;
    
    public function __construct($overlayTitle = '', $overlayGridViews = array()) {
        $this->setOverlayGridViews($overlayGridViews);
        $this->overlayTitle = $overlayTitle;
    }
    
    public function setOverlayGridViews($overlayViewGrids){
        foreach($overlayViewGrids as $overlayViewGrid){
            $this->addOverlayView($overlayViewGrid);
        }
        return $this;
    }
    
    public function isOpenOnLoad($openOnLoad){
        $this->openOnLoad = $openOnLoad;
        return $this;
    }
    
    public function addOverlayView(GenericOverlayGridView $overlayViewGrid){
        $this->overlayGridViews[$this->curOverlayGridIndex] = $overlayViewGrid;
        $overlayViewGrid->setGridIndex($this->curOverlayGridIndex);
        $this->curOverlayGridIndex++;
        return $this;
    }

    public function addOverlayWrapClass($class) {
        if (!in_array($class, $this->overlayWrapClass)) {
            array_push($this->overlayWrapClass, $class);
        }
        return $this;
    }
    
    protected function buildView(){
        if(!$this->buildView){
            $this->addOverlayWrapHTML();
        }
        $this->buildView = true;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

    protected function getOverlayWrapClass(){
        $overlayWrapClass = implode(' ', $this->overlayWrapClass);
        return $overlayWrapClass;
    }
    
    protected function addOverlayWrapHTML(){
        $overlayWrapClass = $this->getOverlayWrapClass();
        
        $this->addHTML('<div id="overlay_grids_wrap" class="');
        if (!$this->openOnLoad) {
            $this->addHTML('hide_on_load');
        }
        
        $this->addHTML(' '.$overlayWrapClass.'">');
            $this->addOverlayCloseBtn();
            $this->addOverlayTitle();
            $this->addOverlaysHTML();
        $this->addHTML('</div>');
    }
    
    protected function addOverlayCloseBtn(){
        $iconView = new IconView('eks');
        $this->addHTML('<span class="custom_btn close_overlay">'.$iconView->getHTMLView().'</span>');
    }
    
    protected function addOverlayTitle(){
        $this->addHTML('<div class="overlay_title">'. $this->overlayTitle.'</div>');
    }
    
    protected function addOverlaysHTML(){
        $this->addHTML('<div id="overlay_grids">');
            foreach($this->overlayGridViews as $overlayViewGrid){
                $this->addHTML($overlayViewGrid->getHTMLView());
            }
        $this->addHTML('</div>');
    }
}
