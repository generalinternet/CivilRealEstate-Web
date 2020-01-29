<?php
/**
 * Description of AbstractSuspensionSummaryView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */

abstract class AbstractSuspensionSummaryView extends MainWindowView {
    
    protected $contact;
    
    public function __construct(AbstractContact $contact) {
        parent::__construct();
        $this->contact = $contact;
    }
    
    protected function addViewBodyContent() {
        $this->addStatusSection();
        $this->addSuspensionHistorySection();
    }
    
    protected function addStatusSection() {
       $this->addContentBlock($this->contact->getSuspendedStatus(), 'Current Status');
    }
    
    protected function addSuspensionHistorySection() {
        $tableView = $this->contact->getSuspensionTableView();
        if (!empty($tableView)) {
            $this->addHTML('<h3>Suspension(s)</h3>');
            $tableView->setOnlyBodyContent(true);
            $this->addHTML($tableView->getHTMLView());
        }
    }
    
}