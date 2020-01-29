<?php
/**
 * Description of AbstractLogService
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractLogService extends GI_Service {
    
    protected static $maxNumOfRecentActivityEntriesPerUser = 10;
    protected static $logsFolder = NULL;
    
    public static function getMaxNumOfRecentActivityEntriesPerUser() {
        return static::$maxNumOfRecentActivityEntriesPerUser;
    }
    
    /**
     * 
     * @param Boolean $ignoreNextLogView
     */
    public static function setIgnoreNextLogView($ignoreNextLogView = true) {
        $key = 'ignore_next_log_view_' . Login::getUserId();
        $val = 0;
        if ($ignoreNextLogView) {
            $val = 1;
        }
        SessionService::setValue($key, $val);
    }

    public static function logAdd(GI_Model $model, $memo = '', $iconClass = 'plus') {
        $recentActivity = RecentActivityFactory::getNextAvailableModel(Login::getUser(), 'add');
        if (empty($recentActivity)) {
            return false;
        }
        $recentActivity->setProperty('icon_class', $iconClass);
        return static::logRecentActivity($recentActivity, $model, $memo);
    }

    public static function logEdit(GI_Model $model, $memo = '', $iconClass = 'pencil') {
        $recentActivity = RecentActivityFactory::getNextAvailableModel(Login::getUser(), 'edit');
        if (empty($recentActivity)) {
            return false;
        }
        $recentActivity->setProperty('icon_class', $iconClass);
        return static::logRecentActivity($recentActivity, $model, $memo);
    }

    public static function logView(GI_Model $model, $memo = '', $iconClass = 'visible') {
        $ignoreNextLogView = SessionService::getValue('ignore_next_log_view_' . Login::getUserId());
        if (!empty($ignoreNextLogView) && $ignoreNextLogView === 1) {
            SessionService::setValue('ignore_next_log_view_' . Login::getUserId(), 0);
        }
        $recentActivity = RecentActivityFactory::getNextAvailableModel(Login::getUser(), 'view');
        if (empty($recentActivity)) {
            return false;
        }
        $recentActivity->setProperty('icon_class', $iconClass);
        return static::logRecentActivity($recentActivity, $model, $memo);
    }

    public static function logActivity($url = '', $memo = '', $iconClass = '', $recentActivityTypeRef = 'activity') {
        if (!empty($recentActivityTypeRef)) {
            $ignoreNextLogView = SessionService::getValue('ignore_next_log_view_' . Login::getUserId());
            if ($recentActivityTypeRef === 'view' && !empty($ignoreNextLogView) && $ignoreNextLogView === 1) {
                SessionService::setValue('ignore_next_log_view_' . Login::getUserId(), 0);
                return true;
            }
            $recentActivity = RecentActivityFactory::getNextAvailableModel(Login::getUser(), $recentActivityTypeRef);
            if (empty($recentActivity)) {
                return false;
            }
            $recentActivity->setProperty('url', $url);
            $recentActivity->setProperty('memo', $memo);
            $recentActivity->setProperty('icon_class', $iconClass);
            $recentActivity->setProperty('table_name', '0');
            $recentActivity->setProperty('item_id', '0');
            $mostRecentRecentActivity = RecentActivityFactory::getMostRecentModel(Login::getUser());
            if (!empty($mostRecentRecentActivity) && $recentActivity->equals($mostRecentRecentActivity)) {
                return true;
            } 
            if (!$recentActivity->save()) {
                return false;
            }
        }
        return true;
    }

    protected static function logRecentActivity(AbstractRecentActivity $recentActivity, GI_Model $model, $memo = '') {
        $url = NULL;
        $viewURLAttributes = $model->getViewURLAttrs();
        if (!empty($viewURLAttributes)) {
            $url = GI_URLUtils::buildURL($viewURLAttributes);
        }
        if (empty($memo)) {
            $memo = $model->getViewTitle(false);
        }
        $recentActivity->setProperty('url', $url);
        $recentActivity->setProperty('table_name', $model->getTableName());
        $recentActivity->setProperty('item_id', $model->getId());
        $recentActivity->setProperty('memo', $memo);
        $mostRecentRecentActivity = RecentActivityFactory::getMostRecentModel(Login::getUser());
        if (!empty($mostRecentRecentActivity) && $recentActivity->equals($mostRecentRecentActivity)) {
            return true;
        }
        if (!$recentActivity->save()) {
            return false;
        }
        return true;
    }
    
    public static function logEvent(AbstractEvent $event, $byType = true, $byUser = true) {
        $subjectModel = $event->getSubjectModel();
        if (empty($subjectModel)) {
            return;
        }
        $dateTime = $event->getEventDateTime();
        if (empty($dateTime)) {
            $dateTime = new DateTime(GI_Time::getDateTime());
        }
        $user = $event->getFromUser();
        $userName = 'Anonymous';
        if($user){
            $userName = $user->getFullName();
        }

        $logEntry = array(
            'timestamp' => $dateTime->format('Y-m-d H:i:s'),
            'user_name' => $userName,
            'event_title' => $event->getTypeTitle(),
            'subject_id' => $subjectModel->getId(),
            'msg' => $event->getLogMessage(),
        );
        if ($byType) {
            static::logEventByType($event, $logEntry);
        }
        if ($byUser) {
            static::logEventByUser($event, $logEntry);
        }
    }

    public static function logEventByType(AbstractEvent $event, $logEntry) {
        $subjectModel = $event->getSubjectModel();
        if (empty($subjectModel) || empty($subjectModel->getId())) {
            return;
        }
        if (!DEV_MODE && !ProjectConfig::getIsWorkerServer()) {
            $user = $event->getFromUser();
            $properties = array(
                'timestamp' => (string) $logEntry['timestamp'],
                'user_name' => (string) $logEntry['user_name'],
                'uid' => (string) $user->getId(),
                'subject_id' => (string) $subjectModel->getId(),
                'subject_factory_class' => (string) $subjectModel->getFactoryClassName(),
                'msg' => (string) $logEntry['msg'],
                'event_id' =>  (string) $event->getId(),
            );
            if (!AWSService::sendMessageToSQSQueue('event_log', $properties)) {
                return false;
            }
        } else {
            $fileName = 'event_log_' . $subjectModel->getTypeRef() . '_' . $subjectModel->getId();
            $s3Path = 'Logs/Events/By Type/' . $event->getTypeTitle() . '/' . $fileName . '.csv';
            $localPath = 'tempData/logs/events/byType/' . $event->getTypeTitle();
            File::createTempDataFolders($localPath);
            $csv = new GI_CSV(GI_Sanitize::filename($fileName));
            $csv->setCSVPath($localPath);
            $csv->setOverWrite(false);
            if (File::doesS3FileExist($s3Path)) {
                File::saveFileFromS3(ProjectConfig::getAWSBucket(), $s3Path, $localPath . '/');
                $csv->setAddToExisting(true);
            }
            $csv->addRow($logEntry);
            GI_CSV::setCSVExporting(false);
            $csvFilePath = $csv->getCSVFilePath();
            File::saveToS3($csvFilePath, $s3Path);
            File::deleteDir($localPath);
            $eventsLogFolder = $subjectModel->getEventsLogFolder();
            if (!empty($eventsLogFolder)) {
                $file = $eventsLogFolder->getFile($fileName . '.csv');
                if (empty($file)) {
                    $file = FileFactory::buildNewModel('file');
                    $file->setProperty('display_name', 'Event Log: ' . $subjectModel->getTypeTitle() . ' ' . $subjectModel->getId() . '.csv');
                    $file->setProperty('filename', $fileName . '.csv');
                    $file->setProperty('aws_s3_bucket', ProjectConfig::getAWSBucket());
                    $file->setProperty('aws_s3_key', $s3Path);
                    $file->setProperty('aws_region', ProjectConfig::getAWSRegion());
                    $file->setProperty('system', 1);

                    if (!($file->save() && FolderFactory::linkFileToFolder($file, $eventsLogFolder))) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public static function logEventByUser(AbstractEvent $event, $logEntry) {
        $subjectModel = $event->getSubjectModel();
        if (empty($subjectModel) || empty($subjectModel->getId())) {
            return;
        }
        $user = $event->getFromUser();
        if (empty($user) || empty($user->getId())) {
            return;
        }
        if (!DEV_MODE && !ProjectConfig::getIsWorkerServer()) {
            $user = $event->getFromUser();
            $properties = array(
                'timestamp' => (string) $logEntry['timestamp'],
                'user_name' => (string) $logEntry['user_name'],
                'uid' => (string) $user->getId(),
                'subject_id' => (string) $subjectModel->getId(),
                'subject_factory_class' => (string) $subjectModel->getFactoryClassName(),
                'msg' => (string) $logEntry['msg'],
                'event_id' =>  (string) $event->getId(),
            );
            if (!AWSService::sendMessageToSQSQueue('user_activity_log', $properties)) {
                return false;
            }
        } else {
            $fileName = 'user_activity_log_' . $user->getId();
            $s3Path = 'Logs/Events/By User/' . 'user_' . $user->getId() . '/' . $fileName . '.csv';
            $localPath = 'tempData/logs/events/byUser';
            File::createTempDataFolders($localPath);
            $csv = new GI_CSV(GI_Sanitize::filename($fileName));
            $csv->setCSVPath($localPath);
            $csv->setOverWrite(false);
            if (File::doesS3FileExist($s3Path)) {
                File::saveFileFromS3(ProjectConfig::getAWSBucket(), $s3Path, $localPath . '/');
                $csv->setAddToExisting(true);
            }
            $csv->addRow($logEntry);
            GI_CSV::setCSVExporting(false);
            $csvFilePath = $csv->getCSVFilePath();
            File::saveToS3($csvFilePath, $s3Path);
            File::deleteDir($localPath);
            $eventsLogFolder = $user->getEventsLogFolder();
            if (!empty($eventsLogFolder)) {
                $file = $eventsLogFolder->getFile($fileName . '.csv');
                if (empty($file)) {
                    $file = FileFactory::buildNewModel('file');
                    $file->setProperty('display_name', 'User Activity Log: User ' . $user->getId() . '.csv');
                    $file->setProperty('filename', $fileName . '.csv');
                    $file->setProperty('aws_s3_bucket', ProjectConfig::getAWSBucket());
                    $file->setProperty('aws_s3_key', $s3Path);
                    $file->setProperty('aws_region', ProjectConfig::getAWSRegion());
                    $file->setProperty('system', 1);

                    if (!($file->save() && FolderFactory::linkFileToFolder($file, $eventsLogFolder))) {
                        return false;
                    }
                }
            } 
        }
        return true;
    }

    public static function getLogsFolder() {
        if (empty(static::$logsFolder)) {
            $search = FolderFactory::search();
            $search->filter('ref', 'logs')
                    ->filter('is_root', 1)
                    ->filter('system', 1);
            $results = $search->select();
            if (!empty($results)) {
                static::$logsFolder = $results[0];
            } else {
                $folder = FolderFactory::buildNewModel();
                $folder->setProperty('ref', 'logs')
                        ->setProperty('title', 'Logs')
                        ->setProperty('is_root', 1)
                        ->setProperty('system', 1);
                if ($folder->save()) {
                    static::$logsFolder = $folder;
                }
            }
        }
        return static::$logsFolder;
    }
    

    
    
    
    

}
