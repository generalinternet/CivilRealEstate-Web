<?php
/**
 * Description of AbstractUICardView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class AbstractUICardView extends GI_View{

    /** @var GI_Model */
    protected $model = NULL;
    protected $url = '';
    protected $urlMethod = 'getViewURL';
    protected $targetId = '';
    protected $tabTitle = '';
    protected $title = '';
    protected $hoverTitle = '';
    protected $subtitle = '';
    protected $summary = '';
    protected $topRight = '';
    protected $cardClass = '';
    protected $cardContentClass = '';
    protected $tabClass = '';
    protected $headerClass = '';
    protected $topRightClass = '';
    protected $titleClass = '';
    protected $subtitleClass = '';
    protected $summaryClass = '';
    protected $avatarHTML = '';
    protected $addTab = false;
    protected $addAvatar = false;
    protected $tabColour = '';
    protected $dataAttrs = array();
    
    public function __construct(GI_Model $model = NULL){
        parent::__construct();
        $this->model = $model;
    }
    
    /*****PROPERTIES*****/
    
    public function getURLMethod() {
        return $this->urlMethod;
    }
    
    public function getTargetId(){
        return $this->targetId;
    }
    
    public function getTabTitle() {
        return $this->tabTitle;
    }

    public function getTitle() {
        return $this->title;
    }
    
    public function getHoverTitle(){
        $hoverTitle = $this->hoverTitle;
        if(empty($hoverTitle)){
            $hoverTitle = $this->title;
        }
        return GI_Sanitize::htmlAttribute($hoverTitle);
    }

    public function getSubtitle() {
        return $this->subtitle;
    }

    public function getSummary() {
        return $this->summary;
    }

    public function getTopRight() {
        return $this->topRight;
    }
    
    public function getAvatarHTML(){
        return $this->avatarHTML;
    }
    
    public function getTabColour(){
        return $this->tabColour;
    }
    
    public function setURL($url){
        $this->url = $url;
        return $this;
    }
    
    public function setURLMethod($urlMethod) {
        $this->urlMethod = $urlMethod;
        return $this;
    }
    
    public function setTargetId($targetId){
        $this->targetId = $targetId;
        return $this;
    }

    public function setTabTitle($tabTitle) {
        $this->tabTitle = $tabTitle;
        if($tabTitle){
            $this->setAddTab(true);
        }
        return $this;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function setHoverTitle($hoverTitle) {
        $this->hoverTitle = $hoverTitle;
        return $this;
    }

    public function setSubtitle($subtitle) {
        $this->subtitle = $subtitle;
        return $this;
    }

    public function setSummary($summary) {
        $this->summary = $summary;
        return $this;
    }

    public function setTopRight($topRight) {
        $this->topRight = $topRight;
        return $this;
    }
    
    public function setAvatarHTML($avatarHTML){
        $this->avatarHTML = $avatarHTML;
        if(!empty($avatarHTML)){
            $this->setAddAvatar(true);
        }
        return $this;
    }
    
    public function setAddTab($addTab){
        $this->addTab = $addTab;
        return $this;
    }
    
    public function setAddAvatar($addAvatar){
        $this->addAvatar = $addAvatar;
        return $this;
    }
    
    public function setTabColour($colourString){
        $this->tabColour = $colourString;
    }
    
    public function addDataAttr($attr, $val = NULL){
        $this->dataAttrs[$attr] = $val;
        return $this;
    }
    
    /*****CLASSES*****/
    
    public function getCardClass() {
        return $this->cardClass;
    }
    
    public function getCardContentClass() {
        return $this->cardContentClass;
    }

    public function getTabClass() {
        return $this->tabClass;
    }
    
    public function getHeaderClass() {
        return $this->headerClass;
    }

    public function getTopRightClass() {
        return $this->topRightClass;
    }

    public function getTitleClass() {
        return $this->titleClass;
    }

    public function getSubtitleClass() {
        return $this->subtitleClass;
    }

    public function getSummaryClass() {
        return $this->summaryClass;
    }

    public function addCardClass($cardClass) {
        if(!empty($this->cardClass)){
            $this->cardClass .= ' ';
        }
        $this->cardClass .= $cardClass;
        return $this;
    }

    public function addCardContentClass($cardContentClass) {
        if(!empty($this->cardContentClass)){
            $this->cardContentClass .= ' ';
        }
        $this->cardContentClass .= $cardContentClass;
        return $this;
    }

    public function addTabClass($tabClass) {
        if(!empty($this->tabClass)){
            $this->tabClass .= ' ';
        }
        $this->tabClass .= $tabClass;
        return $this;
    }

    public function setHeaderClass($headerClass) {
        if(!empty($this->headerClass)){
            $this->headerClass .= ' ';
        }
        $this->headerClass .= $headerClass;
    }

    public function setTopRightClass($topRightClass) {
        if(!empty($this->$topRightClass)){
            $this->topRightClass .= ' ';
        }
        $this->topRightClass .= $topRightClass;
    }

    public function addTitleClass($titleClass) {
        if(!empty($this->titleClass)){
            $this->titleClass .= ' ';
        }
        $this->titleClass .= $titleClass;
        return $this;
    }

    public function addSubtitleClass($subtitleClass) {
        if(!empty($this->subtitleClass)){
            $this->subtitleClass .= ' ';
        }
        $this->subtitleClass .= $subtitleClass;
        return $this;
    }

    public function addSummaryClass($summaryClass) {
        if(!empty($this->summaryClass)){
            $this->summaryClass .= ' ';
        }
        $this->summaryClass .= $summaryClass;
        return $this;
    }
    
    /****************/
    
    public function getURL(){
        if(empty($this->url)){
            $urlMethod = $this->getURLMethod();
            if(!empty($urlMethod) && method_exists($this->model, $urlMethod)){
                $this->url = $this->model->$urlMethod();
            }
        }
        return $this->url;
    }
    
    protected function getDataAttrs(){
        return $this->dataAttrs;
    }
    
    public function getCardDataString(){
        $dataString = '';
        if(!empty($this->model)){
            $dataString .= ' data-model-id="' . $this->model->getId() . '"';
        }
        
        $url = $this->getURL();
        if(!empty($url)){
            $dataString .= ' data-url="' . $url . '"';
        }
        
        $targetId = $this->getTargetId();
        if(!empty($targetId)){
            $dataString .= ' data-target-id="' . $targetId . '"';
        }
        
        $dataAttrs = $this->getDataAttrs();
        if(!empty($dataAttrs)){
            foreach($dataAttrs as $attr => $val){
                $dataString .= ' data-' . $attr . '="' . $val . '" ';
            }
        }
        
        return $dataString;
    }
    
    protected function openCardWrap(){
        $hoverTitle = $this->getHoverTitle();
        
        $url = $this->getURL();
        if(!empty($url)){
            $this->addCardClass('tile_link');
            $this->addCardClass('card_link');
        }
        if($this->addTab){
            $this->addCardClass('has_tab');
        }
        if($this->addAvatar){
            $this->addCardClass('has_avatar');
        }
        $cardClass = $this->getCardClass();
        $this->addHTML('<div class="ui_tile ui_card ' . $cardClass . '" title="' . $hoverTitle . '"');
        
        $dataString = $this->getCardDataString();
        $this->addHTML($dataString);
        
        $this->addHTML('>');
        
        $cardContentClass = $this->getCardContentClass();
        $this->addHTML('<div class="ui_card_content ' . $cardContentClass . '">');
        return $this;
    }
    
    protected function closeCardWrap(){
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    protected function buildTab(){
        if($this->addTab){
            $tabClass = $this->getTabClass();
            $tabTitle = $this->getTabTitle();
            $tabColour = $this->getTabColour();
            $styleString = '';
            if($tabColour){
                $styleString = 'style="background:' . $tabColour . ';"';
            }
            $this->addHTML('<div class="card_tab ' . $tabClass . '" title="' . $tabTitle . '" ' . $styleString. '>');
                $this->addHTML($tabTitle);
            $this->addHTML('</div>');
        }
    }
    
    protected function buildAvatar(){
        if($this->addAvatar){
            $this->addHTML('<div class="card_avatar">');
                $this->addHTML($this->getAvatarHTML());
            $this->addHTML('</div>');
        }
    }
    
    protected function buildHeader(){
        $this->buildTopRight();
        $headerClass = $this->getHeaderClass();
        $this->addHTML('<div class="card_header ' . $headerClass .'">');
            $this->buildTitle();
            $this->buildSubtitle();
        $this->addHTML('</div>');
    }
    
    protected function buildTitle(){
        $title = $this->getTitle();
        if($title){
            $titleClass = $this->getTitleClass();
            $this->addHTML('<span class="title ' . $titleClass .'">');
                $this->addHTML($title);
            $this->addHTML('</span>');
        }
    }
    
    protected function buildSubtitle(){
        $subtitle = $this->getSubtitle();
        if($subtitle){
            $subtitleClass = $this->getSubtitleClass();
            $this->addHTML('<span class="subtitle ' . $subtitleClass .'">');
                $this->addHTML($subtitle);
            $this->addHTML('</span>');
        }
    }
    
    protected function buildTopRight(){
        $topRight = $this->getTopRight();
        if($topRight){
            $topRightClass = $this->getTopRightClass();
            $this->addHTML('<div class="card_top_right ' . $topRightClass .'">');
                $this->addHTML($topRight);
            $this->addHTML('</div>');
        }
    }
    
    protected function buildSummary(){
        $summary = $this->getSummary();
        if(!empty($summary)){
            $summaryClass = $this->getSummaryClass();
            $this->addHTML('<div class="card_summary ' . $summaryClass .'">');
                $this->addHTML($summary);
            $this->addHTML('</div>');
        }
    }
    
    protected function buildView(){
        $this->openCardWrap();
            $this->buildTab();
            $this->buildAvatar();
            $this->buildHeader();
            $this->buildSummary();
        $this->closeCardWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
        return parent::beforeReturningView();
    }
    
}
