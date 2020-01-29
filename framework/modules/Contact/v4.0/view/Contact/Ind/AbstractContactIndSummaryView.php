<?php
/**
 * Description of AbstractContactIndSummaryView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactIndSummaryView extends AbstractContactSummaryView {
    
    /** @var ContactInd */
    protected $contact;
    
    public function buildView() {
        $this->openViewWrap();
        $name = $this->contact->getName();
            $this->addHTML($name);
            if(!empty($this->relationship) && !empty($this->relationship->getProperty('title'))){
                $this->addHTML(' (' . $this->relationship->getProperty('title') . ')');
            }
        $this->closeViewWrap();
    }
    
}