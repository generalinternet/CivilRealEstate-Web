<?php
/**
 * Description of AbstractDashboardActiveUsersTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardActiveUsersTableWidgetView extends AbstractDashboardWidgetView {

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('Active Users');
        $this->setHeaderIcon('activity');
//        $linkURL = GI_URLUtils::buildURL(array(
//                    'controller' => 'user',
//                    'action' => 'index',
//                    'search' => 1,
//                    'redirectAfterSearch' => 1
//        ));
//        $this->setLinkURL($linkURL);
//        $btnOptionsArray = array(
//            'title' => 'Search',
//            'icon' => 'search',
//            'link' => $linkURL,
//            'class_names' => 'open_modal_form',
//            'other_data' => ' data-modal-class="shadow_box_modal large_sized"',
//        );
//        $this->setBtnOptions($btnOptionsArray);
    }

    public function buildBodyContent() {
        $UITableCols = array();
        $tableColArrays = array(
            array(
                'header_title' => 'Name',
                'method_name' => 'getFullName',
                'cell_url_method_name' => 'getViewURL',
            ),
            array(
                'header_title' => 'Active',
                'method_name' => 'getLastLoginString',
                'method_attributes' => array(true),
                'css_class' => 'med_col',
                'css_header_class' => 'med_col'
            ),
        );
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        $search = UserFactory::searchRestricted();
        $thirtyMinsAgoDateTime = new DateTime(date('Y-m-d H:i:s'));
        $thirtyMinsAgoDateTime->modify("-30 minutes");
        $tableName = UserFactory::getDbPrefix() . 'user';
        $search->filterNotEqualTo('id', Login::getUserId())
                ->join('login', 'user_id', $tableName, 'id', 'LOGIN')
                ->setPageNumber(1)
                ->setItemsPerPage(WidgetService::getDashboardWidgetMaxTableRows())
                ->filterGreaterThan('LOGIN.last_mod', $thirtyMinsAgoDateTime->format('Y-m-d H:i:s'))
                ->orderBy('LOGIN.last_mod', 'DESC');
        $models = $search->select();
        $uiTableView = new UITableView($models, $UITableCols);
        $this->addHTML($uiTableView->getHTMLView());
    }

    protected function determineIsViewable() {
        if (!Permission::verifyByRef('view_active_users_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }
}

