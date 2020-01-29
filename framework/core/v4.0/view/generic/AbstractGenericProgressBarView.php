<?php
/**
 * Description of AbstractGenericProgressBarView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.2
 */
abstract class AbstractGenericProgressBarView extends GI_View {

    protected $percentage = 0;
    protected $percentagePrecision = 2;
    protected $completedTitle = 'Completed';
    protected $onlyBuildBar = false;
    protected $progressBarTitle = '';
    protected $progressDesc = '';
    protected $otherBarInfo = '';
    protected $buildingBar = false;
    protected $progressForward = true;
    protected $nextURL = NULL;
    protected $timeTrackerId = NULL;
    protected $addTitle = true;

    public function __construct() {
        parent::__construct();
    }
    
    public function setOnlyBuildBar($onlyBuildBar){
        $this->onlyBuildBar = $onlyBuildBar;
        return $this;
    }
    
    public function setTimeTrackerId($timeTrackerId){
        $this->timeTrackerId = $timeTrackerId;
        return $this;
    }
    
    public function setAddTitle($addTitle){
        $this->addTitle = $addTitle;
        return $this;
    }
    
    protected function openViewWrap(){
        if($this->isProgressing() && GI_URLUtils::isAJAX()){
            $nextURL = $this->getNextURL();
            $this->addHTML('<div class="ajaxed_contents auto_load no_loading_class" data-url="' . $nextURL . '">');
        }
        if(!$this->onlyBuildBar){
            $this->addHTML('<div class="view_wrap">');
        }
        return $this;
    }
    
    protected function closeViewWrap(){
        if($this->isProgressing() && GI_URLUtils::isAJAX()){
            $this->addHTML('</div>');
        }
        if(!$this->onlyBuildBar){
            $this->addHTML('</div>');
        }
        return $this;
    }
    
    protected function buildView(){
        $this->buildingBar = true;
        $this->openViewWrap();
        if(!$this->onlyBuildBar){
            if(!empty($this->progressBarTitle) && $this->addTitle){
                $this->addSiteTitle($this->progressBarTitle);
                $this->addHTML('<div class="view_header">');
                $this->addMainTitle($this->progressBarTitle);
                $this->addHTML('</div>');
            }
            if($this->isProgressing()){
                $this->addHTML('<div class="gear_wrap">')
                    ->addHTML('<div class="gears"></div>');
                if(!empty($this->progressDesc)){
                    $this->addHTML('<p>' . $this->progressDesc . '</p>');
                }
                $this->addHTML('</div>');
            }
        }
        $this->addHTML('<div class="view_body">');
        $this->addLoadingBar();
        
        $this->addTimeString();
        
        $this->addOtherBarInfo();
        $this->addHTML('</div>');
        $this->closeViewWrap();
    }
    
    /**
     * @param string $nextURL
     * @return \AbstractGenericProgressBarView
     */
    public function setNextURL($nextURL){
        $this->nextURL = $nextURL;
        return $this;
    }
    
    public function getNextURL(){
        return $this->nextURL;
    }
    
    /**
     * @param boolean $progressForward
     * @return \AbstractGenericProgressBarView
     */
    public function setProgressForward($progressForward){
        $this->progressForward = $progressForward;
        return $this;
    }
    
    protected function addOtherBarInfo(){
        $this->addHTML($this->otherBarInfo);
    }

    public function addHTML($html){
        if($this->buildingBar){
            return parent::addHTML($html);
        } else {
            $this->otherBarInfo .= $html;
            return $this;
        }
    }

    /**
     * @param string $progressBarTitle
     * @return \AbstractGenericProgressBarView
     */
    public function setProgressBarTitle($progressBarTitle){
        $this->progressBarTitle = $progressBarTitle;
        return $this;
    }
    
    /**
     * @param string $progressDesc
     * @return \AbstractGenericProgressBarView
     */
    public function setProgressDesc($progressDesc){
        $this->progressDesc = $progressDesc;
        return $this;
    }
    
    /**
     * @param float $percentage
     * @return \AbstractGenericProgressBarView
     */
    public function setPercentage($percentage){
        $this->percentage = $percentage;
        return $this;
    }
    
    /**
     * @param integer $percentagePrecision
     * @return \AbstractGenericProgressBarView
     */
    public function setPercentagePrecision(int $percentagePrecision){
        $this->percentagePrecision = $percentagePrecision;
        return $this;
    }
    
    public function getPercentage(){
        if($this->percentage > 100){
            return 100;
        }
        if($this->percentage < 0){
            return 0;
        }
        return round($this->percentage, $this->percentagePrecision);
    }
    
    /**
     * @param string $completedTitle
     * @return \AbstractGenericProgressBarView
     */
    public function setCompletedTitle($completedTitle){
        $this->completedTitle = $completedTitle;
        return $this;
    }
    
    public function getCompletedText(){
        return $this->getPercentage() . '% ' . $this->completedTitle;
    }
    
    public function isProgressing(){
        $nextURL = $this->getNextURL();
        if($this->progressForward && $nextURL){
            return true;
        }
        return false;
    }
    
    protected function getTopBarClass(){
        $percentage = $this->getPercentage();
        $topBarClass = '';
        if($percentage == 0){
            $topBarClass = 'hide_bar';
        }
        if($percentage == 100){
            $topBarClass = 'no_border';
        }
        return $topBarClass;
    }
    
    protected function addLoadingBar(){
        $percentage = $this->getPercentage();
        
        $topBarClass = $this->getTopBarClass();
        
        $widthStyle = 'style="width: ' . $percentage . '%;"';
        $completedText = $this->getCompletedText();
        $this->addHTML('<div class="progress_bar_wrap">')
                ->addHTML('<div class="progress_bar">')
                    ->addHTML('<span class="bar ' . $topBarClass . '" ' . $widthStyle . '>' . $completedText . '</span>')
                    ->addHTML('<span class="bar back" ' . $widthStyle . '>' . $completedText . '</span>')
                ->addHTML('</div>')
            ->addHTML('</div>');
    }
    
    protected function verifyTrackTime(){
        if(!is_null($this->timeTrackerId)){
            $curDateTime = GI_Time::getDateTime();
            $percentage = $this->getPercentage();
            $timeTrackerId = $this->timeTrackerId;
            $sessionTimeTrackerArray = SessionService::getValue(array(
                        'timeTrackerIds',
                        $timeTrackerId
            ));
            if (empty($sessionTimeTrackerArray)) {
                SessionService::setValue(array(
                    'timeTrackerIds',
                    $timeTrackerId
                        ), array(
                    'startTime' => $curDateTime,
                    'lastTime' => $curDateTime,
                    'lastPercentage' => $percentage,
                    'perTrackTime' => NULL,
                    'endTime' => NULL
                ));
            } elseif ($this->getPercentage() == 100) {
                $endTime = SessionService::getValue(array(
                            'timeTrackerIds',
                            $timeTrackerId,
                            'endTime'
                ));
                if (empty($endTime)) {
                    SessionService::setValue(array(
                        'timeTrackerIds',
                        $timeTrackerId,
                        'endTime'
                            ), $curDateTime);
                }
            } else {
                $lastTime = SessionService::getValue(array(
                            'timeTrackerIds',
                            $timeTrackerId,
                            'lastTime',
                ));
                $perTrackTime = GI_Time::getSecondsBetween($lastTime, $curDateTime);
                SessionService::setValue(array(
                    'timeTrackerIds',
                    $timeTrackerId,
                    'perTrackTime'
                        ), $perTrackTime);
            }
        }
    }

    protected function trackTime() {
        if (!is_null($this->timeTrackerId)) {
            $curDateTime = GI_Time::getDateTime();
            $percentage = $this->getPercentage();
            $timeTrackerId = $this->timeTrackerId;

            $sessionValues = SessionService::getValue(array(
                        'timeTrackerIds',
                        $timeTrackerId,
            ));
            if (empty($sessionValues)) {
                $this->verifyTrackTime();
            } else {
                $startTime = $this->getStartTime();
                $sinceStart = GI_Time::getSecondsBetween($startTime, $curDateTime);
                $perTrackTime = $sinceStart / $percentage;

                SessionService::setValue(array(
                    'timeTrackerIds',
                    $timeTrackerId,
                    'lastTime'
                        ), $curDateTime);
                SessionService::setValue(array(
                    'timeTrackerIds',
                    $timeTrackerId,
                    'perTrackTime',
                        ), $perTrackTime);
                SessionService::setValue(array(
                    'timeTrackerIds',
                    $timeTrackerId,
                    'lastPercentage',
                        ), $percentage);
            }
        }
    }

    protected function getStartTime() {
        $timeTrackerId = $this->timeTrackerId;
        $startTime = SessionService::getValue(array(
            'timeTrackerIds',
            $timeTrackerId,
            'startTime'
        ));
        if (!empty($startTime)) {
            return GI_Time::formatDateTimeForDisplay($startTime);
        }
        return NULL;
    }
    
    protected function getEstimatedTimeRemaining(){
        $timeTrackerId = $this->timeTrackerId;
        $endTime = SessionService::getValue(array(
                    'timeTrackerIds',
                    $timeTrackerId,
                    'endTime'
        ));
        if (!empty($endTime)) {
            return NULL;
        }
        $perTrackTime = SessionService::getValue(array(
            'timeTrackerIds',
            $timeTrackerId,
            'perTrackTime',
        ));
        if (!empty($perTrackTime)) {
            $lastPercentage = SessionService::getValue(array(
                'timeTrackerIds',
                $timeTrackerId,
                'lastPercentage',
            ));
            $curPercentage = $this->getPercentage();
            $percentageBlock = $curPercentage - $lastPercentage;
            $percentageLeft = 100 - $curPercentage;
            if($percentageBlock != 0){
                $percentageBlocksLeft = $percentageLeft / $percentageBlock;
                $secondsLeft = round($percentageBlocksLeft * $perTrackTime);
                $estimatedEndDateTime = new DateTime();
                if($secondsLeft > 0) {
                    $estimatedEndDateTime->modify('+' . $secondsLeft . ' second');
                    $estimatedEnd = GI_Time::formatDateTime($estimatedEndDateTime);
                    return GI_Time::formatTimeUntil($estimatedEnd);
                }
            }
        }
        return '<i>unknown</i>';
    }

    protected function getEndTime() {
        $timeTrackerId = $this->timeTrackerId;
        $endTime = SessionService::getValue(array(
            'timeTrackerIds',
            $timeTrackerId,
            'endTime'
        ));
        if (!empty($endTime)) {
            return GI_Time::formatDateTimeForDisplay($endTime);
        }
        return NULL;
    }
    
    protected function getTimeTaken(){
        $timeTrackerId = $this->timeTrackerId;
        $endTime = SessionService::getValue(array(
            'timeTrackerIds',
            $timeTrackerId,
            'endTime'
        ));
        if (empty($endTime)) {
            return NULL;
        }
        $startTime = SessionService::getValue(array(
            'timeTrackerIds',
            $timeTrackerId,
            'startTime'
        ));
        return GI_Time::formatTimeSince($startTime, $endTime);
    }
    
    protected function addTimeString(){
        if(!is_null($this->timeTrackerId)){
            $this->addHTML('<div class="progress_time_tracker">');
                $this->addContentBlock($this->getStartTime(), 'Start Time');
                $this->addContentBlock($this->getEstimatedTimeRemaining(), 'Estimated Time Remaining');
                $this->addContentBlock($this->getEndTime(), 'End Time');
                $this->addContentBlock($this->getTimeTaken(), 'Time Taken');
            $this->addHTML('</div>');
        }
    }
    
    public function beforeReturningView() {
        $this->verifyTrackTime();
        $this->buildView();
        $this->trackTime();
        $nextURL = $this->getNextURL();
        if($this->isProgressing()){
            Header('Refresh: 1;url=' . $nextURL);
        }
    }
    
}
