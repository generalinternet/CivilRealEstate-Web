<?php
/**
 * Description of AbstractBirdBeakMenuBtnView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractBirdBeakMenuBtnView extends GI_View {
    
    protected $label = NULL;
    protected $hoverLabel = NULL;
    protected $url = NULL;
    protected $svgIcon = NULL;
    protected $icon = NULL;
    protected $class = array(
        'beak_menu_btn',
        'custom_btn'
    );
    protected $btnTag = 'a';
    protected $dataAttrs = array();
    
    public function __construct($label, $url = NULL, $svgIcon = NULL){
        $this->setLabel($label);
        $this->setURL($url);
        $this->setSVGIcon($svgIcon);
        parent::__construct();
    }
    
    public function getLabel() {
        return $this->label;
    }

    public function getHoverLabel() {
        if(empty($this->hoverLabel)){
            return GI_Sanitize::htmlAttribute($this->getLabel());
        }
        return $this->hoverLabel;
    }

    public function getURL() {
        return $this->url;
    }

    public function getSVGIcon() {
        return $this->svgIcon;
    }

    public function getIcon() {
        return $this->icon;
    }

    public function getBtnTag() {
        $url = $this->getURL();
        if(empty($url) && $this->btnTag == 'a'){
            $this->setBtnTag('span');
        }
        return $this->btnTag;
    }

    public function getClassString() {
        return implode(' ', $this->class);
    }

    public function setLabel($label) {
        $this->label = $label;
        return $this;
    }

    public function setHoverLabel($hoverLabel) {
        $this->hoverLabel = $hoverLabel;
        return $this;
    }

    public function setURL($url) {
        $this->url = $url;
        return $this;
    }

    public function setSVGIcon($svgIcon) {
        $this->svgIcon = $svgIcon;
        return $this;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
        return $this;
    }

    public function setBtnTag($btnTag) {
        $this->btnTag = $btnTag;
        return $this;
    }

    public function addClass($class) {
        $this->class[] = $class;
        return $this;
    }
    
    public function setDataAttr($attr, $val){
        $this->dataAttrs[$attr] = $val;
        return $this;
    }
    
    public function getIconHTML(){
        $svgIcon = $this->getSVGIcon();
        if(!empty($svgIcon)){
            return GI_StringUtils::getSVGIcon($svgIcon);
        }
        $icon = $this->getIcon();
        if(!empty($icon)){
            return GI_StringUtils::getIcon($icon);
        }
    }
    
    public function addBtn($label, $url = NULL, $svgIcon = NULL, $otherData = array()){
        $btnData = array(
            'label' => $label
        );
        if(!empty($url)){
            $btnData['url'] = $url;
        }
        if(!empty($svgIcon)){
            $btnData['svgIcon'] = $svgIcon;
        }
        foreach($otherData as $key => $val){
            $btnData[$key] = $val;
        }
        return $this;
    }
    
    public function getDataAttrString(){
        $string = '';
        foreach($this->dataAttrs as $attr => $val){
            $string .= 'data-' . $attr . '="' . $val . '" ';
        }
        return $string;
    }
    
    protected function openBtn(){
        $this->addHTML('<');
        $btnTag = $this->getBtnTag();
        $this->addHTML($btnTag . ' ');
        $url = $this->getURL();
        if(!empty($url)){
            if($btnTag == 'a'){
                $this->addHTML('href="');
            } else {
                $this->addHTML('data-url="');
            }
            $this->addHTML($url . '" ');
        }
        $hoverLabel = $this->getHoverLabel();
        $this->addHTML('title="' . $hoverLabel .'" ');
        $this->addHTML('class="' . $this->getClassString() . '" ');
        $this->addHTML($this->getDataAttrString());
        $this->addHTML('>');
        return $this;
    }
    
    protected function closeBtn(){
        $this->addHTML('</');
        $btnTag = $this->getBtnTag();
        $this->addHTML($btnTag . ' ');
        $this->addHTML('>');
        return $this;
    }

    protected function buildView(){
        $this->openBtn();
        $this->addHTML($this->getIconHTML());
        $this->addHTML('<span class="btn_text">' . $this->getLabel() . '</span>');
        $this->closeBtn();
    }
    
    public function beforeReturningView() {
        $this->buildView();
        parent::beforeReturningView();
    }
    
}
