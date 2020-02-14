<?php
/**
 * Description of AbstractAccReportSalesBySalesperson
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportSalesBySalesperson extends AbstractAccReport {

    protected $totals = NULL;
    protected $dailyDates = NULL;
    protected $weekDates = NULL;
    protected $monthDates = NULL;
    protected $ytdDates = NULL;
    protected $cacheTTL = 43200; //12 hours
    
    public function __construct($typeRef, DateTime $startDate, DateTime $endDate) {
        $endDate = new DateTime(date('Y-m-d'));
        parent::__construct($typeRef, $startDate, $endDate);
    }

    public function getTitle() {
        return 'Sales by Salesperson';
    }

    public function getDescription() {
        return 'Shows total sales for each salesperson so you can see which ones generate the most revenue for you.';
    }

    public function getColour() {
        return 'E5E500';
    }

    public function getInitials() {
        return 'SS';
    }

    public function getDetailView() {
        return new AccReportSalesBySalespersonDetailView($this);
    }
    
    public function getTotals() {
        if (!$this->reportBuilt) {
            $this->buildReport();
        }
        return $this->totals;
    }

    public function buildReport() {
        if (!$this->reportBuilt) {
            $unsortedTotals = array();
            $rankingArray = array();
            $sortedTotals = array();
            $userTableName = UserFactory::getDbPrefix() . 'user';
            $userSearch = UserFactory::search();
            $userSearch->join('assigned_to_contact', 'user_id', $userTableName, 'id', 'ASSTO')
                    ->filterNotNull('ASSTO.id');
            $assignedToType = TypeModelFactory::getTypeModelByRef('assigned_to', 'assigned_to_contact_type');
            if (!empty($assignedToType)) {
                $userSearch->filter('ASSTO.assigned_to_contact_type_id', $assignedToType->getProperty('id'));
            }
            $users = $userSearch
                    ->groupBy('id')
                    ->orderBy('id')
                    ->select();
            if (!empty($users)) {
                $dailyDates = $this->getDailyDates();
                $weeklyDates = $this->getWeekDates();
                $monthlyDates = $this->getMonthDates();
                $ytdDates = $this->getYTDDates();
                $primaryCurrency = $this->getCurrency();
                $secondaryCurrency = $this->getSecondaryCurrency();
                $primaryCurrencyRef = $primaryCurrency->getProperty('ref');
                $secondaryCurrencyRef = '';
                if (!empty($secondaryCurrency) && $secondaryCurrency->getProperty('ref') !== $primaryCurrencyRef) {
                    $secondaryCurrencyRef = $secondaryCurrency->getProperty('ref');
                }
                
                foreach ($users as $user) {
                    $userArray = array();
                    $dailyArray = array();
                    $dailyArray[$primaryCurrencyRef] = $this->getSalesBySalesperson($dailyDates['start'], $dailyDates['end'], $user, $primaryCurrency);
                    if (!empty($secondaryCurrencyRef)) {
                        $dailyArray[$secondaryCurrencyRef] = $this->getSalesBySalesperson($dailyDates['start'], $dailyDates['end'], $user, $secondaryCurrency);
                    }
                    $userArray['daily'] = $dailyArray;
                    $weeklyArray = array();
                    $weeklyArray[$primaryCurrencyRef] = $this->getSalesBySalesperson($weeklyDates['start'], $weeklyDates['end'], $user, $primaryCurrency);
                    if (!empty($secondaryCurrency)) {
                        $weeklyArray[$secondaryCurrencyRef] = $this->getSalesBySalesperson($weeklyDates['start'], $weeklyDates['end'], $user, $secondaryCurrency);
                    }
                    $userArray['weekly'] = $weeklyArray;
                    $monthlyArray = array();
                    $monthlyArray[$primaryCurrencyRef] = $this->getSalesBySalesperson($monthlyDates['start'], $monthlyDates['end'], $user, $primaryCurrency);
                    if (!empty($secondaryCurrencyRef)) {
                        $monthlyArray[$secondaryCurrencyRef] = $this->getSalesBySalesperson($monthlyDates['start'], $monthlyDates['end'], $user, $secondaryCurrency);
                    }
                    $userArray['monthly'] = $monthlyArray;
                    $ytdArray = array();
                    $ytdArray[$primaryCurrencyRef] = $this->getSalesBySalesperson($ytdDates['start'], $ytdDates['end'], $user, $primaryCurrency);
                    if (!empty($secondaryCurrency)) {
                        $ytdArray[$secondaryCurrencyRef] = $this->getSalesBySalesperson($ytdDates['start'], $ytdDates['end'], $user, $secondaryCurrency);
                    }
                    $userArray['ytd'] = $ytdArray;
                    $rankingArray[$user->getId()] = $userArray['ytd'][$primaryCurrencyRef];
                    $unsortedTotals[$user->getId()] = $userArray;
                }
            }
            arsort($rankingArray, SORT_NUMERIC);
            foreach ($rankingArray as $userId => $primaryCurrencyTotal) {
                $sortedTotals[$userId] = $unsortedTotals[$userId];
            }
            $this->totals = $sortedTotals;
        }
        return true;
    }

    public function getSalesBySalesperson(DateTime $startDate, DateTime $endDate, AbstractUser $user = NULL, AbstractCurrency $currency = NULL, $brandRef = NULL, $tags = array()) {
        if (empty($tags)) {
            if (empty($currency)) {
                $currency = CurrencyFactory::getModelByRef(ProjectConfig::getDefaultCurrencyRef());
            }
            $key = 'sales_by_user_' . $user->getId() . '_' . $currency->getProperty('ref');
            if (!empty($brandRef)) {
                $key .= '_' . $brandRef;
            }
            $key .= '_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');
            $cachedValue = $this->getValueFromCache($key);
            if (!is_null($cachedValue)) {
                return $cachedValue;
            }
            $calculatedValue = $this->calculateSalesBySalesperson($startDate, $endDate, $user, $currency, $brandRef);
            if (!is_null($calculatedValue)) {
                $this->setValueInCache($key, $calculatedValue);
            }
            return $calculatedValue;
        } else {
            return $this->calculateSalesBySalesperson($startDate, $endDate, $user, $currency, $brandRef, $tags);
        }
    }

    protected function calculateSalesBySalesperson(DateTime $startDate, DateTime $endDate, AbstractUser $user = NULL, AbstractCurrency $currency = NULL, $brandRef = NULL, $tags = array()) {
        if (empty($user)) {
            $user = Login::getUser();
        }
        if (empty($currency)) {
            $currency = CurrencyFactory::getModelById(ProjectConfig::getDefaultCurrencyId());
        }

        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
        }
        $brand = NULL;
        if (!empty($brandRef)) {
            $brandSearch = InvItemBrandFactory::search()
                    ->filter('brand_ref', $brandRef);
            $brandArray = $brandSearch->select();
            if (!empty($brandArray)) {
                $brand = $brandArray[0];
            }
        }

        $orderLineTableName = OrderLineFactory::getDbPrefix() . 'order_line';
        $orderLineSearch = OrderLineFactory::search();
        $orderLineSearch->filterByTypeRef('sales');
        $orderLineSearch->join('order', 'id', $orderLineTableName, 'order_id', 'ORDER')
                ->join('order_has_income', 'order_id', 'ORDER', 'id', 'OHI')
                ->join('income', 'id', 'OHI', 'income_id', 'INCOME')
                ->join('contact', 'id', 'ORDER', 'contact_id', 'CONTACT')
                ->join('assigned_to_contact', 'contact_id', 'CONTACT', 'id', 'ASSTO');
        $orderLineSearch->filter('ASSTO.user_id', $user->getProperty('id'))
                ->filterNotNull('sales.shipped_ti_id')
                ->filter('INCOME.currency_id', $currency->getId());
        if (!empty($brand)) {
            $orderLineSearch->filter('ORDER.inv_item_brand_id', $brand->getProperty('id'));
        }
        if (!empty($startDate)) {
            $orderLineSearch->filterGreaterThan('INCOME.applicable_date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $orderLineSearch->filterLessThan('INCOME.applicable_date', $endDateSearchable);
        }
        if (!empty($tags)) {
            $orderLineSearch->join('income_link_to_tag', 'income_id', 'INCOME', 'id', 'tl');
            $orderLineSearch->andIf();
            $orderLineSearch->filterGroup();
            $tagCount = count($tags);
            for ($i = 0; $i < $tagCount; $i++) {
                if ($i > 0) {
                    $orderLineSearch->orIf();
                }
                $tag = $tags[$i];
                $orderLineSearch->filter('tl.tag_id', $tag->getProperty('id'));
            }
            $orderLineSearch->closeGroup();
            $orderLineSearch->andIf();
        }
        $sumArray = $orderLineSearch->sum(array('subtotal' => 'subtotal'));
        if (isset($sumArray['subtotal']) && !empty($sumArray['subtotal'])) {
            $subtotal = $sumArray['subtotal'];
        } else {
            $subtotal = 0;
        }
        return $subtotal;
    }

    protected function buildCSV(GI_CSV $csv) {
        $this->addCurrencyAndDatesToCSV($csv);
        $this->addHeadersToCSV($csv);
        $totals = $this->getTotals();
        $this->addRowsToCSV($csv, $totals);
        return $csv;
    }

    protected function addCurrencyAndDatesToCSV(GI_CSV $csv) {
        $endDate = $this->getEndDate();
        $row = array(
            'Sales by Salesperson as of ' . $endDate->format('Y-m-d'),
        );
        $csv->addHeaderRow($row);
    }

    protected function addHeadersToCSV(GI_CSV $csv) {
        $currency = $this->getCurrency();
        $secondaryCurrency = $this->getSecondaryCurrency();
        $primaryCurrencyName = $currency->getProperty('name');
        $secondaryCurrencyName = '';
        if (!empty($secondaryCurrency) && $secondaryCurrency->getId() !== $currency->getId()) {
            $secondaryCurrencyName = $secondaryCurrency->getProperty('name');
        }
        $headers = array(
            'Salesperson',
            'Daily (' . $primaryCurrencyName . ')',
        );
        if (!empty($secondaryCurrencyName)) {
            $headers[] = 'Daily (' . $secondaryCurrencyName . ')';
        }
        $headers[] = 'Weekly (' . $primaryCurrencyName . ')';
        if (!empty($secondaryCurrencyName)) {
            $headers[] = 'Weekly (' . $secondaryCurrencyName . ')';
        }
        $headers[] = 'Monthly (' . $primaryCurrencyName . ')';
        if (!empty($secondaryCurrencyName)) {
            $headers[] = 'Monthly (' . $secondaryCurrencyName . ')';
        }
        $headers[] = 'YTD (' . $primaryCurrencyName . ')';
        if (!empty($secondaryCurrencyName)) {
            $headers[] = 'YTD (' . $secondaryCurrencyName . ')';
        }
        $csv->addHeaderRow($headers);
    }

    protected function addRowsToCSV(GI_CSV $csv, $totals) {
        $currency = $this->getCurrency();
        $secondaryCurrency = $this->getSecondaryCurrency();
        $primaryCurrencyRef = $currency->getProperty('ref');
        $secondaryCurrencyRef = '';
        if (!empty($secondaryCurrency) && $secondaryCurrency->getId() !== $currency->getId()) {
            $secondaryCurrencyRef = $secondaryCurrency->getProperty('ref');
        }
        foreach ($totals as $userId => $userTotals) {
            $user = UserFactory::getModelById($userId);
            if (!empty($user)) {
                $name = $user->getFullName();
                $row = array(
                    $name,
                );
                $dailyTotals = $userTotals['daily'];
                $weeklyTotals = $userTotals['weekly'];
                $monthlyTotals = $userTotals['monthly'];
                $ytdTotals = $userTotals['ytd'];
                $row[] = $dailyTotals[$primaryCurrencyRef];
                if (!empty($secondaryCurrencyRef)) {
                    $row[] = $dailyTotals[$secondaryCurrencyRef];
                }
                $row[] = $weeklyTotals[$primaryCurrencyRef];
                if (!empty($secondaryCurrencyRef)) {
                    $row[] = $weeklyTotals[$secondaryCurrencyRef];
                }
                $row[] = $monthlyTotals[$primaryCurrencyRef];
                if (!empty($secondaryCurrencyRef)) {
                    $row[] = $monthlyTotals[$secondaryCurrencyRef];
                }
                $row[] = $ytdTotals[$primaryCurrencyRef];
                if (!empty($secondaryCurrencyRef)) {
                    $row[] = $ytdTotals[$secondaryCurrencyRef];
                }
                $csv->addRow($row);
            }
        }
    }

    protected function getFullCacheKeyFromKey($key) {
        $franchiseId = QBConnection::getFranchiseId();
        $fullKey = 'report_data_' . $key . '_' . $franchiseId;
        return $fullKey;
    }
    
        protected function getDailyDates() {
        if (empty($this->dailyDates)) {
            $dates = array();
            $today = $this->getEndDate();
            $yesterdayDateTime = clone $today;
            $yesterdayDateTime->modify("-1 days");
            $startDateTime = new DateTime($yesterdayDateTime->format('Y-m-d') . ' 00:00:00');
            $endDateTime = new DateTime($yesterdayDateTime->format('Y-m-d') . '23:59:59');
            $dates['start'] = $startDateTime;
            $dates['end'] = $endDateTime;
            $this->dailyDates = $dates;
        }
        return $this->dailyDates;
    }

    protected function getWeekDates() {
        if (empty($this->weekDates)) {
            $dates = array();
            $endDate = $this->getEndDate();
            $endDateObject = clone $endDate;
            $endDateObject->modify("-1 days");
            $endDateObject->setTime(23,59,59);
            $startDate = clone $endDate;
            $startDate->modify('last Sunday');
            $startDate->setTime(00,00,00);
            $dates['start'] = $startDate;
            $dates['end'] = $endDateObject;
            $this->weekDates = $dates;
        }
        return $this->weekDates;
    }

    protected function getMonthDates() {
        if (empty($this->monthDates)) {
            $dates = array();
            $endDate = $this->getEndDate();
            $startDate = clone $endDate;
            $startDate->modify('last day of previous month')
                    ->modify("+1 days")
                    ->setTime(00, 00, 00);
            $endDateObject = clone $endDate;
            $endDateObject->modify("-1 days");
            $endDateObject->setTime(23, 59, 59);
            $dates['start'] = $startDate;
            $dates['end'] = $endDateObject;

            $this->monthDates = $dates;
        }
        return $this->monthDates;
    }

    protected function getYTDDates() {
        if (empty($this->ytdDates)) {
            $dates = array();
            $endDate = $this->getEndDate();
            $endDateObject = clone $endDate;
            $endDateObject->modify("-1 days");
            $endDateObject->setTime(23, 59, 59);
            $fiscalYearDates = GI_Time::getFiscalYearStartAndEndDates($endDate);
            $dates['start'] = $fiscalYearDates['start'];
            $dates['end'] = $endDateObject;
            $this->ytdDates = $dates;
        }
        return $this->ytdDates;
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_sales_by_salesperson_report')) {
            return true;
        }
        return false;
    }

}
