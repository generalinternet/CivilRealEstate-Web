<?php
/**
 * Description of AbstractBirdBeakMenuView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractBirdBeakMenuView extends GI_View {
    
    protected $menuHTML = '';
    /** @var AbstractBirdBeakMenuBtnView[] */
    protected $btns = array();
    protected $class = array();

    public function getClassString() {
        return implode(' ', $this->class);
    }

    public function addClass($class) {
        $this->class[] = $class;
        return $this;
    }
    
    protected function addBeaks(){
        $this->addOpenBeak();
        $this->addCloseBeak();
    }
    
    protected function addOpenBeak(){
        $this->addHTML('<span class="bird_beak open">' . GI_StringUtils::getSVGIcon('bird_beak_down') . '</span>');
    }
    
    protected function addCloseBeak(){
        $this->addHTML('<span class="bird_beak close">' . GI_StringUtils::getSVGIcon('bird_beak_up') . '</span>');
    }
    
    protected function openMenuWrap(){
        $this->addHTML('<div class="bird_beak_menu_wrap ' . $this->getClassString() . '">');
    }
    
    protected function closeMenuWrap(){
        $this->addHTML('</div>');
    }
    
    protected function openMenu(){
        $this->addHTML('<div class="bird_beak_menu">');
    }
    
    protected function closeMenu(){
        $this->addHTML('</div>');
    }
    
    public function addMenuHTML($html){
        $this->menuHTML .= $html;
        return $this;
    }
    
    protected function buildMenu(){
        foreach($this->btns as $btn){
            $this->addMenuHTML($btn->getHTMLView());
        }
        $this->addHTML($this->menuHTML);
    }
    
    public function createBtn($label, $url = NULL, $svgIcon = NULL){
        $btn = new BirdBeakMenuBtnView($label, $url, $svgIcon);
        $this->addBtn($btn);
        return $this;
    }
    
    public function addBtn(AbstractBirdBeakMenuBtnView $btn){
        $this->btns[] = $btn;
        return $this;
    }

    protected function buildView(){
        $this->openMenuWrap();
            $this->addBeaks();
            $this->openMenu();
                $this->buildMenu();
            $this->closeMenu();
        $this->closeMenuWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
        parent::beforeReturningView();
    }
    
}
