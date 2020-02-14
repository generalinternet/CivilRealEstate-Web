<?php
/**
 * Description of AbstractAccReportOrderValues
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportOrderValues extends AbstractAccReport {
    
    protected $totals = NULL;
    protected $dailyDates = NULL;
    protected $weekDates = NULL;
    protected $monthDates = NULL;
    protected $ytdDates = NULL;
    protected $cacheTTL = 21600; //6 hours
    
    public function __construct($typeRef, \DateTime $startDate, \DateTime $endDate) {
        $endDate = new DateTime(date('Y-m-d'));
        $fiscalYearDates = GI_Time::getFiscalYearStartAndEndDates($endDate);
        $startDate = $fiscalYearDates['start'];
        parent::__construct($typeRef, $startDate, $endDate);
    }


    public function getTitle() {
        return 'Order Values';
    }

    public function getDescription() {
        return 'Shows Purchase Order and Sales Order totals by day, week, month, and YTD.';
    }

    public function getColour() {
        return 'ff630f';
    }

    public function getInitials() {
        return 'OV';
    }

    public function getDetailView() {
        return new AccReportOrderValuesDetailView($this, $this->getTotals());
    }

    public function buildReport() {
        $totals = array();
        $totals['po'] = $this->getPOTotals();
        $totals['so'] = $this->getSOTotals();
        $this->totals = $totals;
        return true;
    }
    
    public function getTotals() {
        if (empty($this->totals)) {
            $this->buildReport();
        }
        return $this->totals;
    }
    
    protected function getPOTotals() {
        $poTotals = array();
        $cadCurrency = CurrencyFactory::getModelByRef('cad');
        $usdCurrency = CurrencyFactory::getModelByRef('usd');
        $dailyDates = $this->getDailyDates();
        $weekDates = $this->getWeekDates();
        $monthDates = $this->getMonthDates();
        $ytdDates = $this->getYTDDates();
        
        $cadTotals = array();
        $cadInProgressTotals = array();
            $cadInProgressTotals['daily'] = $this->getPOTotal($dailyDates['start'], $dailyDates['end'], $cadCurrency, false);
            $cadInProgressTotals['week'] = $this->getPOTotal($weekDates['start'], $weekDates['end'], $cadCurrency, false);
            $cadInProgressTotals['month'] = $this->getPOTotal($monthDates['start'], $monthDates['end'], $cadCurrency, false);
            $cadInProgressTotals['ytd'] = $this->getPOTotal($ytdDates['start'], $ytdDates['end'], $cadCurrency, false);
        $cadReceivedTotals = array();
            $cadReceivedTotals['daily'] = $this->getPOTotal($dailyDates['start'], $dailyDates['end'], $cadCurrency, true);
            $cadReceivedTotals['week'] = $this->getPOTotal($weekDates['start'], $weekDates['end'], $cadCurrency, true);
            $cadReceivedTotals['month'] = $this->getPOTotal($monthDates['start'], $monthDates['end'], $cadCurrency, true);
            $cadReceivedTotals['ytd'] = $this->getPOTotal($ytdDates['start'], $ytdDates['end'], $cadCurrency, true);
        $cadTotals['inp'] = $cadInProgressTotals;
        $cadTotals['rec'] = $cadReceivedTotals;
        $poTotals['cad'] = $cadTotals;
        $usdTotals = array();
        $usdInProgressTotals = array();
            $usdInProgressTotals['daily'] = $this->getPOTotal($dailyDates['start'], $dailyDates['end'], $usdCurrency, false);
            $usdInProgressTotals['week'] = $this->getPOTotal($weekDates['start'], $weekDates['end'], $usdCurrency, false);
            $usdInProgressTotals['month'] = $this->getPOTotal($monthDates['start'], $monthDates['end'], $usdCurrency, false);
            $usdInProgressTotals['ytd'] = $this->getPOTotal($ytdDates['start'], $ytdDates['end'], $usdCurrency, false);
        $usdReceivedTotals = array();
            $usdReceivedTotals['daily'] = $this->getPOTotal($dailyDates['start'], $dailyDates['end'], $usdCurrency, true);
            $usdReceivedTotals['week'] = $this->getPOTotal($weekDates['start'], $weekDates['end'], $usdCurrency, true);
            $usdReceivedTotals['month'] = $this->getPOTotal($monthDates['start'], $monthDates['end'], $usdCurrency, true);
            $usdReceivedTotals['ytd'] = $this->getPOTotal($ytdDates['start'], $ytdDates['end'], $usdCurrency, true);
        $usdTotals['inp'] = $usdInProgressTotals;
        $usdTotals['rec'] = $usdReceivedTotals;
        $poTotals['usd'] = $usdTotals;
        return $poTotals;
    }
    
    protected function getPOTotal(DateTime $startDate, DateTime $endDate, AbstractCurrency $currency, $received = true) {
        $key = 'po_' . $startDate->format('Y_m_d') . '_' . $endDate->format('Y_m_d') . '_' . $currency->getProperty('ref') . '_';
        if ($received) {
            $key .= 'rec';
        } else {
            $key .= 'inp';
        }
        $cachedValue = $this->getValueFromCache($key);
        if (!is_null($cachedValue)) {
           return $cachedValue;
        }
        $total = $this->calculatePOTotal($startDate, $endDate, $currency, $received);
        if (!is_null($total) && $this->useCache) {
            $this->setValueInCache($key, $total);
        }
        return $total;
    }

    protected function calculatePOTotal(DateTime $startDate, DateTime $endDate, AbstractCurrency $currency, $received = true) {
        $startDateObject = clone $startDate;
        $endDateObject = clone $endDate;
        $tableName = ExpenseItemFactory::getDbPrefix() . 'expense_item';
        $search = ExpenseItemFactory::search();
        $search->join('expense', 'id', $tableName, 'expense_id', 'EXP')
                ->filter('EXP.currency_id', $currency->getId());
        $itemJoin = $search->createJoin('item_link_to_expense_item', 'expense_item_id', $tableName, 'id', 'ILTEI');
        $itemJoin->filter('ILTEI.table_name', 'order_line')
                ->filter('ILTEI.status', 1);
        $search->join('order_line', 'id', 'ILTEI', 'item_id', 'OL');
        $search->join('order', 'id', 'OL', 'order_id', 'ORD');
        
        $startDateObject->modify("-1 days");
        $startDateObject->setTime(23, 59,59);
        $endDateObject->modify("+1 days");
        $endDateObject->setTime(00, 00, 00);
        $search->filterGreaterThan('ORD.date', $startDateObject->format('Y-m-d'))
                ->filterLessThan('ORD.date', $endDateObject->format('Y-m-d'));
        
        $itemJoinTwo = $search->createJoin('item_link_to_expense_item', 'expense_item_id', $tableName, 'id', 'ILTEI2', 'left');
        $itemJoinTwo->filter('ILTEI2.table_name', 'inv_stock')
                ->filter('ILTEI2.status', 1);
        if ($received) {
            $search->filter('ILTEI2.status', 1);
        } else {
            $search->filterNull('ILTEI2.status');
        }
        $results = $search->sum(array('net_amount' => 'net_amount'));
        if (isset($results['net_amount'])) {
            return $results['net_amount'];
        }
        return 0;
    }

    protected function getSOTotals() {
        $soTotals = array();
        $cadCurrency = CurrencyFactory::getModelByRef('cad');
        $usdCurrency = CurrencyFactory::getModelByRef('usd');
        $dailyDates = $this->getDailyDates();
        $weekDates = $this->getWeekDates();
        $monthDates = $this->getMonthDates();
        $ytdDates = $this->getYTDDates();

        $cadTotals = array();
        $cadInProgressTotals = array();
        $cadInProgressTotals['daily'] = $this->getSOTotal($dailyDates['start'], $dailyDates['end'], $cadCurrency, false);
        $cadInProgressTotals['week'] = $this->getSOTotal($weekDates['start'], $weekDates['end'], $cadCurrency, false);
        $cadInProgressTotals['month'] = $this->getSOTotal($monthDates['start'], $monthDates['end'], $cadCurrency, false);
        $cadInProgressTotals['ytd'] = $this->getSOTotal($ytdDates['start'], $ytdDates['end'], $cadCurrency, false);
        $cadShippedTotals = array();
        $cadShippedTotals['daily'] = $this->getSOTotal($dailyDates['start'], $dailyDates['end'], $cadCurrency, true);
        $cadShippedTotals['week'] = $this->getSOTotal($weekDates['start'], $weekDates['end'], $cadCurrency, true);
        $cadShippedTotals['month'] = $this->getSOTotal($monthDates['start'], $monthDates['end'], $cadCurrency, true);
        $cadShippedTotals['ytd'] = $this->getSOTotal($ytdDates['start'], $ytdDates['end'], $cadCurrency, true);
        $cadTotals['inp'] = $cadInProgressTotals;
        $cadTotals['shp'] = $cadShippedTotals;
        $soTotals['cad'] = $cadTotals;
        $usdTotals = array();
        $usdInProgressTotals = array();
        $usdInProgressTotals['daily'] = $this->getSOTotal($dailyDates['start'], $dailyDates['end'], $usdCurrency, false);
        $usdInProgressTotals['week'] = $this->getSOTotal($weekDates['start'], $weekDates['end'], $usdCurrency, false);
        $usdInProgressTotals['month'] = $this->getSOTotal($monthDates['start'], $monthDates['end'], $usdCurrency, false);
        $usdInProgressTotals['ytd'] = $this->getSOTotal($ytdDates['start'], $ytdDates['end'], $usdCurrency, false);
        $usdShippedTotals = array();
        $usdShippedTotals['daily'] = $this->getSOTotal($dailyDates['start'], $dailyDates['end'], $usdCurrency, true);
        $usdShippedTotals['week'] = $this->getSOTotal($weekDates['start'], $weekDates['end'], $usdCurrency, true);
        $usdShippedTotals['month'] = $this->getSOTotal($monthDates['start'], $monthDates['end'], $usdCurrency, true);
        $usdShippedTotals['ytd'] = $this->getSOTotal($ytdDates['start'], $ytdDates['end'], $usdCurrency, true);
        $usdTotals['inp'] = $usdInProgressTotals;
        $usdTotals['shp'] = $usdShippedTotals;
        $soTotals['usd'] = $usdTotals;
        return $soTotals;
    }

    protected function getSOTotal(DateTime $startDate, DateTime $endDate, AbstractCurrency $currency, $shipped = true) {
        $key = 'so_' . $startDate->format('Y_m_d') . '_' . $endDate->format('Y_m_d') . '_' . $currency->getProperty('ref') . '_';
        if ($shipped) {
            $key .= 'shp';
        } else {
            $key .= 'inp';
        }
        $cachedValue = $this->getValueFromCache($key);
        if (!is_null($cachedValue)) {
           return $cachedValue;
        }
        $total = $this->calculateSOTotal($startDate, $endDate, $currency, $shipped);
        if (!is_null($total) && $this->useCache) {
            $this->setValueInCache($key, $total);
        }
        return $total;
    }
    
    protected function calculateSOTotal(DateTime $startDate, DateTime $endDate, AbstractCurrency $currency, $shipped = true) {
        $total = 0;
        $regularLinesTotal = $this->calculateRegularSOLinesTotal($startDate, $endDate, $currency, $shipped);
        if (!empty($regularLinesTotal)) {
            $total += $regularLinesTotal;
        }
        $soLinesTotal = $this->calculateACSOLinesTotal($startDate, $endDate, $currency, $shipped);
        if (!empty($soLinesTotal)) {
            $total += $soLinesTotal;
        }
        return $total;
    }

    protected function calculateRegularSOLinesTotal(DateTime $startDate, DateTime $endDate, AbstractCurrency $currency, $shipped = true) {
        $startDateObject = clone $startDate;
        $endDateObject = clone $endDate;
        $tableName = IncomeItemFactory::getDbPrefix() . 'income_item';
        $search = IncomeItemFactory::search();
        $search->join('income', 'id', $tableName, 'income_id', 'INCOME')
                ->filter('INCOME.currency_id', $currency->getId());
        $itemJoin = $search->createJoin('item_link_to_income_item', 'income_item_id', $tableName, 'id', 'ILTII');
        $itemJoin->filter('ILTII.table_name', 'order_line')
                ->filter('ILTII.status', 1);
        $search->join('order_line', 'id', 'ILTII', 'item_id', 'OL');
        $search->join('order', 'id', 'OL', 'order_id', 'ORD');

        $startDateObject->modify("-1 days");
        $startDateObject->setTime(23, 59, 59);
        $endDateObject->modify("+1 days");
        $endDateObject->setTime(00, 00, 00);
        $search->filterGreaterThan('ORD.date', $startDateObject->format('Y-m-d'))
                ->filterLessThan('ORD.date', $endDateObject->format('Y-m-d'));

        $search->join('order_line_sales', 'parent_id', 'OL', 'id', 'OLS');
        if ($shipped) {
            $search->filterNotNull('OLS.shipped_ti_id');
        } else {
            $search->filterNull('OLS.shipped_ti_id');
        }

        $results = $search->sum(array('net_amount' => 'net_amount'));
        if (isset($results['net_amount'])) {
            return $results['net_amount'];
        }
        return 0;
    }
    
    protected function calculateACSOLinesTotal(DateTime $startDate, DateTime $endDate, AbstractCurrency $currency, $shipped = true) {
        $startDateObject = clone $startDate;
        $endDateObject = clone $endDate;
        $tableName = OrderLineFactory::getDbPrefix() . 'order_line';
        $search = OrderLineFactory::search();
        $search->filterByTypeRef('ac_sales');
        $search->join('order_shipment', 'id', $tableName, 'order_shipment_id', 'OSHIP')
                ->join('order_shipment_status', 'id', 'OSHIP', 'order_shipment_status_id', 'OSHIPSTAT')
                ->join('order', 'id', $tableName, 'order_id', 'ORD');
        
        $startDateObject->modify("-1 days");
        $startDateObject->setTime(23, 59,59);
        $endDateObject->modify("+1 days");
        $endDateObject->setTime(00, 00, 00);
        $search->filterGreaterThan('ORD.date', $startDateObject->format('Y-m-d'))
                ->filterLessThan('ORD.date', $endDateObject->format('Y-m-d'));

        $search->filter('ORD.currency_id', $currency->getId());
        if ($shipped) {
            $search->filterGroup()
                    ->filter('OSHIPSTAT.ref', 'shipped')
                    ->orIf()
                    ->filter('OSHIPSTAT.ref', 'delivered')
                    ->closeGroup()
                    ->andIf();
        } else {
            $search->filter('OSHIPSTAT.ref', 'awaiting_shipment');
        }
        $results = $search->sum(array('subtotal' => 'subtotal'));
        if (isset($results['subtotal'])) {
            return $results['subtotal'];
        }
        return 0;
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

    protected function getFullCacheKeyFromKey($key) {
        $franchiseId = QBConnection::getFranchiseId();
        $fullKey = 'report_data_' . $key . '_' . $franchiseId;
        return $fullKey;
    }
    
        protected function buildCSV(GI_CSV $csv) {
        $this->addCurrencyAndDatesToCSV($csv);
        $this->addHeadersToCSV($csv);
        $this->addLabelRowToCSV($csv, 'Purchase Orders');
        $this->addPODataToCSV($csv);
        $this->addLabelRowToCSV($csv, 'Sales Orders', true);
        $this->addSODataToCSV($csv);
        return $csv;
    }

    protected function addCurrencyAndDatesToCSV(GI_CSV $csv) {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $row = array(
            'Start: ' . $startDate->format('Y-m-d'),
            'End: ' . $endDate->format('Y-m-d'),
        );
        $csv->addHeaderRow($row);
    }

    protected function addHeadersToCSV(GI_CSV $csv) {
        $headers = array(
            '',
            'Status',
            'CAD',
            'USD'
        );
        $csv->addHeaderRow($headers);
    }
    
    protected function addLabelRowToCSV(GI_CSV $csv, $label, $addSpacingRow = false) {
        $spacerRow = array(
            '',
            '',
            '',
            ''
        );
        $labelRow = array(
            $label,
            '',
            '',
            ''
        );
        if ($addSpacingRow) {
            $csv->addRow($spacerRow);
        }
        $csv->addRow($labelRow);
    }
    
    protected function addPODataToCSV(GI_CSV $csv) {
        $data = $this->getPOTotals();
        $dailyInProgressRow = array('Daily','In Progress', $data['cad']['inp']['daily'], $data['usd']['inp']['daily']);
        $csv->addRow($dailyInProgressRow);
        $dailyReceivedRow = array('Daily','Received',$data['cad']['rec']['daily'], $data['usd']['rec']['daily']);
        $csv->addRow($dailyReceivedRow);
        $weekInProgressRow = array('Week','In Progress', $data['cad']['inp']['week'], $data['usd']['inp']['week']);
        $csv->addRow($weekInProgressRow);
        $weekReceivedRow = array('Week','Received',$data['cad']['rec']['week'], $data['usd']['rec']['week']);
        $csv->addRow($weekReceivedRow);
        $monthInProgressRow = array('Month','In Progress',$data['cad']['inp']['month'], $data['usd']['inp']['month']);
        $csv->addRow($monthInProgressRow);
        $monthReceivedRow = array('Month', 'Received',$data['cad']['rec']['month'], $data['usd']['rec']['month']);
        $csv->addRow($monthReceivedRow);
        $ytdInProgressRow = array('YTD','In Progress',$data['cad']['inp']['ytd'], $data['usd']['inp']['ytd']);
        $csv->addRow($ytdInProgressRow);
        $ytdReceivedRow = array('YTD','Received',$data['cad']['rec']['ytd'], $data['usd']['rec']['ytd']);
        $csv->addRow($ytdReceivedRow);
    }
    
    protected function addSODataToCSV(GI_CSV $csv) {
        $data = $this->getSOTotals();
        $dailyInProgressRow = array('Daily','In Progress',$data['cad']['inp']['daily'],$data['usd']['inp']['daily']);
        $csv->addRow($dailyInProgressRow);
        $dailyShippedRow = array('Daily', 'Shipped',$data['cad']['shp']['daily'],$data['usd']['shp']['daily']);
        $csv->addRow($dailyShippedRow);
        $weekInProgressRow = array('Week','In Progress',$data['cad']['inp']['week'],$data['usd']['inp']['week']);
        $csv->addRow($weekInProgressRow);
        $weekShippedRow = array('Week','Shipped',$data['cad']['shp']['week'],$data['usd']['shp']['week']);
        $csv->addRow($weekShippedRow);
        $monthInProgressRow = array('Month','In Progress',$data['cad']['inp']['month'],$data['usd']['inp']['month']);
        $csv->addRow($monthInProgressRow);
        $monthShippedRow = array('Month', 'Shipped', $data['cad']['shp']['month'], $data['usd']['shp']['month']);
        $csv->addRow($monthShippedRow);
        $ytdInProgressRow = array('YTD', 'In Progress', $data['cad']['inp']['ytd'], $data['usd']['inp']['ytd']);
        $csv->addRow($ytdInProgressRow);
        $ytdShippedRow = array('YTD', 'Shipped', $data['cad']['shp']['ytd'], $data['usd']['shp']['ytd']);
        $csv->addRow($ytdShippedRow);
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_order_values_report')) {
            return true;
        }
        return false;
    }

}
