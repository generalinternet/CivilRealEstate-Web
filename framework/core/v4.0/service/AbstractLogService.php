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
        $_SESSION[$key] = $val;
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
        if (isset($_SESSION['ignore_next_log_view_' . Login::getUserId()]) && $_SESSION['ignore_next_log_view_' . Login::getUserId()] === 1) {
            $_SESSION['ignore_next_log_view_' . Login::getUserId()] = 0;
            return true;
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
            if ($recentActivityTypeRef === 'view' && isset($_SESSION['ignore_next_log_view_' . Login::getUserId()]) && ($_SESSION['ignore_next_log_view_' . Login::getUserId()] === 1)) {
                $_SESSION['ignore_next_log_view_' . Login::getUserId()] = 0;
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
    
    

}
