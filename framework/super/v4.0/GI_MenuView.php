<?php
/**
 * Description of GI_MenuView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class GI_MenuView extends GI_View {

    protected $menu = array();
    protected $strictLinkCheck = false;
    protected $menuBuilt = false;
    protected $curMenuRef = NULL;

    public function __construct() {
        parent::__construct();
        $this->addMenu('main');
    }
    
    /** 
     * 
     * @param boolean $strictLinkCheck check/compare links based on full link rather than just controller and action
     */
    public function setStrictLinkCheck($strictLinkCheck){
        $this->strictLinkCheck = $strictLinkCheck;
    }
    
    public function setCurMenuRef($curMenuRef){
        $this->curMenuRef = $curMenuRef;
    }

    protected function filterMenuItem($array) {
        $tmpArray = array();
        $notAllowed = array(
            'ref',
            'link',
            'label'
        );
        foreach ($array as $key => $item) {
            if (!in_array($key, $notAllowed)) {
                $tmpArray[$key] = $item;
            }
        }
        return $tmpArray;
    }

    protected function checkLink($link) {
        $current = GI_URLUtils::isLinkCurrent($link, $this->strictLinkCheck);
        
        if($current){
            return 'current';
        } else {
            return '';
        }
    }
    
    protected function checkSubMenuLinks($subMenuItems){
        $subMenuClass = '';
        
        foreach($subMenuItems as $subMenuItem){
            if(isset($subMenuItem['ref'])){
                $subMenuClass .= ' ' . $this->checkSubMenuLinks($this->menu[$subMenuItem['ref']]);
                if($subMenuItem['ref'] == $this->curMenuRef){
                    $subMenuClass .= ' current';
                }
            } elseif(isset($subMenuItem['itemRef']) && $subMenuItem['itemRef'] == $this->curMenuRef){
                $subMenuClass .= ' current';
            }
            if(isset($subMenuItem['link'])){
                $subMenuClass .= ' ' . $this->checkLink($subMenuItem['link']);
            }
        }
        return $subMenuClass;
    }

    public function addMenu($ref) {
        if (!isset($this->menu[$ref])) {
            $this->menu[$ref] = array();
        }
    }

    public function addMenuItem($ref, $label = '', $link = '', $other = array()) {
        if (!isset($this->menu[$ref])) {
            $this->addMenu($ref);
        }
        $item = array(
            'label' => $label
        );
        if (!empty($link)) {
            $item['link'] = $link;
        }
        $item = array_merge($item, $this->filterMenuItem($other));
        array_push($this->menu[$ref], $item);
    }

    public function addSubMenu($ref, $sub, $label, $link = '', $other = array()) {
        if (!isset($this->menu[$ref])) {
            $this->addMenu($ref);
        }
        $this->addMenu($sub);
        $item = array(
            'ref' => $sub,
            'label' => $label
        );
        if (!empty($link)) {
            $item['link'] = $link;
        }
        $item = array_merge($item, $this->filterMenuItem($other));
        $this->menu[$ref][] = $item;
    }

    protected function getMenuItemWrap($item) {
        if (!empty($item)) {
            $label = $item['label'];
            if(isset($item['link'])){
                $link = $item['link'];
            } else {
                $link = '';
            }
            $linkClass = '';
            if (isset($item['linkClass']) && !empty($item['linkClass'])){
                $linkClass = $item['linkClass'];
            }
            $anchorClass = '';
            if (isset($item['anchorClass']) && !empty($item['anchorClass'])){
                $anchorClass = $item['anchorClass'];
            }
            if (!empty($item['ref'])) {
                $checkSubMenuClass = $this->checkSubMenuLinks($this->menu[$item['ref']]);
                $linkClass .= ' sub_menu ' . $checkSubMenuClass;
                $anchorClass .= ' sub_menu ' . $checkSubMenuClass;
                if($item['ref'] == $this->curMenuRef){
                    $linkClass .= ' current';
                    $anchorClass .= ' current';
                }
            } elseif(isset($item['itemRef']) && $item['itemRef'] == $this->curMenuRef){
                $linkClass .= ' current';
                $anchorClass .= ' current';
            }
            $start = '';
            $end = '';
            if (!empty($link)) {
                $linkClass .= ' ' . $this->checkLink($link);
                $start = '<a href="' . $link . '" class="' . $anchorClass . '" title="' . GI_Sanitize::htmlAttribute($label) . '">';
                $end = '</a>';
            } elseif (!empty($label) || $label === '0') {
                $start = '<span class="title ' . $anchorClass . '" title="' . GI_Sanitize::htmlAttribute($label) . '">';
                $end = '</span>';
            }
            $item_array = array(
                'start' => $start,
                'end' => $end,
                'linkClass' => $linkClass
            );
            return $item_array;
        }
    }

    protected function getMenuItemLabel($item) {
        if (!empty($item)) {
            $label = $item['label'];
            if (!empty($item['content'])) {
                return $item['content'];
            } else {
                return $label;
            }
        }
    }

    protected function getMenuItem($item) {
        $wrap = $this->getMenuItemWrap($item);
        $label = $this->getMenuItemLabel($item);
        if ($wrap && $label) {
            $dataAttr = NULL;
            $itemLabel = $wrap['start'] . $label . $wrap['end'];
            if (!empty($item['ref'])) {
                $subMenu = $this->getMenu($item['ref']);
                $dataAttr = 'data-menu-ref="' . $item['ref'] . '"';
            }
            $html = '<li class="' . $wrap['linkClass'] . '" ' . $dataAttr . '>';
            $html .= $itemLabel;
            if (isset($subMenu)){
                $html .= $subMenu;
            }
            $html .= '</li>';
            return $html;
        }
    }

    protected function getMenu($ref) {
        if (!empty($this->menu[$ref])) {
            $html = '<ul>';
            foreach ($this->menu[$ref] as $item) {
                $html .= $this->getMenuItem($item);
            }
            $html .= '</ul>';
            return $html;
        }
    }
    
    public function buildView(){
        if (!$this->menuBuilt) {
            $this->addHTML($this->getMenu('main'));
            $this->menuBuilt = true;
        }
    }
    
    public function resetHTML(){
        $this->menuBuilt = false;
        return parent::resetHTML();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
