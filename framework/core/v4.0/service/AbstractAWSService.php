<?php

use Aws\Credentials\CredentialProvider;
use Aws\Sqs\SqsClient;

/**
 * Description of AbstractAWSService
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.3
 */
abstract class AbstractAWSService extends GI_Service {
    
    protected static $sqsClient = NULL;
    
    protected static $credentialProvider = NULL;
    
    public static function getCredentialProvider() {
        if (!DEV_MODE && empty(static::$credentialProvider)) {
            $provider = CredentialProvider::defaultProvider();
            static::$credentialProvider = CredentialProvider::memoize($provider);
        }
        return static::$credentialProvider;
    }
    
    public static function getSQSClient() {
        if (empty(static::$sqsClient)) {
            $client = SqsClient::factory(array(
                        'credentials' => static::getCredentialProvider(),
                        'region' => ProjectConfig::getAWSRegion(),
                        'version'=>'latest',
            ));
            static::$sqsClient = $client;
        }
        return static::$sqsClient;
    }
    
    public static function getSQSQueueURL() {
        return ProjectConfig::getAWSSQSQueueURL();
    }

    public static function sendMessageToSQSQueue($type, $attributes, $delaySeconds = 0) {
        $sqsClient = static::getSQSClient();
        if (empty($sqsClient)) {
            return false;
        }
        $queueURL = static::getSQSQueueURL();
        if (empty($queueURL)) {
            return false;
        }
        $messageProperties = array(
            'QueueUrl' => $queueURL,
            'MessageBody' => 'MESSAGE',
            'DelaySeconds' => $delaySeconds,
        );
        $attributes['type'] = $type;
        $messageAttributes = array();
        foreach ($attributes as $key => $value) {
            $key = static::sanitizeSQSDAttributeKey($key);
            $attributeArray = array(
                'DataType' => 'String'
            );
            if (is_array($value)) {
                $attributeArray['StringListValues'] = $value;
            } else {
                $attributeArray['StringValue'] = $value;
            }
            $messageAttributes[static::sanitizeSQSDAttributeKey($key)] = $attributeArray;
        }
        $messageProperties['MessageAttributes'] = $messageAttributes;

        $result = $sqsClient->sendMessage($messageProperties);
        return true;
    }

    public static function processSQSDPost() {
        $headerPrefix = 'HTTP_X_AWS_SQSD_ATTR_';
        $messageBody = trim(file_get_contents('php://input'));
        $typeHeader = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('type', 'receive')];
        if (empty($typeHeader)) {
            return;
        }
        $type = strtolower($typeHeader);
        switch ($type) {
            case 'notification':
                $notificationId = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('id', 'receive')];
                return static::processNotificationMessage($notificationId);
            case 'event_log':
            case 'user_activity_log':
                $timestapmp = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('timestamp', 'receive')];
                $userName = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('user-name', 'receive')];
                $subjectId = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('subject-id', 'receive')];
                $message = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('msg', 'receive')];
                $uid = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('uid', 'receive')];
                $eventId = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('event-id', 'receive')];
                $subjectFactoryClass = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('subject-factory-class', 'receive')];
                $logProperties = array(
                    'timestamp' => $timestapmp,
                    'user_name' => $userName,
                    'subject_id' => $subjectId,
                    'message' => $message,
                    'uid' => $uid,
                    'event_id' => $eventId,
                    'subject_factory_class' => $subjectFactoryClass
                );
                return static::processLogMessage($logProperties, $type);
            case 'project_milestone':
                $idsString = (string) $_SERVER[$headerPrefix . static::sanitizeSQSDAttributeKey('ids', 'receive')];
                return static::processProjectMilestoneMessage($idsString);
            default:
                break;
        }
        return true;
    }

    protected static function processNotificationMessage($notificationId) {
        $notification = NotificationFactory::getModelById($notificationId);
        if (empty($notification)) {
            return false;
        }
        if (!empty($notification->getProperty('in_system'))) {
            NotificationService::notifyUserInSystem($notification);
        }
        if (!empty($notification->getProperty('text'))) {
            NotificationService::notifyUserByText($notification);
        }
        if (!empty($notification->getProperty('immediate_email'))) {
            NotificationService::notifyUserByEmail($notification);
        }
        return true;
    }

    protected static function processLogMessage($properties, $type = 'event_log') {
        $eventId = $properties['event_id'];
        $event = EventFactory::getModelById($eventId);
        if (empty($event)) {
            return false;
        }
        $subjectModelId = $properties['subject_id'];
        $subjectFacotryClassName = $properties['subject_factory_class'];
        if (!class_exists($subjectFacotryClassName)) {
            return false;
        }
        $subjectModel = $subjectFacotryClassName::getModelById($subjectModelId);
        if (empty($subjectModel)) {
            return false;
        }
        $event->setSubjectModel($subjectModel);
        
        $fromUser = UserFactory::getModelById($properties['uid']);
        $event->setFromUser($fromUser);
        
        $logEntry = array(
            'timestamp' => $properties['timestamp'],
            'user_name' => $properties['user_name'],
            'event_title' => $event->getTypeTitle(),
            'subject_id' => $properties['subject_id'],
            'msg' => $properties['message'],
        );
        if ($type == 'event_log') {
            return LogService::logEventByType($event, $logEntry);
        } else if ($type == 'user_activity_log') {
            return LogService::logEventByUser($event, $logEntry);
        }
        return true;
    }
    
    protected static function processProjectMilestoneMessage($idsString) {
        if (empty($idsString)) {
            return;
        }
        $ids = explode(',', $idsString);
        if (empty($ids)) {
            return;
        }
        $waitingCacheKeyPrefix = 'pmile_waiting_';
        $evaluationStartCacheKeyPrefix = 'pmile_eval_start_';
        $evalHoldSeconds = 60;

        foreach ($ids as $milestoneId) {
            $startKey = $evaluationStartCacheKeyPrefix . $milestoneId;
            $waitingKey = $waitingCacheKeyPrefix . $milestoneId;
            if (apcu_exists($startKey)) {
                if (!apcu_exists($waitingKey)) {
                    apcu_add($waitingKey, '1', $evalHoldSeconds);
                }
            } else {
                $startDateTime = new DateTime();
                $startTimestamp = $startDateTime->getTimestamp();
                apcu_add($startKey, '1', $evalHoldSeconds);
                $milestone = ProjectMilestoneFactory::getModelById($milestoneId);

                if (empty($milestone)) {
                    return true;
                }
                $reachedPrior = $milestone->getProperty('target_reached_on_date');
                if (empty($reachedPrior) && $milestone->evaluate()) {
                    static::triggerEventMilestoneReached($milestone);
                }
                $endDateTime = new DateTime();
                $endTimestamp = $endDateTime->getTimestamp();

                $wait = $evalHoldSeconds - ($endTimestamp - $startTimestamp);
                sleep($wait);

                if (apcu_exists($waitingKey)) {
                    apcu_delete($waitingKey);
                    if (apcu_exists($startKey)) {
                        apcu_delete($startKey);
                    }
                    apcu_add($startKey, '1', $evalHoldSeconds);
                    $milestoneCopy = ProjectMilestoneFactory::getModelById($milestoneId);
                    if (empty($milestoneCopy->getProperty('target_reached_on_date')) && $milestoneCopy->evaluate()) {
                        static::triggerEventMilestoneReached($milestoneCopy);
                    }
                }

                if (apcu_exists($waitingKey)) {
                    apcu_delete($waitingKey);
                }
                
                if (apcu_exists($startKey)) {
                    apcu_delete($startKey);
                }
            }
        }
        return true;
    }

    protected static function triggerEventMilestoneReached($milestone) {
        $event = EventFactory::getModelByRefAndTypeRef('milestone_reached', 'project');
        $systemUser = UserFactory::getSystemUser();
        if (empty($event) || empty($systemUser)) {
            return false;
        }
        $project = $milestone->getProject();
        if (empty($project)) {
            return false;
        }
        $event->setSubjectModel($project);
        $event->setSubjectMilestone($milestone);
        $event->setFromUser($systemUser);
        $message = $milestone->getTitle() . ' on ' . $project->getViewTitle(false) . ' ' . $project->getProperty('project_number') . ' has been reached';
        $event->setLogMessage($message);
        $event->setNotificationMessage($message);
        EventService::addEvent($event);
        if (EventService::processEvents()) {
            return true;
        }
        return false;
    }

    protected static function sanitizeSQSDAttributeKey($key, $action = 'send') {
        if ($action == 'send') {
            $key = str_replace("_", "-", $key);
            $key = strtolower($key);
        } else {
            $key = str_replace("-", "_", $key);
            $key = strtoupper($key);
        }
        return $key;
    }
    
   
}