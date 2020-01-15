<?php

/**
 * Description of AbstractDashboardContactEventHistoryTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardContactEventHistoryTableWidgetView extends AbstractDashboardWidgetView {
    
    protected $catType = 'category';

    public function __construct($ref) {
        parent::__construct($ref);
        
        if ($this->catType == 'category') {
            $this->setTitle('Contact History');
        } else {
            $typeModel = TypeModelFactory::getTypeModelByRef($this->catType, 'contact_cat_type');
            $this->setTitle($typeModel->getProperty('title').' History');
        }
        
        $this->setHeaderIcon('contacts');
        $linkURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contactevent',
                    'action' => 'index',
                    'catType' => $this->catType,
                    'search' => 1,
                    'redirectAfterSearch' => 1
        ));
        $this->setLinkURL($linkURL);
        $btnOptionsArray = array(
            'title' => 'Search',
            'hoverTitle'=>'Search',
            'icon' => 'search',
            'link' => $linkURL,
            'class_names' => 'open_modal_form',
            'other_data' => ' data-modal-class="shadow_box_modal large_sized"',
        );
        $this->setBtnOptions($btnOptionsArray);
    }

    public function buildBodyContent() {
        $UITableCols = array();
        $tableColArrays = array(
            array(
                'method_name' => 'getTypeIcon',
                'css_header_class' => 'icon_cell',
                'css_class' => 'icon_cell ',
                'cell_hover_title_method_name' => 'getTypeTitle',
                'header_hover_title' => 'Event Type',
            ),
            array(
                'header_title' => 'Contact',
                'method_name' => 'getContactName',
                'cell_url_method_name' => 'getContactViewURL',
            ),
            array(
                'header_title' => 'Title',
                'method_name' => 'getEventTitleWithLink',
                'css_class' => 'linked',
            ),
        );
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        $search = ContactEventFactory::search();
        $search->setPageNumber(1)
                ->setItemsPerPage(WidgetService::getDashboardWidgetMaxTableRows());
                
        $sampleContactEvent = ContactEventFactory::buildNewModel('event');
        if (!empty($sampleContactEvent)) {
            $sampleContactEvent->addSortingToDataSearch($search);
            if ($this->catType != 'category') {
                $sampleContactEvent->addContactTableToDataSearch($search);
                $search->join('contact_cat', 'contact_id', 'CONTACT', 'id', 'CAT')
                    ->join('contact_cat_type', 'id', 'CAT', 'contact_cat_type_id', 'CAT_TYPE')
                    ->filter('CAT_TYPE.ref', $this->catType)
                    ->groupBy('id');
                $sampleContactEvent->addContactCatJoinsToDataSearch($search);
                $sampleContactEvent->addCustomFiltersToDataSearch($search);
            }
        }
        $models = $search->select();
        $uiTableView = new UITableView($models, $UITableCols);
        $this->addHTML($uiTableView->getHTMLView());
    }
    
    protected function determineIsViewable() {
        if (!dbConnection::isModuleInstalled('contact') || !Permission::verifyByRef('view_contact_history_table_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }

}
