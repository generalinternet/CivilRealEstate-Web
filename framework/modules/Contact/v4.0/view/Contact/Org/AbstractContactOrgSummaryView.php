<?php
/**
 * Description of AbstractContactOrgSummaryView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactOrgSummaryView extends AbstractContactSummaryView {
    
    /** @var ContactOrg */
    protected $contact;
    
    public function buildView() {
        $this->openViewWrap();
        $name = $this->contact->getName();
            $this->addHTML($name);
            if(!empty($this->relationship) && !empty($this->relationship->getProperty('title'))){
                $this->addHTML(' (' . $this->relationship->getProperty('title') . ')');
            }
            $phoneNumber = $this->contact->getPhoneNumber();
            if(!empty($phoneNumber)){
                $this->addHTML('<br/>' . $phoneNumber);
            }
        $this->closeViewWrap();
    }
    
}