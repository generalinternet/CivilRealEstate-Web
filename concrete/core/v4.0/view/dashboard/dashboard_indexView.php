<?php

class DashboardIndexView extends AbstractDashboardIndexView {
    
//    /**
//     * Sidebar
//     */
    protected function addCategoriesBeforeSidebar() {
        //Sidebar Elements
        $this->addSidebarElements();
        
        //Sidebar Layout
        $this->addSidebarLayout();
        
        //Sidebar Forms
        $this->addSidebarForms();
        
        //Sidebar Other
        $this->addSidebarOther();
    }
    
    protected function addSidebarElements() {
        $headerTitle = 'Elements';
        $headerIcon = 'bos';
        $this->openSidebarCategoryWrap($headerTitle, $headerIcon);
        $this->addSidebarElementsBtns();
        $this->closeSidebarCategoryWrap();
    }
    
    protected function addSidebarElementsBtns(){
        $this->addStaticElementPaletteBtn();
        $this->addStaticIconsBtn();
        $this->addStaticSVGIconsBtn();
        $this->addStaticProgressBarBtn();
    }
    
    protected function addStaticElementPaletteBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'elementPalette',
            ));
        $linkURLText = 'Element Palette';
        $linkIcon = 'pencil';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticIconsBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'icons',
            ));
        $linkURLText = 'Icons';
        $linkIcon = 'search';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticSVGIconsBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'svgIcons',
            ));
        $linkURLText = 'SVG Icons';
        $linkIcon = 'search';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticProgressBarBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'progressBar',
            ));
        $linkURLText = 'Progress Bar';
        $linkIcon = 'swap';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addSidebarLayout() {
        $headerTitle = 'Layout';
        $headerIcon = 'folder';
        $this->openSidebarCategoryWrap($headerTitle, $headerIcon);
        $this->addSidebarLayoutBtns();
        $this->closeSidebarCategoryWrap();
    }
    
    protected function addSidebarLayoutBtns(){
        $this->addStaticCatalogBtn();
        $this->addStaticTabsBtn();
        $this->addStaticColumnsBtn();
        $this->addStaticAutoColumnsBtn();
    }
    
    protected function addStaticCatalogBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'catalog',
            ));
        $linkURLText = 'Catalog';
        $linkIcon = 'content';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticTabsBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'tabs',
            ));
        $linkURLText = 'Tabs';
        $linkIcon = 'folder';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticColumnsBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'columns',
            ));
        $linkURLText = 'Columns';
        $linkIcon = 'grid';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticAutoColumnsBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'autoColumns',
            ));
        $linkURLText = 'Auto Columns';
        $linkIcon = 'menu';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addSidebarForms() {
        $headerTitle = 'Forms';
        $headerIcon = 'clipboard_text';
        $this->openSidebarCategoryWrap($headerTitle, $headerIcon);
        $this->addSidebarFormsBtns();
        $this->closeSidebarCategoryWrap();
    }
    
    protected function addSidebarFormsBtns(){
        $this->addStaticContactBtn();
        $this->addStaticSignHereBtn();
        $this->addStaticNotifyBtn();
    }
    
    protected function addStaticContactBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'contact',
            ));
        $linkURLText = 'Contact Form';
        $linkIcon = 'contacts';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticSignHereBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'signHere',
            ));
        $linkURLText = 'Sign Here';
        $linkIcon = 'clipboard_work_order';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn open_modal_form">'.$btnText.'</a>');
    }
    
    protected function addStaticNotifyBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'notify',
            ));
        $linkURLText = 'Notify';
        $linkIcon = 'info';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addSidebarOther() {
        $headerTitle = 'Other';
        $headerIcon = 'gear';
        $this->openSidebarCategoryWrap($headerTitle, $headerIcon);
        $this->addSidebarOtherBtns();
        $this->closeSidebarCategoryWrap();
    }
    
    protected function addSidebarOtherBtns(){
        $this->addStaticCodeBlockBtn();
        $this->addStaticColoursBtn();
        $this->addStaticGraphsBtn();
        $this->addStaticErrorCodesBtn();
    }
    
    protected function addStaticCodeBlockBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'codeBlock',
            ));
        $linkURLText = 'Code Block';
        $linkIcon = 'pencil';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticColoursBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'colours',
            ));
        $linkURLText = 'Colour Sorting';
        $linkIcon = 'flag';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticGraphsBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'graphs',
            ));
        $linkURLText = 'Graphs';
        $linkIcon = 'visible';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
    protected function addStaticErrorCodesBtn(){
        $linkURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'errorCodes',
            ));
        $linkURLText = 'Error Codes';
        $linkIcon = 'caution';
        $btnText = $this->getSidebarMenuTextWithSVGIcon($linkIcon, $linkURLText);
        $this->addHTML('<a href="' . $linkURL . '" title="'.$linkURLText.'" class="sidbar_btn">'.$btnText.'</a>');
    }
    
}
