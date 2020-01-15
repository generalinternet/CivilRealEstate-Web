<?php
/**
 * Description of AbstractDashboardSOTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardSOTableWidgetView extends AbstractDashboardWidgetView {

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('Sales Orders');
        $this->setHeaderIcon('clipboard_money');
        $linkURL = GI_URLUtils::buildURL(array(
                    'controller' => 'order',
                    'action' => 'index',
                    'type' => 'sales',
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
                'header_title' => 'Order Number',
                'method_name' => 'getOrderNumber',
                'cell_url_method_name' => 'getViewURL',
            ),
//            array(
//                'header_title' => 'Order Total',
//                'method_name' => 'getSortableTotal',
//                'method_attributes' => array(true),
//            ),
            array(
                'header_title' => 'Order Total',
                'method_name' => 'getTotal',
                'method_attributes' => array(true),
            )
        );
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        $search = OrderFactory::search();
        $search->filterByTypeRef('sales')
                ->setPageNumber(1)
                ->setItemsPerPage(WidgetService::getDashboardWidgetMaxTableRows())
                ->orderBy('inception', 'DESC');
        $sampleOrder = OrderFactory::buildNewModel('sales');
        if (!empty($sampleOrder)) {
            $sampleOrder->addCustomFiltersToDataSearch($search);
        }
        $models = $search->select();
        $uiTableView = new UITableView($models, $UITableCols);
        $this->addHTML($uiTableView->getHTMLView());
        $this->addHTML('</div>');
    }

    protected function determineIsViewable() {
        if (!dbConnection::isModuleInstalled('order') || !Permission::verifyByRef('view_so_table_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }

}
