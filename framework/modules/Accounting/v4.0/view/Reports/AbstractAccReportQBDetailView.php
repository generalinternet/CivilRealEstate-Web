<?php
/**
 * Description of AbstractAccReportQBDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccReportQBDetailView extends AbstractAccReportDetailView {
    
    protected function buildViewBody() {
        $tableView = new AccReportQBTableView($this->accReport);
        $this->addHTML($tableView->getHTMLView());
    }
    
}
