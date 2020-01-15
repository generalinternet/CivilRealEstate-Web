<?php
/**
 * Description of GI_SidebarView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    1.0.0
 * @deprecated since 4.0.0 -> use AbstractSidebarView instead
 */
abstract class GI_SidebarView extends GI_View {
    
    /**
     * Sidebar categories array containing sidebar menus
     */
    protected $mainTitle = '';
    protected $addSidebar = true;
    protected $sidebarTitle = 'Sidebar';
    protected $sidebarSubtitle = 'Quick Commands';
    protected $sidebar = array();
    protected $iconWidthsidebarHeader = '26px';
    protected $iconHeightsidebarHeader = '26px';
    protected $iconWidthsidebarMenu = '18px';
    protected $iconHeightsidebarMenu = '18px';
    protected $targetRefPrefix = "target_ref_";
    protected $hasOverlay = true;
    
    public function __construct() {
        parent::__construct();
        $this->addCSS('resources/external/js/jquery.scrollbar/jquery.scrollbar.min.css');
        $this->addJS('resources/external/js/jquery.scrollbar/jquery.scrollbar.min.js');
        $this->addBodyClass('sidebar_view');
    }
    
    protected function setMainTitle($mainTitle){
        $this->mainTitle = $mainTitle;
    }
    
    public function setAddSidebar($addSidebar) {
        $this->addSidebar = $addSidebar;
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
    
    public function addSidebarCategoryMenu($categoryRef, $menuType, $menu) {
        $category = $this->getSidebarCategory($categoryRef);
        $categoryMenus = $category['menus'];
        $categoryMenus[$menuType] = $menu;
        //$this->setSidebarCategory($categoryRef, $category);
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
        }
        
        $this->setSidebarCategory($targetRef, $category);
    }
    
    protected function getSidebarHeaderTextWithSVGIcon($icon, $title, $classNames = 'left_icon') {
        return $this->getTextWithSVGIcon($icon, $title, $this->iconWidthsidebarHeader, $this->iconHeightsidebarHeader, $classNames);
    }
    
    protected function getSidebarMenuTextWithSVGIcon($icon, $title, $classNames = 'left_icon') {
        return $this->getTextWithSVGIcon($icon, $title, $this->iconWidthsidebarMenu, $this->iconHeightsidebarMenu, $classNames);
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->buildMainSection();
        if ($this->addSidebar) {
            $this->buildSidebarSection();
        }
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div id="main_content" class="flex_content');
        if (!$this->addSidebar) {
            $this->addHTML(' no_sidebar');
        }
        $this->addHTML('">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div><!--#main_content-->');
    }
    
    protected function buildMainSection() {
        $this->openMainSectionWrap();
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->closeMainSectionWrap();
    }
    
    protected function openMainSectionWrap() {
        $this->addHTML('<div id="main_section" class="main_column_left content_padding">');
    }
    
    protected function closeMainSectionWrap() {
        $this->addHTML('</div><!--#main_section-->');
    }
    
    protected function buildViewHeader() {
        //@override
    }
    
    protected function buildViewBody() {
        //@override
    }
    
    protected function buildViewFooter() {
        //@override
    }
    
    public function buildSidebarSection() {
        $this->openSidebarSectionWrap();
        $this->addSidebarTitle();
        $this->buildSidebarBody();
        $this->closeSidebarSectionWrap();
    }
    
    protected function openSidebarSectionWrap($classNames = 'main_column_right') {
        $this->addHTML('<aside id="sidebar_section" class="'.$classNames.'">');
            $this->addHTML('<div id="sidebar_body_wrap">');
    }
    
    protected function closeSidebarSectionWrap() {
            $this->addHTML('</div>');
        $this->addHTML('</aside>');
    }
    
    protected function addSidebarTitle() {
        $this->addHTML('<div class="sidebar_title">');
            if ($this->hasOverlay) {
                $this->addSidebarHeaderBtns();
            }
            $this->addHTML('<div class="title">' . $this->sidebarTitle . '</div><div class="subtitle">' . $this->sidebarSubtitle . '</div>');
        $this->addHTML('</div>');
    }
    
    protected function addSidebarHeaderBtns(){
        $this->addHTML('<div class="right_btns">');
            $this->addShowOverlayBtn();
        $this->addHTML('</div>');
    }
    
    protected function addShowOverlayBtn(){
        $this->addHTML('<a title="Show Overlay buttons" class="custom_btn open_overlay"><span class="icon_wrap"><span class="icon grid"></span></span></a>');
    }
    
    protected function buildSidebarBody() {
        $this->addHTML('<div id="sidebar_body">');
            //@todo: Need to add a scrollbar
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
                    
                    //Check if there is more than a details menu
                    $hasOtherMenu = false;
                    $btnDetailsOptionArray = array();
                    if (!empty($btnOptionsArray)) {
                        foreach ($btnOptionsArray as $btnOptionArray) {
                            if (!empty($btnOptionArray) && is_array($btnOptionArray) && isset($btnOptionArray['type']) && $btnOptionArray['type'] == 'details') {
                                $btnDetailsOptionArray[] = $btnOptionArray;
                                break;
                            } else {
                                $hasOtherMenu = true;
                            }
                        }
                    }
                    //if ($hasOtherMenu) { //Show side menus if there are more than a details menu
                        $headerTitle = $category['title'];
                        $headerIcon = $category['icon'];
                        $targetRef = $category['ref'];
                        $advClassNames = 'sidebar_cagegory';
                        $classNames = $category['class_names'];
                        if (!empty($classNames)) {
                            $advClassNames .= ' '.$classNames;
                        }
                        $advContent = $this->buildSidebarContents($btnOptionsArray);
                        //Show only the details menu on the header
                        if (empty($advContent)) {
                            $advClassNames .= ' no_menu';
                        }
                        $this->addAdvancedBlock($headerTitle, $advContent, $btnDetailsOptionArray, NULL, false, '', NULL, $targetRef, $advClassNames, $headerIcon);
                    //}
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
    
    public function beforeReturningView() {
        $this->buildView();
    }
}
