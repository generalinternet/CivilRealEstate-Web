<?php
/**
 * Description of AbstractQBTaxRate
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */

abstract class AbstractQBTaxRate extends GI_Model {
    
    /**
     * Returns data as an array, for easier caching
     */
    public function getDataArray() {
        $data = array();
        $data['rate'] = $this->getProperty('rate');
        $data['name'] = $this->getProperty('name');
        $data['description'] = $this->getProperty('description');
        $data['effective_date'] = $this->getProperty('effective_date');
        $data['end_date'] = $this->getProperty('end_date');
        return $data;
    }
    
}