<?php
/**
 * Description of AbstractDashboardIndexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    2.0.0
 */
abstract class AbstractDashboardIndexView extends SidebarView {

    protected $sidebarTitle = 'Dashboard';
    protected $hasOverlay = false;
    protected $widgets = NULL;
    protected $numOfColumns = 3;
    protected $addViewHeader = false;

    /**
     * @param AbstractDashboardWidgetView $widgets
     */
    public function __construct($widgets) {
        parent::__construct();
        $this->widgets = $widgets;
        $this->numOfColumns = WidgetService::getDashboardIndexWidgetColumnCount();
        $this->addCSS('resources/external/js/morris/morris.css');
        $this->addJS('resources/external/js/raphael.min.js');
        $this->addJS('resources/external/js/morris/morris.min.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/custom_morris.js');
        $this->setListBarURL(GI_URLUtils::buildURL(array(
            'controller' => 'notification',
            'action' => 'index'
        )));
    }

    protected function openViewBody(){
        switch ($this->numOfColumns) {
            case 1:
                $class = '';
                break;
            case 2:
                $class = 'halves';
                break;
            case 3:
                $class = 'thirds';
                break;
            case 4:
                $class = 'quarters';
                break;
            case 5:
                $class = 'fifths';
                break;
            case 6:
                $class = 'sixths';
                break;
            default:
                $class = 'thirds';
                break;
        }
        $this->setViewBodyClass('grid_section no_header columns '.$class);
        parent::openViewBody();
    }

    protected function addViewBodyContent() {
        if (!empty($this->widgets)) {
            $widgetsByColumn = array();
            $count = count($this->widgets);
            $keys = array_keys($this->widgets);
            for ($i = 0; $i < $count; $i++) {
                $j = $i % $this->numOfColumns;
                if (!isset($widgetsByColumn[$j])) {
                    $widgetsByColumn[$j] = array();
                }

                $widgetsByColumn[$j][] = $this->widgets[$keys[$i]];
            }
            for ($k = 0; $k < $this->numOfColumns; $k++) {
                $this->addHTML('<div class="column grid_column">');
                if (isset($widgetsByColumn[$k])) {
                    $columnWidgets = $widgetsByColumn[$k];
                    if (!empty($columnWidgets)) {
                        foreach ($columnWidgets as $colWidget) {
                            $this->addHTML($colWidget->getHTMLView());
                        }
                    }
                }
                $this->addHTML('</div>');
            }
        }
    }


//    
//    protected function addContactsSection() {
//        $headerTitle = 'Contacts';
//        $headerIcon = 'contacts';
//        $gridContent = '';//@todo;
//        $linkURL = GI_URLUtils::buildURL(array(
//            'controller' => 'contact',
//            'action' => 'catIndex',
//            'search' => 1,
//            'redirectAfterSearch' => 1
//        ));
//        $btnOptionArray = array(
//                'title' => 'Search',
//                'icon' => 'search',
//                'link' => $linkURL,
//                'class_names' => 'open_modal_form',
//                'other_data' => ' data-modal-class="shadow_box_modal medium_sized"',
//            );
//        $this->addGridBox($headerTitle, $gridContent, $headerIcon, $btnOptionArray);
//    }

//    protected function addBillingSection() {
//        $headerTitle = 'Billing';
//        $headerIcon = 'bill';
//        $gridContent = '';//@todo;
//        $linkURL = GI_URLUtils::buildURL(array(
//            'controller' => 'billing',
//            'action' => 'index',
//            'search' => 1,
//            'redirectAfterSearch' => 1
//        ));
//        $btnOptionArray = array(
//                'title' => 'Search',
//                'icon' => 'search',
//                'link' => $linkURL,
//                'class_names' => 'open_modal_form',
//                'other_data' => ' data-modal-class="shadow_box_modal large_sized"',
//            );
//        $this->addGridBox($headerTitle, $gridContent, $headerIcon, $btnOptionArray);
//    }
//    
//    protected function addInvoiceSection() {
//        $headerTitle = 'Invoice';
//        $headerIcon = 'invoice';
//        $gridContent = '';//@todo;
//        $linkURL = GI_URLUtils::buildURL(array(
//            'controller' => 'invoice',
//            'action' => 'index',
//            'search' => 1,
//            'redirectAfterSearch' => 1
//        ));
//        $btnOptionArray = array(
//                'title' => 'Search',
//                'icon' => 'search',
//                'link' => $linkURL,
//                'class_names' => 'open_modal_form',
//                'other_data' => ' data-modal-class="shadow_box_modal large_sized"',
//            );
//        $this->addGridBox($headerTitle, $gridContent, $headerIcon, $btnOptionArray);
//    }
//    

//    

//    
//    protected function addWorkOrderSection() {
//        $headerTitle = 'Work Orders';
//        $headerIcon = 'clipboard_work_order';
//        $gridContent = '';//@todo;
//        $linkURL = GI_URLUtils::buildURL(array(
//            'controller' => 'project',
//            'action' => 'index',
//            'type' => 'work_order',
//            'search' => 1,
//            'redirectAfterSearch' => 1
//        ));
//        $btnOptionArray = array(
//                'title' => 'Search',
//                'icon' => 'search',
//                'link' => $linkURL,
//                'class_names' => 'open_modal_form',
//                'other_data' => ' data-modal-class="shadow_box_modal large_sized"',
//            );
//        $this->addGridBox($headerTitle, $gridContent, $headerIcon, $btnOptionArray);
//    }
//    

//    
//    protected function addQuoteSection() {
//        $headerTitle = 'Quotes';
//        $headerIcon = 'quote';
//        $gridContent = '';//@todo;
//        $linkURL = GI_URLUtils::buildURL(array(
//            'controller' => 'project',
//            'action' => 'indexQuote',
//            'type' => 'work_order',
//            'search' => 1,
//            'redirectAfterSearch' => 1
//        ));
//        $btnOptionArray = array(
//                'title' => 'Search',
//                'icon' => 'search',
//                'link' => $linkURL,
//                'class_names' => 'open_modal_form',
//                'other_data' => ' data-modal-class="shadow_box_modal large_sized"',
//            );
//        $this->addGridBox($headerTitle, $gridContent, $headerIcon, $btnOptionArray);
//    }
//    



}
