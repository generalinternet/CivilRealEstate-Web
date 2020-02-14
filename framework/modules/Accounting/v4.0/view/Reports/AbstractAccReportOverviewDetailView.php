<?php
/**
 * Description of AbstractAccReportOverviewDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccReportOverviewDetailView extends AbstractAccReportDetailView {

    protected function buildViewBody() {
        $this->buildSummarySection();
    }
    
    protected function buildSummarySection() {
        $this->buildTotalsRow();
        $this->buildAPandARSection();
    }
    
    protected function buildTotalsRow() {
        $incomeProperties = $this->accReport->getProperty('income');
        $cogsProperties = $this->accReport->getProperty('cogs');
        $profitProperites = $this->accReport->getProperty('profit');
        $inventoryProperties = $this->accReport->getProperty('inventory');
                $this->addHTML('<div class="columns fifths flex_columns">');
        $this->addHTML('<div class="column border_column">');
        $this->addHTML('<div class="hr_title">Total Inventory</div>');
        $this->addHTML('<div class="hr_content">');
        if (!empty($inventoryProperties) && isset($inventoryProperties['total'])) {
            $this->addContentBlock('$' . GI_StringUtils::formatMoney($inventoryProperties['total'], true));
        } else {
            $this->addContentBlock('--');
        }
        
        $this->addHTML('</div><!--.hr_content-->');
        $this->addHTML('</div>');

        $this->addHTML('<div class="column border_column">');
        $this->addHTML('<div class="hr_title">Total Waste</div>');
        $this->addHTML('<div class="hr_content">');
        if (!empty($cogsProperties) && isset($cogsProperties['waste'])) {
            $this->addContentBlock('$' . GI_StringUtils::formatMoney($cogsProperties['waste'], true));
        } else {
            $this->addContentBlock('--');
        }
        $this->addHTML('</div><!--.hr_content-->');
        $this->addHTML('</div>');

        $this->addHTML('<div class="column border_column">');
        $this->addHTML('<div class="hr_title">Total COGS</div>');
        $this->addHTML('<div class="hr_content">');
        if (!empty($cogsProperties) && isset($cogsProperties['cogs'])) {
            $this->addContentBlock('$' . GI_StringUtils::formatMoney($cogsProperties['cogs'], true));
        } else {
            $this->addContentBlock('--');
        }
        $this->addHTML('</div><!--.hr_content-->');
        $this->addHTML('</div>');

        $this->addHTML('<div class="column border_column">');
        $this->addHTML('<div class="hr_title">Total Sales</div>');
        $this->addHTML('<div class="hr_content">');
        if (!empty($incomeProperties) && isset($incomeProperties['total'])) {
            $this->addContentBlock('$' . GI_StringUtils::formatMoney($incomeProperties['total'], true));
        } else {
            $this->addContentBlock('--');
        }
        $this->addHTML('</div><!--.hr_content-->');
        $this->addHTML('</div>');

        $this->addHTML('<div class="column border_column">');
        $this->addHTML('<div class="hr_title">Gross Profit</div>');
        $this->addHTML('<div class="hr_content">');
        if (!empty($profitProperites) && isset($profitProperites['total'])) {
            $this->addContentBlock('$' . GI_StringUtils::formatMoney($profitProperites['total'], true));
        } else {
            $this->addContentBlock('--');
        }
        $this->addHTML('</div><!--.hr_content-->');
        $this->addHTML('</div>');

        $this->addHTML('</div>');
    }
    
    protected function buildAPandARSection() {
        $arProperties = $this->accReport->getProperty('ar');
        $inProgressExpenses = $this->accReport->getProperty('in_progress_expenses');
        $apProperties = $this->accReport->getProperty('ap');
        $inProgressIncomes = $this->accReport->getProperty('in_progress_incomes');
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
         $this->addHTML('<div class="hr_title">Accounts Payable</div>');
         $this->addHTML('<div class="hr_content">');
        if (!empty($apProperties) && isset($apProperties['total'])) {
            $this->addContentBlock('$' . GI_StringUtils::formatMoney($apProperties['total'], true));
        } else {
            $this->addContentBlock('--');
        }
        $this->addHTML('</div><!--.hr_content-->');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<div class="hr_title">In Progress Expenses</div>');
        $this->addHTML('<div class="hr_content">');
        if (!empty($inProgressExpenses)) {
            $this->addContentBlock('$' . GI_StringUtils::formatMoney($inProgressExpenses, true));
        } else {
            $this->addContentBlock('--');
        }
        $this->addHTML('</div><!--.hr_content-->');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<div class="hr_title">Accounts Receivable</div>');
        $this->addHTML('<div class="hr_content">');
        if (!empty($arProperties) && isset($arProperties['total'])) {
            $this->addContentBlock('$' . GI_StringUtils::formatMoney($arProperties['total'], true));
        } else {
            $this->addContentBlock('--');
        }
        $this->addHTML('</div><!--.hr_content-->');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML('<div class="hr_title">In Progress Incomes</div>');
        $this->addHTML('<div class="hr_content">');
        if (!empty($inProgressIncomes)) {
            $this->addContentBlock('$' . GI_StringUtils::formatMoney($inProgressIncomes, true));
        } else {
            $this->addContentBlock('--');
        }
        $this->addHTML('</div><!--.hr_content-->');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

}
