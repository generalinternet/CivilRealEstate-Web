<?php
/**
 * Description of AbstractAccountingElement
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccountingElement extends GI_Model {
    
    protected $removedBy = NULL;
    protected $removedDate = NULL;
    protected $removedNotes = NULL;
    
    public function getIsVoid() {
        $void = $this->getProperty('void');
        if (!empty($void)) {
            return true;
        }
        return false;
    }
    
    public function isVoid(){
        return $this->getIsVoid();
    }

    public function getIsCancelled() {
        $cancelled = $this->getProperty('cancelled');
        if (!empty($cancelled)) {
            return true;
        }
    }
    
    public function isCancelled(){
        return $this->getIsCancelled();
    }

    public function getIsVoidOrCancelled() {
        if ($this->getIsCancelled()) {
            return true;
        }
        if ($this->getIsVoid()) {
            return true;
        }
        return false;
    }

    public function getIsVoidable() {
        if ($this->getIsVoidOrCancelled()) {
            return false;
        }
        if (!$this->getIsLocked()) {
            return true;
        }
        return false;
    }

    public function getIsCancellable() {
        if ($this->getIsVoidOrCancelled()) {
            return false;
        }
        if (!$this->getIsLocked()) {
            return true;
        }
        return false;
    }

    public function getIsLocked() {
        return true;
    }
    
    public function getRemovedBy() {
        if (empty($this->removedBy)) {
            $removedById = $this->getProperty('removed_by_id');
            if (!empty($removedById)) {
                $this->removedBy = UserFactory::getModelById($removedById);
            }
        }
        return $this->removedBy;
    }
    
    public function getRemovedDate() {
        if (empty($this->removedDate)) {
            $this->removedDate = $this->getProperty('removed_date');
        }
        return $this->removedDate;
    }
    
    public function getRemovedNotes() {
        if (empty($this->removedNotes)) {
            $this->removedNotes = $this->getProperty('removed_note');
        }
        return $this->removedNotes;
    }

}
