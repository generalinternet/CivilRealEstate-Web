<?php

abstract class AbstractREListingStatus extends GI_Model {
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
    public function getRef(){
        return $this->getProperty('ref');
    }
    
}
