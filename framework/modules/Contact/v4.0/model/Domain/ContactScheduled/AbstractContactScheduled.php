<?php
/**
 * Description of AbstractContactScheduled
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactScheduled extends GI_Model {
    
    /** @var AbstractTimeInterval */
    protected $timeInterval = NULL;
    /** @var AbstractContact */
    protected $contact = NULL;
    
    /** @return AbstractTimeInterval */
    public function getTimeInterval(){
        if(is_null($this->timeInterval)){
            $this->timeInterval = TimeIntervalFactory::getModelById($this->getProperty('time_interval_id'));
        }
        return $this->timeInterval;
    }
    
    /**
     * @param AbstractTimeInterval $timeInterval
     * @return $this
     */
    public function setTimeInterval(AbstractTimeInterval $timeInterval){
        $this->timeInterval = $timeInterval;
        return $this;
    }
    
    /** @return AbstractContact */
    public function getContact(){
        if(is_null($this->contact)){
            $this->contact = ContactFactory::getModelById($this->getProperty('contact_id'));
        }
        return $this->contact;
    }
    
    /**
     * @param AbstractContact $contact
     * @return $this
     */
    public function setContact(AbstractContact $contact){
        $this->contact = $contact;
        return $this;
    }
    
}
