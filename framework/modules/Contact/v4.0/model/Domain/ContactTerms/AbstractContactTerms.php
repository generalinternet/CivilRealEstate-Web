<?php

abstract class AbstractContactTerms extends GI_Model {
    
    public function getDueDate($issueDate){
        $issueDateObj = new DateTime($issueDate);
        if($this->getProperty('fixed')){
            $days = $this->getProperty('days');
            $issueDateObj->add(new DateInterval('P' . $days . 'D'));
            return $issueDateObj->format('Y-m-d');
        } else {
            $day = $this->getProperty('day_of_month');
            $dueDateObj = new DateTime($issueDateObj->format('Y-m-' . $day));
            $dueDate = $dueDateObj->format('Y-m-d');
            
            $dayLeeway = $this->getProperty('day_of_month_leeway');
            $leewayDateObj = new DateTime($dueDate);
            $leewayDateObj->sub(new DateInterval('P' . $dayLeeway . 'D'));
            //$leewayDate = $leewayDateObj->format('Y-m-d');
            
            if($dueDateObj >= $issueDateObj && $leewayDateObj >= $issueDateObj){
                return $dueDate;
            } else {
                $issueDateObj->add(new DateInterval('P1M'));
                return $issueDateObj->format('Y-m-' . $day);
            }
        }
    }
    
}
