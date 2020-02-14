<?php
/**
 * Description of AbstractAccReportSalesBySalespersonDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportSalesBySalespersonDetailView extends AbstractAccReportDetailView {
    
    protected $currencyNames = array();
    protected $currencyRefs = array();

    public function __construct(\AbstractAccReport $accReport) {
        parent::__construct($accReport);
        $currency = $this->accReport->getCurrency();
        $secondaryCurrency = $this->accReport->getSecondaryCurrency();
        $this->currencyNames[] = $currency->getProperty('name');
        $this->currencyRefs[] = $currency->getProperty('ref');
        if (!empty($secondaryCurrency) && $secondaryCurrency->getId() !== $currency->getId()) {
            $this->currencyNames[] = $secondaryCurrency->getProperty('name');
            $this->currencyRefs[] = $secondaryCurrency->getProperty('ref');
        }
    }

    protected function buildViewBody() {
        $this->addHTML('<h3>Total sales from shipped items as of ' . $this->accReport->getEndDate()->format('M jS, Y') . '</h3>');
        $this->addTable();
    }
    
    protected function addTable() {
        $this->addHTML('<table class="ui_table">');
        $this->addTableHeader();
        $this->addTableBody();
        $this->addTableFooter();
        $this->addHTML('</table>');
    }

    protected function addTableHeader() {
        $currencyCount = count($this->currencyNames);
        $headerTitles = array(
            'Daily',
            'Week',
            'Month',
            'YTD',
        );
        $this->addHTML('<thead>');
        $this->addHTML('<tr>');
        $this->addHTML('<th>Salesperson</th>');
        foreach ($headerTitles as $hTitle) {
            $this->addHTML('<th colspan="'.$currencyCount.'" style="text-align:center;">'.$hTitle.'</th>');
        }
        $this->addHTML('</tr>');
        if ($currencyCount > 1) {
            $this->addHTML('<tr>');
            $this->addHTML('<th></th>'); //Empty
            foreach ($headerTitles as $hTitle) {
                foreach ($this->currencyNames as $cName) {
                     $this->addHTML('<th class="sml_col" style="text-align:center;">'.$cName.'</th>');
                }
            }

            $this->addHTML('</tr>');
        }
        $this->addHTML('</thead>');
    }

    protected function addTableBody() {
        $this->addHTML('<tbody>');
        $totals = $this->accReport->getTotals();
        if (!empty($totals)) {
            foreach ($totals as $userId => $userTotals) {
                $user = UserFactory::getModelById($userId);
                if (!empty($user)) {
                    $this->addTableRow($user, $userTotals);
                }
            }
        }
        $this->addHTML('</tbody>');
    }

    protected function addTableRow(AbstractUser $user, $userTotals) {
        $this->addHTML('<tr>');
        $keys = array(
            'daily',
            'weekly',
            'monthly',
            'ytd',
        );
        
        $this->addTableCell($user->getFullName(), ' ');
        $class = '';
        if (count($this->currencyNames) > 1) {
            //$class = "sml_col";
        }
        foreach ($keys as $key) {
            $keyTotals = $userTotals[$key];
            foreach ($this->currencyRefs as $currencyRef) {
                $value = '$'. GI_StringUtils::formatMoney($keyTotals[$currencyRef]);
                $this->addTableCell($value, $class);
            }
        }
        $this->addHTML('</tr>');
    }

    protected function addTableCell($value, $class = '') {
        if (!empty($class)) {
            $openTag = '<td class="' . $class . '">';
        } else {
            $openTag = '<td style="text-align:center;">';
        }
        $this->addHTML($openTag);
        $this->addHTML($value);
        $this->addHTML('</td>');
    }

    protected function addTableFooter() {
        $this->addHTML('<tfoot>');


        $this->addHTML('</tfoot>');
    }

}
