<?php
/**
 * Description of AbstractDashboardRecentActivityTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardRecentActivityTableWidgetView extends AbstractDashboardWidgetView {

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('Recent Activity');
        $this->setHeaderIcon('activity');
        $linkURL = GI_URLUtils::buildURL(array(
                    'controller' => 'user',
                    'action' => 'activityIndex',
                    'id'=>login::getUserId(),
                    'ajax'=>1,
                    'tabbed'=>1,
        ));
        $this->setLinkURL($linkURL);
        $btnOptionsArray = array(
            'title' => 'More',
            'hoverTitle'=>'View All',
            'icon' => 'binoculars',
            'link' => $linkURL,
            'class_names' => 'open_modal_form',
            'other_data' => ' data-modal-class="medium_sized"',
        );
        $this->setBtnOptions($btnOptionsArray);
    }

    public function buildBodyContent() {
        $UITableCols = array();
        $tableColArrays = array(
            array(
                'header_title' => '',
                'method_name' => 'getIcon',
                'css_header_class' => 'icon_cell',
                'css_class' => 'icon_cell',
            ),
            array(
                'header_title' => 'Activity',
                'method_name' => 'getMemo',
                'cell_url_method_name' => 'getURL',
            ),
        );
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        $search = RecentActivityFactory::search();
        $search->filter('uid', Login::getUserId());
        $search->setPageNumber(1)
                ->setItemsPerPage(5)
                ->orderBy('last_mod', 'DESC');
        $models = $search->select();
        $uiTableView = new UITableView($models, $UITableCols);
        $this->addHTML($uiTableView->getHTMLView());
    }

    protected function determineIsViewable() {
        if (!Permission::verifyByRef('view_recent_activity_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }

}

