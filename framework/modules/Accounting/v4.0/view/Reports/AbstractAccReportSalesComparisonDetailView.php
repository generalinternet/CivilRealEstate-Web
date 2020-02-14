<?php
/**
 * Description of AbstractAccReportSalesComparisonDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportSalesComparisonDetailView extends AbstractAccReportDetailView {

    public function buildViewBody() {
        $this->addHTML('<h3>As of ' . $this->accReport->getEndDate()->format('M jS, Y') . '</h3>');
        $this->addHTML('<hr />');
        $this->addHTML('<div class="flex_row">');
        $this->addHTML('<div class="flex_col">');
        $this->buildTable();
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        
       
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }



    protected function buildTable() {
        $totals = $this->accReport->getSalesTotals();
        if (!empty($totals) && isset($totals['current']) && isset($totals['previous'])) {
            $this->addHTML('<div class="flex_table ui_table">');
            $this->buildTableHeader();
            $this->buildTableRows($totals);
            $this->addHTML('</div>');
        } else {
            $this->addHTML('<p>Error calculating Purchase Order data. If this problem perists, please contact your friendly General Internet team.</p>');
        }
    }

    protected function buildTableHeader() {
        $this->addHTML('<div class="flex_row flex_head">')
                ->addHTML('<div class="flex_col">')
                ->addHTML('')
                ->addHTML('</div>')
                ->addHTML('<div class="flex_col">')
                ->addHTML('Previous Year')
                ->addHTML('</div>')
                ->addHTML('<div class="flex_col">')
                ->addHTML('Current Year')
                ->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function buildTableRows($totals) {
        $months = array();
        foreach ($totals['previous'] as $monthKey => $previousTotal) {
            $monthKey = str_replace('_', '-', $monthKey);
            $monthDateTime = new DateTime($monthKey . '-01 00:00:00');
            $monthName = $monthDateTime->format('F');
            $months[] = $monthName;
        }
        $currentTotals = array_values($totals['current']);
        $previousTotals = array_values($totals['previous']);
        $count = count($previousTotals);
        for ($i=($count-1);$i>-1;$i--) {
             $currentTotal = $currentTotals[$i];
             $previousTotal = $previousTotals[$i];
             $monthName = $months[$i];
            $this->addHTML('<div class="flex_row">')
                    ->addHTML('<div class="flex_col">')
                    ->addHTML($monthName)
                    ->addHTML('</div>')
                    ->addHTML('<div class="flex_col">')
                    ->addHTML('$' . GI_StringUtils::formatMoney($previousTotal))
                    ->addHTML('</div>')
                    ->addHTML('<div class="flex_col">')
                    ->addHTML('$' . GI_StringUtils::formatMoney($currentTotal))
                    ->addHTML('</div>')
                    ->addHTML('</div>');
        
        }
    }

    protected function buildTableRow($label, $previousTotal, $currentTotal) {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">')
                ->addHTML($label)
                ->addHTML('</div>')
                ->addHTML('<div class="flex_col">')
                ->addHTML('$' . GI_StringUtils::formatMoney($previousTotal))
                ->addHTML('</div>')
                ->addHTML('<div class="flex_col">')
                ->addHTML('$' . GI_StringUtils::formatMoney($currentTotal))
                ->addHTML('</div>')
                ->addHTML('</div>');
    }

}
