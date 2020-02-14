<?php

/**
 * Description of AbstractQBAccount
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */

abstract class AbstractQBAccount extends GI_Model {

    protected $currency;
    protected $parentAccount;
    
    protected $tableWrapId = 'qb_account_table';
    protected static $searchFormId = 'qb_account_search';

    /** @return string */
    public function getTableWrapId() {
        return $this->tableWrapId;
    }

    /** @return string */
    public static function getSearchFormId() {
        return static::$searchFormId;
    }

    public function updateFromQBData($qbData) {
        $qbId = $qbData->Id;
        $name = $qbData->Name;
        $fullyQualifiedName = $qbData->FullyQualifiedName;
        $qbActive = $qbData->Active;
        $number = $qbData->AcctNum;
        $accountType = $qbData->AccountType;
        $accountSubType = $qbData->AccountSubType;
        $isSubAccount = $qbData->SubAccount;
        $parentAccountQBId = $qbData->ParentRef;
        $currencyTitle = $qbData->CurrencyRef;
        $taxCodeQBId = $qbData->TaxCodeRef;
        
        $this->setProperty('qb_id',$qbId);
        $this->setProperty('name',$name);
        $this->setProperty('fully_qual_name',$fullyQualifiedName);
        if ($qbActive == 'false') {
            $this->setProperty('qb_active', 0);
            $this->setProperty('bos_active', 0);
        } else {
            $this->setProperty('qb_active', 1);
        }
        $this->setProperty('number', $number);
        if ($isSubAccount == 'true') {
            $this->setProperty('is_sub_account',1);
            $this->setProperty('parent_account_qb_id',$parentAccountQBId);
        } else {
            $this->setProperty('is_sub_account',0);
            $this->setProperty('parent_account_qb_id',NULL);
        }
        $this->setProperty('account_type_name',$accountType);
        $this->setProperty('account_sub_type_name',$accountSubType);
        $this->setProperty('tax_code_qb_id',$taxCodeQBId);
        
        $currencyModel = CurrencyFactory::getModelByRef(strtolower($currencyTitle));
        if (empty($currencyModel)) {
            return true;
        }
        $this->setProperty('currency_id', $currencyModel->getId());
        if (!$this->save()) {
            return false;
        }
        return true;
    }

    public static function getIndexView($accounts = NULL, AbstractUITableView $uiTableView = NULL, AbstractQBAccount $sampleAccount = NULL, GI_SearchView $searchView = NULL) {
        $view = new QBAccountIndexView($accounts, $uiTableView, $sampleAccount, $searchView);
        return $view;
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Account',
                'method_name' => 'getNumberAndName',
            ),
            array(
                'header_title' => 'Classification',
                'method_name' => 'getTypeTitle'
            ),
            array(
                'header_title' => 'Type',
                'method_attributes' => 'account_type_name'
            ),
            array(
                'header_title' => 'Currency',
                'method_name' => 'getCurrencyName',
                'css_header_class' => 'col_xsmall',
                'css_class' => 'col_xsmall'
            ),
            array(
                'header_title' => 'Parent Account',
                'method_name' => 'getParentAccountNumberAndName'
            ),
            array(
                'header_title' => 'Default Tax Code',
                'method_name' => 'getDefaultTaxCodeName'
            ),
            array(
                'header_title' => 'Dropdown Status',
                'method_name' => 'getDropdownOptionStatus'
            ),
            array(
                'header_title' => '',
                'method_name' => 'getRowDropdownMenu',
                'css_header_class' => 'col_xsmall',
                'css_class' => 'col_xsmall'
            )
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getNumberAndName() {
        $string = $this->getProperty('number');
        if (!empty($string)) {
            $string .= ' ';
        }
        $string .= $this->getProperty('name');
        return $string;
    }
    
    public function getCurrency() {
        if (empty($this->currency)) {
            $this->currency = CurrencyFactory::getModelById($this->getProperty('currency_id'));
        }
        return $this->currency;
    }
    
    public function getCurrencyName() {
      $currency = $this->getCurrency();
      if (!empty($currency)) {
          return $currency->getProperty('name');
      }
      return '';
    }
    
    public function getParentAccount() {
        if (empty($this->parentAccount)) {
            $parentAccountQBId = $this->getProperty('parent_account_qb_id');
            if (!empty($parentAccountQBId)) {
                $search = QBAccountFactory::search();
                $search->ignoreFranchise('qb_account');
                QBConnection::addFranchiseFilterToDataSearch($search);
                $search->filter('qb_id', $parentAccountQBId);
                $results = $search->select();
                if (!empty($results)) {
                    $this->parentAccount = $results[0];
                }
            }
        }
        return $this->parentAccount;
    }
    
    public function getParentAccountNumberAndName() {
        $parentAccount = $this->getParentAccount();
        if (!empty($parentAccount)) {
            return $parentAccount->getNumberAndName();
        }
        return '';
    }
    
    public function getDefaultTaxCodeName() {
        $defaultTaxCodeQBId = $this->getProperty('tax_code_qb_id');
        if (!empty($defaultTaxCodeQBId)) {
            $taxCodeDataArray = QBTaxCodeFactory::getQBTaxCodeDataById($defaultTaxCodeQBId);
            if (!empty($taxCodeDataArray) && isset($taxCodeDataArray['name'])) {
                return $taxCodeDataArray['name'];
            } 
        }
        return '';
    }
    
    public function getDropdownOptionStatus() {
        $statusTitle = 'Hidden';
        $icon = 'invisible';
        $iconColour = 'light_gray';
        if (!empty($this->getProperty('bos_active'))) {
            $statusTitle = 'Visible';
            $icon = 'visible';
            $iconColour = NULL;
        }
        if(!GI_CSV::csvExporting() && !empty($icon)){
            return GI_StringUtils::getIcon($icon . ' inline_block', false, $iconColour) . ' <span class="inline_block">' . $statusTitle . '</span>';
        }
        return $statusTitle;
    }

    public function getRowDropdownMenu() {
        $modifyURL = GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
                    'action' => 'switchQBAccountActiveStatus',
                    'id' => $this->getProperty('id'),
        ));
        if (!empty($this->getProperty('bos_active'))) {
            $modifyTitle = 'Hide';
        } else {
            $modifyTitle = 'Show';
        }
        $html = '<div class="dropdown_tooltip_menu">';
        $html .= '<span class="icon tooltip_arrow"></span>';
        $html .= '<ul class="tooltip_menu">';
        $html .= '<li><a href="' . $modifyURL . '" class="open_modal_form custom_btn" title="' . $modifyTitle . ' Account">' . GI_StringUtils::getIcon('pencil') . '<span class="btn_text">' . $modifyTitle . '</span></a></li>';
        $html .= '</ul>';
        $html .= '</div><!--.dropdown_tooltip_menu-->';
        return $html;
    }
    
            /**
     * @param GI_DataSearch $dataSearch
     * @param array $redirectArray
     * @return AbstractQBAccountSearchView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, $redirectArray = array()) {
        $form = new GI_Form(static::getSearchFormId());
        $searchView = static::getSearchFormView($form, $dataSearch);

        static::filterSearchForm($dataSearch, $form);

        if ($form->wasSubmitted() && $form->validate()) {
            $queryId = $dataSearch->getQueryId();

            if (empty($redirectArray)) {
                $redirectArray = array(
                    'controller' => 'admin',
                    'action' => 'QBSettingsIndex',
                    'tab'=>'accounts'
                    
                );
            }

            $redirectArray['queryId'] = $queryId;
            if (GI_URLUtils::getAttribute('ajax')) {
                $redirectArray['ajax'] = 1;
            }

            GI_URLUtils::redirect($redirectArray);
        }
        return $searchView;
    }
    
        /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return AbstractQBAccountSearchView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL) {
        $searchValues = array();
        if ($dataSearch) {
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = static::buildSearchFormView($form, $searchValues);
        return $searchView;
    }

    /**
     * @param GI_Form $form
     * @param array $searchValues
     * @return AbstractQBAccountSearchView
     */
    protected static function buildSearchFormView(GI_Form $form, $searchValues = NULL) {
        $searchFormView = new QBAccountSearchView($form, $searchValues);
        $searchFormView->setModelClass(get_called_class());
        return $searchFormView;
    }

    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL) {
        $accountName = $dataSearch->getSearchValue('account_name');
        if (!empty($accountName)) {
            static::addNameFilterToDataSearch($accountName, $dataSearch);
        }

        if (!is_null($form) && $form->wasSubmitted() && $form->validate()) {
            $accountName = filter_input(INPUT_POST, static::getSearchFieldName('search_account_name'));
            $dataSearch->setSearchValue('account_name', $accountName);
        }
        return true;
    }

    public static function addNameFilterToDataSearch($nameTerm, GI_DataSearch $dataSearch) {
            $dataSearch->filterLike('name', '%' . $nameTerm . '%');
    }

}
