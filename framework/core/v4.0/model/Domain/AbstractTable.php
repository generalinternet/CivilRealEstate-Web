<?php

class AbstractTable extends GI_Model {
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
    public function getTableName(){
        return $this->getProperty('system_title');
    }
    
}
