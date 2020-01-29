<?php
/**
 * Description of AbstractContactLocSummaryView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.2
 */
abstract class AbstractContactLocSummaryView extends AbstractContactSummaryView {
    
    /** @var ContactLoc */
    protected $contact;
    
    public function buildView() {
        $this->openViewWrap();
        $name = $this->contact->getName();
            $this->addHTML($name . ' (' . $this->contact->getTypeTitle() . ')');
        $this->closeViewWrap();
    }
    
}
