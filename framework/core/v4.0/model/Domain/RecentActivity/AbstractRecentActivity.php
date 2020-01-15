<?php
/**
 * Description of AbstractRecentActivity
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    2.0.0
 */
abstract class AbstractRecentActivity extends GI_Model {

    protected $tableWrapId = 'recent_activity_table';

    public function getTableWrapId() {
        return $this->tableWrapId;
    }

    public function getIconClass() {
        return $this->getProperty('icon_class');
    }

    public function getIcon() {
        $iconClass = $this->getIconClass();
        return '<span class="icon_wrap"><span class="icon gray ' . $iconClass . '"></span></span>';
    }

    public function getURL() {
        return $this->getProperty('url');
    }

    public function getMemo() {
        return $this->getProperty('memo');
    }

    public function equals(AbstractRecentActivity $recentActivity) {
        if (!($this->getProperty('table_name') === $recentActivity->getProperty('table_name'))) {
            return false;
        }
        if (!($this->getProperty('item_id') === $recentActivity->getProperty('item_id'))) {
            return false;
        }
        if (!($this->getProperty('url') === $recentActivity->getProperty('url'))) {
            return false;
        }
        if (!($this->getProperty('memo') === $recentActivity->getProperty('memo'))) {
            return false;
        }
        if (!($this->getProperty('icon_class') === $recentActivity->getProperty('icon_class'))) {
            return false;
        }
        return true;
    }

    public function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        $userId = $userId = $dataSearch->getSearchValue('user_id');
        if (!Permission::verifyByRef('view_other_user_activity')) {
            $userId = Login::getUserId();
        }
        if (!empty($userId)) {
            static::addUserFilterToDataSearch($userId, $dataSearch);
        }
        return $dataSearch;
    }

    public static function addUserFilterToDataSearch($userId, GI_DataSearch $dataSearch) {
        $dataSearch->filter('uid', $userId);
        return $dataSearch;
    }
    
    public function addOrderBysToDataSearch(GI_DataSearch $dataSearch) {
        $dataSearch->orderBy('last_mod', 'DESC');
        return $dataSearch;
    }
    
    public function getIndexTitle() {
        return 'Recent Activity';
    }

    public static function getUITableCols() {
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
            array(
                'header_title' => '',
                'method_name' => 'getLastMod',
                'method_attributes' => array(true),
            ),
        );
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public static function getUIRolodexCols() {
        $tableColArrays = array(
            //Icon
            array(
                'method_name' => 'getIcon',
                'css_class' => 'icon_cell',
            ),
            //Activity
            array(
                'method_name' => 'getMemo',
            ),
            //Last modified time
            array(
                'method_name' => 'getLastMod',
                'method_attributes' => array(true),
            ),
        );
        $UIRolodexCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UIRolodexCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UIRolodexCols;
    }
    
    public function getLastMod($formatForDisplay = false) {
        $lastMod = $this->getProperty('last_mod');
        if ($formatForDisplay) {
            $lastMod = GI_Time::formatDateTimeForDisplay($lastMod);
        }
        return $lastMod;
    }
    
    public function getViewURL() {
        return $this->getProperty('url');
    }
    
        /**
     * @param GI_DataSearch $dataSearch
     * @param array $redirectArray
     * @return RecentActivitySearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, &$redirectArray = array()){
        $form = new GI_Form('recent_activity_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'user',
                    'action' => 'activityIndex'
                );
            }
            
            $redirectArray['queryId'] = $queryId;
            if(GI_URLUtils::getAttribute('ajax')){
                if(GI_URLUtils::getAttribute('redirectAfterSearch')){
                    //Set new Url for search
                    unset($redirectArray['ajax']);
                    $redirectArray['fullView'] = 1;
                    $redirectArray['newUrl'] = GI_URLUtils::buildURL($redirectArray);
                    $redirectArray['newUrlTargetId'] = 'list_bar';
                    $redirectArray['jqueryAction'] = 'clearMainPanel();';
                } else {
                    $redirectArray['ajax'] = 1;
                    GI_URLUtils::redirect($redirectArray);
                }
            } else {
                GI_URLUtils::redirect($redirectArray);
            }
        }
        return $searchView;
    }
    
    
        /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL) {
//        $userId = $dataSearch->getSearchValue('user_id');
//        if (!empty($userId)) {
//            static::addUserFilterToDataSearch($userId, $dataSearch);
//        }

        if (!is_null($form) && $form->wasSubmitted() && $form->validate()) {
            $dataSearch->clearSearchValues();
                $dataSearch->setSearchValue('search_type', 'advanced');
                
                $userId = filter_input(INPUT_POST, 'search_user_id');
                $dataSearch->setSearchValue('user_id', $userId);
        }
        return true;
    }
    
        /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return RecentActivitySearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new RecentActivitySearchFormView($form, $searchValues);
        return $searchView;
    }
    
    public function getViewTitle($plural = true) {
        if ($plural) {
            return 'Recent Activities';
        }
        return 'Recent Activity';
    }

}
