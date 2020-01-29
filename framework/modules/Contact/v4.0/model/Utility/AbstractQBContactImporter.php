<?php
/**
 * Description of AbstractQBContactImporter
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
class AbstractQBContactImporter {

    protected $contactQBs = array();
    protected $includeSubCustomersAndJobs = true;
    protected $startPos = 0;

    public function __construct() {
        
    }

    public function setStartPos($startPos = 0) {
        $this->startPos = $startPos;
    }

    public function setIncludeSubCustomersAndJobs($include = true) {
        $this->includeSubCustomersAndJobs = $include;
    }
    
    public function importFromQB($contactQBTypeRef = 'customer') {
        if (empty($this->contactQBs)) {
            $sampleContactQB = ContactQBFactory::buildNewModel($contactQBTypeRef);
            $qbConnection = QBConnection::getInstance();
            if (empty($sampleContactQB) || empty($qbConnection)) {
                return false;
            }
            $apiTableName = $sampleContactQB->getAPITableName();
            if (empty($apiTableName)) {
                return false;
            }
            $contactQBs = array();
            
            $query = 'SELECT * from ' . $apiTableName;
            
            if ($contactQBTypeRef == 'customer' && !$this->includeSubCustomersAndJobs) {
                $query .= " WHERE job=false";
            }
            $query.= ' StartPosition '.$this->startPos.' MaxResults 1000';
            try {
                $result = $qbConnection->Query($query);
                $error = $qbConnection->getLastError();
                if (!empty($error)) {
                    GI_URLUtils::redirectToQBError($error);
                }
                if (empty($result)) {
                    return true;
                } else {
                    $contactQBs = ContactQBFactory::importNewModelsFromQB($contactQBTypeRef, $result);
                }
            } catch (Exception $ex) {
                GI_URLUtils::redirectToError(6000, $ex->getMessage());
            }
            $this->contactQBs = $contactQBs;
        }
        return true;
    }

    public function createContacts($contactCatTypeRef = 'client') {
        if (!empty($this->contactQBs)) {
            foreach ($this->contactQBs as $contactQB) {
                $contactOrg = $contactQB->createAndSaveContactFromData('org', $contactCatTypeRef);
                $contactInd = $contactQB->createAndSaveContactFromData('ind', $contactCatTypeRef);

                if (!empty($contactOrg) && !empty($contactInd)) {
                    if (!ContactFactory::linkContactAndContact($contactOrg, $contactInd)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function getGreatestQBId($contactQBTypeRef) {
        $greatestQBId = 0;
        $contactQBSearch = ContactQBFactory::search()
                ->filterByTypeRef($contactQBTypeRef)
                ->setPageNumber(1)
                ->setItemsPerPage(1)
                ->orderBy('qb_id', 'DESC');
        $searchResult = $contactQBSearch->select();
        if (!empty($searchResult)) {
            $model = $searchResult[0];
            $greatestQBId = $model->getProperty('qb_id');
        }
        return $greatestQBId;
    }

}
