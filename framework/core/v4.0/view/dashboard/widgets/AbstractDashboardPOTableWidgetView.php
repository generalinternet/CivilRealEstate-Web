<?php
/**
 * Description of AbstractDashboardPOTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardPOTableWidgetView extends AbstractDashboardWidgetView {

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('Purchase Orders');
        $this->setHeaderIcon('clipboard_text');
        $linkURL = GI_URLUtils::buildURL(array(
                    'controller' => 'order',
                    'action' => 'index',
                    'type' => 'purchase',
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
            array(
                'header_title' => 'Item Subtotal',
                'method_name' => 'getItemSubtotal',
                'method_attributes' => array(true),
            )
        );
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        $search = OrderFactory::search();
        $search->filterByTypeRef('purchase')
                ->setPageNumber(1)
                ->setItemsPerPage(WidgetService::getDashboardWidgetMaxTableRows())
                ->orderBy('inception', 'DESC');
        $sampleOrder = OrderFactory::buildNewModel('purchase');
        if (!empty($sampleOrder)) {
            $sampleOrder->addCustomFiltersToDataSearch($search);
        }
        $models = $search->select();
        $uiTableView = new UITableView($models, $UITableCols);
        $this->addHTML($uiTableView->getHTMLView());
    }
    
    protected function determineIsViewable() {
        if (!dbConnection::isModuleInstalled('order') || !Permission::verifyByRef('view_po_table_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }

}
