<?php
/**
 * Description of AbstractDashboardReceivingTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardReceivingTableWidgetView extends AbstractDashboardWidgetView {

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('Receiving');
        $this->setHeaderIcon('receiving');
        $linkURL = GI_URLUtils::buildURL(array(
                    'controller' => 'order',
                    'action' => 'shipmentIndex',
                    'type' => 'purchase',
                    'indexType' => 'receiving',
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
                'header_title' => 'Vendor',
                'method_name' => 'getContactName',
            )
        );
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        $receivedStatus = OrderShipmentStatusFactory::getModelByRefAndTypeRef('received', 'purchase');
        $search = OrderShipmentFactory::search();
        $search->filterByTypeRef('purchase')
                ->filter('order_shipment_status_id', $receivedStatus->getId())
                ->setPageNumber(1)
                ->setItemsPerPage(WidgetService::getDashboardWidgetMaxTableRows())
                ->orderBy('last_mod', 'DESC');
        $sampleShipment = OrderShipmentFactory::buildNewModel('purchase');
        if (!empty($sampleShipment)) {
            $sampleShipment->addCustomFiltersToDataSearch($search);
        }
        $models = $search->select();
        $uiTableView = new UITableView($models, $UITableCols);
        $this->addHTML($uiTableView->getHTMLView());
    }

    protected function determineIsViewable() {
        if (!dbConnection::isModuleInstalled('order') || !Permission::verifyByRef('view_po_receiving_table_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }

}
