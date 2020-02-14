<?php
/**
 * Description of AbstractAccReport
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReport {
    
    protected $startDate;
    protected $endDate;
    protected $typeRef = NULL;
    protected $title = 'Accounting Report';
    protected $properties = array();
    protected $currencyTitle = NULL;
    protected $currency = NULL;
    protected $secondaryCurrency = NULL;
    protected $reportBuilt = false;
    protected $disabled = false;
    protected $cacheTTL = 86400; //24 hours
    protected $useCache = true;
    protected $overridePermissionCheck = false;

    public function __construct($typeRef, DateTime $startDate, DateTime $endDate) {
        $this->typeRef = $typeRef;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getProperty($key) {
        return $this->properties[$key];
    }

    public function buildReport() {
        
    }

    public function getTypeRef() {
        return $this->typeRef;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getStartDate() {
        return $this->startDate;
    }
    
    public function getEndDate() {
        return $this->endDate;
    }
    
    public function getSummaryView() {
        return new AccReportSummaryView($this);
    }
    
    public function getDescription() {
        return '';
    }
    
    public function getColour() {
        return '000000';
    }
    
    public function getInitials() {
        return '';
    }
    
    public function getDetailView() {
        return NULL;
    }
    
    public function getCurrencyTitle() {
        if (empty($this->currencyTitle)) {
            $currency = $this->getCurrency();
            if (!empty($currency)) {
                $this->currencyTitle = $currency->getProperty('name');
            }
        }
        return $this->currencyTitle;
    }
    
    /**
     * @return AbstractCurrency
     */
    public function getCurrency() {
        if (empty($this->currency)) {
            $this->currency = CurrencyFactory::getModelById(ProjectConfig::getDefaultCurrencyId());
        }
        return $this->currency;
    }

    public function getSecondaryCurrency() {
        if (empty($this->secondaryCurrency)) {
            $primaryCurrency = $this->getCurrency();
            if (empty($primaryCurrency)) {
                return false;
            }
            $primaryCurrencyRef = $primaryCurrency->getProperty('ref');
            if ($primaryCurrencyRef == 'cad') {
                $secondaryCurrency = CurrencyFactory::getModelByRef('usd');
            } else {
                $secondaryCurrency = CurrencyFactory::getModelByRef('cad');
            }
            $this->secondaryCurrency = $secondaryCurrency;
        }
        return $this->secondaryCurrency;
    }

    public function getHideCSSClass() {
        return 'hide_on_load';
    }

    public function getCSVFile() {
        $title = str_replace(' ', '_', $this->getTitle());
        $title = str_replace('/', '', $title);
        $fileName = $title . '_Report_' . $this->getStartDate()->format('Y-m-d') . '_to_' . $this->getEndDate()->format('Y-m-d');
        $csv = new GI_CSV(GI_Sanitize::filename($fileName));
        $csv->setOverWrite(true);
        $csv = $this->buildCSV($csv);
        GI_CSV::setCSVExporting(false);
        $csvFile = $csv->getCSVFilePath();
        return $csvFile;
    }
    
    protected function buildCSV(GI_CSV $csv) {
        //Do nothing
    }


    protected function addCurrencyAndDatesToCSV(GI_CSV $csv) {
        $currency = $this->getCurrency();
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $row = array(
            'Currency: ' . $currency->getProperty('name'),
            'Start: ' . $startDate->format('Y-m-d'),
            'End: ' . $endDate->format('Y-m-d'),
        );
        $csv->addHeaderRow($row);
    }
    
    public function isDisabled() {
        return $this->disabled;
    }

    public function isViewable() {
        return false;
    }

    /**
     * 
     * @param Boolean $overridePermissionCheck
     */
    public function setOverridePermissionCheck($overridePermissionCheck = false) {
        $this->overridePermissionCheck = $overridePermissionCheck;
    }
    
    public function setUseCache($useCache = true) {
        $this->useCache = $useCache;
    }

    protected function setValueInCache($key, $value) {
        if (!$this->useCache) {
            return true;
        }
        $fullKey = $this->getFullCacheKeyFromKey($key);
        if (!empty($fullKey)) {
            return apcu_store($fullKey, $value, $this->cacheTTL);
        }
        return false;
    }

    protected function getValueFromCache($key) {
        if ($this->useCache) {
            $fullKey = $this->getFullCacheKeyFromKey($key);
            if (!empty($fullKey)) {
                if (apcu_exists($fullKey)) {
                    return apcu_fetch($fullKey);
                }
            }
        }
        return NULL;
    }

    protected function getFullCacheKeyFromKey($key) {
        $startDateTime = $this->getStartDate();
        $endDateTime = $this->getEndDate();
        $franchiseId = QBConnection::getFranchiseId();
        $fullKey = 'report_data_' . $startDateTime->format('Y_m') . '_' . $endDateTime->format('Y_m') . '_' . $key . '_' . $franchiseId;
        return $fullKey;
    }

    /**
     * @param Integer $cacheTTL - The number of seconds for values to be stored in cache. 24 hours by default
     */
    public function setCacheTTL($cacheTTL) {
        $this->cacheTTL = $cacheTTL;
    }

}
