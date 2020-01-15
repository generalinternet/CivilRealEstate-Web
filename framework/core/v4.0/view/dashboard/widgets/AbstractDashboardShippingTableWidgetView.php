<?php

/**
 * Description of AbstractDashboardShippingTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardShippingTableWidgetView extends AbstractDashboardWidgetView {

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('Shipping');
        $this->setHeaderIcon('shipping');
        $linkURL = GI_URLUtils::buildURL(array(
                    'controller' => 'order',
                    'action' => 'shipmentIndex',
                    'type' => 'sales',
                    'indexType' => 'shipping',
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
                'header_title' => 'Shipment Number',
                'method_name' => 'getShipmentNumber',
                'method_attributes' => true,
                'cell_url_method_name' => 'getViewURL',
            ),
           array(
            'header_title' => 'Recipient',
            'method_name' => 'getRecipientName',
        )
        );
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        $shippedStatus = OrderShipmentStatusFactory::getModelByRefAndTypeRef('shipped', 'sales');
        $search = OrderShipmentFactory::search();
        $search->filterByTypeRef('sales')
                ->filter('order_shipment_status_id', $shippedStatus->getId())
                ->setPageNumber(1)
                ->setItemsPerPage(WidgetService::getDashboardWidgetMaxTableRows())
                ->orderBy('last_mod', 'DESC');
        $sampleShipment = OrderShipmentFactory::buildNewModel('sales');
        if (!empty($sampleShipment)) {
            $sampleShipment->addCustomFiltersToDataSearch($search);
        }
        $models = $search->select();
        $uiTableView = new UITableView($models, $UITableCols);
        $this->addHTML($uiTableView->getHTMLView());
    }

    protected function determineIsViewable() {
        if (!dbConnection::isModuleInstalled('order') || !Permission::verifyByRef('view_so_shipping_table_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }

}
