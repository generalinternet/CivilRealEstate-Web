<?php
/**
 * Description of AbstractSidebarView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractSidebarView extends MainWindowView {
    
    
    protected $mainTitle = '';
    protected $addSidebar = true;
    protected $sidebarTitle = 'Sidebar';
    protected $sidebarSubtitle = 'Quick Commands';
    protected $sidebarWrapId = 'main_window_sidebar_wrap';
    /**
     * Sidebar categories array containing sidebar menus
     */
    protected $sidebar = array();
    protected $iconWidthsidebarHeader = '26px';
    protected $iconHeightsidebarHeader = '26px';
    protected $iconWidthsidebarMenu = '18px';
    protected $iconHeightsidebarMenu = '18px';
    protected $targetRefPrefix = "target_ref_";
    protected $hasOverlay = true;
    protected $curTab = '';
    
    protected function setMainTitle($mainTitle){
        $this->mainTitle = $mainTitle;
    }
    
    public function setAddSidebar($addSidebar) {
        $this->addSidebar = $addSidebar;
    }
    
    public function getSidebarWrapId() {
        return $this->sidebarWrapId;
    }

    public function setSidebarWrapId($sidebarWrapId) {
        $this->sidebarWrapId = $sidebarWrapId;
    }

    protected function setIconHeightsidebarMenu($height){
        $this->iconHeightsidebarMenu = $height;
    }
    
    protected function setIconWidthsidebarHeader($width){
        $this->iconWidthsidebarHeader = $width;
    }
    
    protected function setIconHeightsidebarHeader($height){
        $this->iconHeightsidebarHeader = $height;
    }
    
    protected function setIconWidthsidebarMenu($width){
        $this->iconWidthsidebarMenu = $width;
    }
    
    protected function setSidebarTitle($title){
        $this->sidebarTitle = $title;
    }
    
    protected function setSidebarSubtitle($subtitle){
        $this->sidebarSubtitle = $subtitle;
    }
    
    public function getSidebarCategory($ref) {
        return $this->sidebar[$ref];
    }
    
    public function setSidebarCategory($categoryRef, $category) {
        $this->sidebar[$categoryRef] = $category;
    }
    
    public function hasOverlay($hasOverlay) {
        $this->hasOverlay = $hasOverlay;
    }
    
    public function setCurTab($curTab){
        $this->curTab = $curTab;
        return $this;
    }
    
    public function addSidebarCategoryMenu($categoryRef, $menuType, $menu) {
        $category = $this->getSidebarCategory($categoryRef);
        $categoryMenus = $category['menus'];
        $categoryMenus[$menuType] = $menu;
    }
    
    protected function addAdvancedBlockToSidebar($title, $targetRef, $btnOptionsArray, $headerIcon, $classNames = '') {
        $category = array(); 
        $category['ref'] = $targetRef;
        $category['title'] = $title;
        $category['icon'] = $headerIcon;
        $categoryClassNames = $this->targetRefPrefix.$targetRef;
        if (!empty($classNames)) {
            $categoryClassNames = ' '.$classNames;
        }
        $category['class_names'] = $categoryClassNames;
        $menus = array();
        if (!empty($btnOptionsArray) && is_array($btnOptionsArray)) {
            foreach ($btnOptionsArray as $key => $value) {
                $menus[$key] = $value;
            }
            $category['menus'] = $menus;
        } else {
            //Default is the details button
            $category['menus'] = array(array('type'=>'details'));
        }
        
        $this->setSidebarCategory($targetRef, $category);
    }
    
    protected function getSidebarHeaderTextWithSVGIcon($icon, $title, $classNames = 'left_icon') {
        return $this->getTextWithSVGIcon($icon, $title, $this->iconWidthsidebarHeader, $this->iconHeightsidebarHeader, $classNames);
    }
    
    protected function getSidebarMenuTextWithSVGIcon($icon, $title, $classNames = 'left_icon') {
        return $this->getTextWithSVGIcon($icon, $title, $this->iconWidthsidebarMenu, $this->iconHeightsidebarMenu, $classNames);
    }
    
    public function buildView(){
        $this->openOuterWrap()
                //main_window_view_wrap
                ->buildMainWindowViewWrap();
                //sidebar_wrap
                if ($this->addSidebar) {
                    $this->buildSidebar();
                }
        $this->closeOuterWrap();
        return $this;
    }
    
    protected function buildMainWindowViewWrap() {
        $this->openViewWrap();
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->closeViewWrap();
        return $this;
    }
    
    protected function openOuterWrap(){
        if(!$this->addOuterWrap){
            return $this;
        }
        $mainContentClassArray = array();
        if ($this->addSidebar) {
            $mainContentClassArray[] = 'has_sidebar';
        }
        if (!empty($mainContentClassArray)) {
            $this->setMainContentClass(implode(' ', $mainContentClassArray));
        }
        parent::openOuterWrap();
        return $this;
    }
    
    protected function openViewWrap(){
        parent::openViewWrap();
        if ($this->hasOverlay) {
            $this->buildOverlayView();
        }
        return $this;
    }
    
    protected function buildOverlayView() {
        $overlayTitle = $this->getOverlayTitle();
        $overlayWrap = new GenericOverlayWrapView($overlayTitle);
        if (!empty($this->curTab)) {
            $overlayWrap->isOpenOnLoad(false);
        }
        $this->addOverlayBtns($overlayWrap);
        $this->addHTML($overlayWrap->getHTMLView());
    }
    
    protected function getOverlayTitle() {
        return '<span class="inline_block">' . $this->mainTitle . '</span></span>';
    }
    
    protected function addOverlayBtns($overlayWrap) {
        return $this;
    }
    
    protected function buildSidebar() {
        $this->openSidebarWrap();
        $this->addSidebarTitle();
        $this->buildSidebarBody();
        $this->closeSidebarWrap();
    }

    protected function openSidebarWrap($classNames = '') {
        $this->addHTML('<aside');
        $sidebarWrapId = $this->getSidebarWrapId();
        if($sidebarWrapId){
            $this->addHTML(' id="'.$sidebarWrapId.'"');
        }
        $this->addHTML(' class="sidebar_wrap '.$classNames.'">');
            $this->addHTML('<div id="sidebar_body_wrap">');
    }
    
    protected function closeSidebarWrap() {
            $this->addHTML('</div>');
        $this->addHTML('</aside>');
    }
    
    protected function addSidebarTitle() {
        $this->addHTML('<div class="sidebar_title">');
            $this->addHTML('<div class="title">' . $this->sidebarTitle . '</div><div class="subtitle">' . $this->sidebarSubtitle . '</div>');
        $this->addHTML('</div>');
    }
    
    protected function addWindowBtns(){
        if ($this->hasOverlay) {
            $this->addShowOverlayBtn();
        }
        return $this;
    }
    
    protected function addShowOverlayBtn(){
        $this->addHTML('<a title="Show Overlay buttons" class="custom_btn open_overlay"><span class="icon_wrap"><span class="icon grid"></span></span></a>');
    }
    
    protected function buildSidebarBody() {
        $this->addHTML('<div id="sidebar_body">');
            $this->addHTML('<div id="sidebar_body_panel">');
                $this->addCategoriesBeforeSidebar();
                $this->addSidebarCategories();
                $this->addCategoriesAfterSidebar();
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    protected function addCategoriesBeforeSidebar() {
    }
    
    protected function openSidebarCategoryWrap($headerTitle, $headerIcon = NULL, $targetRef = NULL, $isOpenOnLoad = false){
        $this->addHTML('<div class="advanced sidebar_cagegory');
        if (!empty($targetRef)) {
           $this->addHTML(' link_to_target '.$this->targetRefPrefix.$targetRef); 
        }
        if ($isOpenOnLoad) {
           $this->addHTML(' open'); 
        }
        $this->addHTML('">');
           $this->addHTML('<div class="advanced_header">');
               $this->addHTML('<span class="advanced_btn_wrap">');
               $this->addHTML('<span class="custom_btn advanced_btn"'.(is_null($targetRef)? '':' data-adv-ref="'.$targetRef.'"').'><span class="icon_wrap"><span class="icon toggle_icon arrow_down border" data-open-icon="arrow_left border" data-close-icon="arrow_down border"></span></span></span>');
               $this->addHTML('</span>');
               $this->addHTML('<h2 class="advanced_title advanced_btn">');
               $this->addHTML($this->getTextWithSVGIcon($headerIcon, $headerTitle));    
               $this->addHTML('</h2>');
           $this->addHTML('</div>');

           $this->addHTML('<div class="advanced_content">');
    }
    
    protected function closeSidebarCategoryWrap(){
            $this->addHTML('</div><!--.advanced_content-->');
        $this->addHTML('</div><!--.advanced sidebar_cagegory-->');
    }
    
    protected function addGeneralInfoBtns(){
    }
    
    protected function addCategoriesAfterSidebar() {
    }
    protected function addSidebarCategories() {
        if (!empty($this->sidebar) && is_array($this->sidebar)) {
            foreach ($this->sidebar as $category) {
                if (is_array($category) && isset($category['menus'])) {
                    $btnOptionsArray = $category['menus'];
                    $btnDetailsOptionArray = array();
                    if (!empty($btnOptionsArray)) {
                        foreach ($btnOptionsArray as $btnOptionArray) {
                            $btnDetailsOptionArray[] = $btnOptionArray;
                        }
                    }
                    $headerTitle = $category['title'];
                    $headerIcon = $category['icon'];
                    $targetRef = $category['ref'];
                    $advClassNames = 'sidebar_cagegory';
                    $classNames = $category['class_names'];
                    if (!empty($classNames)) {
                        $advClassNames .= ' '.$classNames;
                    }
                    $advContent = $this->buildSidebarContents($btnDetailsOptionArray);
                    //Show only the details menu on the header
                    if (empty($advContent)) {
                        $advClassNames .= ' no_menu';
                    }
                    $advHeaderOptionsArray = array('type'=>'details'); //Only show the detail icon on the header

                    $this->addAdvancedBlock($headerTitle, $advContent, $advHeaderOptionsArray, NULL, false, '', NULL, $targetRef, $advClassNames, $headerIcon);
                }
            }
        }
    }
    
    protected function buildSidebarContents($btnOptionsArray) {
        $html = '';
        foreach ($btnOptionsArray as $btnOptionArray) {
            if (!empty($btnOptionArray) && is_array($btnOptionArray) && (!isset($btnOptionArray['type']) || $btnOptionArray['type'] != 'details')) {
                $html .= $this->buildSidebarMenu($btnOptionArray);
            }
        }
        return $html;
    }

    
    protected function buildSidebarMenu($btnOptionArray){
        if (isset($btnOptionArray['view'])) {
            //In case of a view, just return the view
            return '<div class="sidbar_btn">'.$btnOptionArray['view'].'</div>';
        } else {
            //In case of a link, build html
            $btnIcon = 'info';
            $btnTitle = '';
            $btnHoverTitle = '';
            $linkURL = '';
            if (isset($btnOptionArray['link'])) {
                $linkURL = $btnOptionArray['link'];
            }

            if (isset($btnOptionArray['title'])) {
                $btnTitle = $btnOptionArray['title'];
                if(empty($btnHoverTitle)){
                    $btnHoverTitle = $btnTitle;
                }
            }
            if(isset($btnOptionArray['hoverTitle'])){
                $btnHoverTitle = $btnOptionArray['hoverTitle'];
            }
            if (isset($btnOptionArray['icon'])) {
                $btnIcon = $btnOptionArray['icon'];
            }
            if (isset($btnOptionArray['icon_class'])){
                $btnIcon .= ' ' . $btnOptionArray['icon_class'];
            }
            $classNames = 'sidbar_btn';
            if (isset($btnOptionArray['class_names'])) {
                $classNames .= ' '.$btnOptionArray['class_names'];
            }

            $otherData = '';
            if (isset($btnOptionArray['other_data'])) { // data-*** = ""
                $otherData .= ' '.$btnOptionArray['other_data'];
            }

            $btnText = $this->getSidebarMenuTextWithSVGIcon($btnIcon, $btnTitle);
            return '<a href="' . $linkURL . '" title="'.$btnHoverTitle.'" class="'.$classNames.'" '.$otherData.'>'.$btnText.'</a>';
        }
        
    }
}
