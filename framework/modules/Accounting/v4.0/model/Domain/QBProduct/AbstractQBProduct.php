<?php

/**
 * Description of AbstractQBProduct
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */
abstract class AbstractQBProduct extends GI_Model {

    protected $tableWrapId = 'qb_product_table';
    protected static $searchFormId = 'qb_product_search';

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
        $parentProductQBId = $qbData->ParentRef;
        $productTypeName = $qbData->Type;
        $incomeAcctQBId = $qbData->IncomeAccountRef;
        $isSubProduct = $qbData->SubItem;
        $taxCodeQBId = $qbData->SalesTaxCodeRef;

        $this->setProperty('qb_id', $qbId);
        $this->setProperty('name', $name);
        $this->setProperty('fully_qual_name', $fullyQualifiedName);
        if ($qbActive) {
            $this->setProperty('qb_active', 1);
        } else {
            $this->setProperty('qb_active', 0);
        }
        $this->setProperty('product_type_name', $productTypeName);
        $this->setProperty('income_acct_qb_id', $incomeAcctQBId);
        $this->setProperty('sales_tax_code_qb_id', $taxCodeQBId);
        if (!empty($isSubProduct)) {
            $this->setProperty('parent_product_qb_id', $parentProductQBId);
            $this->setProperty('is_sub_product', 1);
        } else {
            $this->setProperty('parent_product_qb_id', '');
            $this->setProperty('is_sub_product', 0);
        }

        if (!$this->save()) {
            return false;
        }
        return true;
    }

    public static function getIndexView($products = NULL, AbstractUITableView $uiTableView = NULL, AbstractQBProduct $sampleQBProduct = NULL, GI_SearchView $searchView = NULL) {
        $view = new QBProductIndexView($products, $uiTableView, $sampleQBProduct, $searchView);
        return $view;
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Product/Service',
                'method_name' => 'getName',
            ),
            array(
                'header_title' => 'Type',
                'method_attributes' => 'product_type_name'
            ),
            //TODO - more
            //parent product
            //income account, etc
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
    
    public function getName() {
        return $this->getProperty('name');
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
                    'action' => 'switchQBProductActiveStatus',
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
    
    public function getViewTitle($plural = true) {
        if (!$plural) {
            return 'Product/Service';
        }
        return 'Products/Services';
    }

    /**
     * @param GI_DataSearch $dataSearch
     * @param array $redirectArray
     * @return AbstractQBProductSearchView
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
                    'tab' => 'accounts'
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
     * @return AbstractQBProductSearchView
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
     * @return AbstractQBProductSearchView
     */
    protected static function buildSearchFormView(GI_Form $form, $searchValues = NULL) {
        $searchFormView = new QBProductSearchView($form, $searchValues);
        $searchFormView->setModelClass(get_called_class());
        return $searchFormView;
    }

    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL) {
        $accountName = $dataSearch->getSearchValue('product_name');
        if (!empty($accountName)) {
            static::addNameFilterToDataSearch($accountName, $dataSearch);
        }

        if (!is_null($form) && $form->wasSubmitted() && $form->validate()) {
            $accountName = filter_input(INPUT_POST, static::getSearchFieldName('search_product_name'));
            $dataSearch->setSearchValue('product_name', $accountName);
        }
        return true;
    }

    public static function addNameFilterToDataSearch($nameTerm, GI_DataSearch $dataSearch) {
        $dataSearch->filterLike('name', '%' . $nameTerm . '%');
    }

}
