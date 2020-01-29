<?php
/**
 * Description of AbstractEventInstaller
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractEventInstaller {

    protected static $projectEvents = array(
        array(
            'title' => 'Created',
            'ref' => 'created',
            'hidden_from_users' => 0,
            'pos' => 10,
        ),
        array(
            'title' => 'Marked in Progress',
            'ref' => 'marked_in_progress',
            'hidden_from_users' => 0,
            'pos' => 20,
        ),
        array(
            'title' => 'Milestone Reached',
            'ref' => 'milestone_reached',
            'hidden_from_users' => 0,
            'pos' => 30,
        ),
    );
    protected static $purchaseOrderEvents = array(
        array(
            'title' => 'Marked in Progress',
            'ref' => 'marked_in_progress',
            'hidden_from_users' => 0,
            'pos' => 10,
        ),
    );
    protected static $salesOrderEvents = array(
        array(
            'title' => 'Marked in Progress',
            'ref' => 'marked_in_progress',
            'hidden_from_users' => 0,
            'pos' => 10,
        ),
    );
    protected static $qnaEvents = array(
        array(
            'title' => 'Question Asked',
            'ref' => 'question_asked',
            'hidden_from_users' => 0,
            'pos' => 10,
        ),
        array(
            'title' => 'Question Answered',
            'ref' => 'question_answered',
            'hidden_from_users' => 0,
            'pos' => 20,
        ),
        array(
            'title' => 'Answer Replied To',
            'ref' => 'answer_replied_to',
            'hidden_from_users' => 0,
            'pos' => 30,
        ),
    );

    protected function installEvent($data, $typeRef) {
        $search = EventFactory::search();
        $search->filterByTypeRef($typeRef)
                ->filter('ref', $data['ref']);
        $results = $search->select();
        if (!empty($results)) {
            $eventModel = $results[0];
        } else {
            $eventModel = EventFactory::buildNewModel($typeRef);
            foreach ($data as $colKey => $value) {
                $eventModel->setProperty($colKey, $value);
            }
            if (!$eventModel->save()) {
                return false;
            }
        }
        return true;
    }
    
    public function installProjectEvents() {
        if (!dbConnection::isModuleInstalled('project')) {
            return true;
        }
        $events = static::$projectEvents;
        if (!empty($events)) {
            foreach ($events as $data) {
                if (!$this->installEvent($data, 'project')) {
                    return false;
                } 
            }
        }
        return true;
    }
    
    public function installPurchaseOrderEvents() {
        if (!dbConnection::isModuleInstalled('order')) {
         return true;   
        }
        $events = static::$purchaseOrderEvents;
        if (!empty($events)) {
            foreach ($events as $data) {
                if(!$this->installEvent($data, 'purchase_order')) {
                    return false;
                }
            }
        }
        return true;
    }

    public function installSalesOrderEvents() {
        if (!dbConnection::isModuleInstalled('order')) {
            return true;
        }
        $events = static::$purchaseOrderEvents;
        if (!empty($events)) {
            foreach ($events as $data) {
                if (!$this->installEvent($data, 'sales_order')) {
                    return false;
                }
            }
        }
        return true;
    }

    public function installQnAEvents() {
        if (!dbConnection::isModuleInstalled('qna')) {
            return true;
        }
        $events = static::$qnaEvents;
        if (!empty($events)) {
            foreach ($events as $data) {
                if (!$this->installEvent($data, 'qna')) {
                    return false;
                }
            }
        }
        return true;
    }

}
