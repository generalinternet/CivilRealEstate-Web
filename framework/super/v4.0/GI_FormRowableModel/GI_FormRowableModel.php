<?php
/**
 * Description of GI_FormRowableModel
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class GI_FormRowableModel extends GI_Model {
    
    protected $fieldSuffix = NULL;
    protected $fieldPrefix = NULL;
    protected $seqNumber = NULL;
    
    /**
     * @param string $fieldSuffix
     * @return \GI_FormRowableModel
     */
    public function setFieldSuffix($fieldSuffix){
        $this->fieldSuffix = $fieldSuffix;
        return $this;
    }
    
    public function getFieldSuffix(){
        return $this->fieldSuffix;
    }
    
    /**
     * @param string $fieldPrefix
     * @return \GI_FormRowableModel
     */
    public function setFieldPrefix($fieldPrefix){
        $this->fieldPrefix = $fieldPrefix;
        return $this;
    }
    
    public function getFieldPrefix(){
        return $this->fieldPrefix;
    }
    
    /**
     * @param $seqNumber
     * @return \GI_FormRowableModel
     */
    public function setSeqNumber($seqNumber) {
        $this->seqNumber = $seqNumber;
        return $this;
    }

    public function getSeqNumber() {
        return $this->seqNumber;
    }

    public function getFieldName($fieldName){
        if(!is_null($this->fieldPrefix)){
            $fieldName = $this->fieldPrefix . '_' . $fieldName;
        }
        if(!is_null($this->fieldSuffix)){
            $fieldName .= '_' . $this->fieldSuffix;
        }
        if(!is_null($this->seqNumber)){
            $fieldName .= '_' . $this->seqNumber;
        }
        return $fieldName;
    }
    
}
