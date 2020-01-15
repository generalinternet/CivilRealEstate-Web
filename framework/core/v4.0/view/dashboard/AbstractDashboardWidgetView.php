<?php

/**
 * Description of AbstractDashboardWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardWidgetView extends GI_WidgetView {
    
    protected $title = '';
    protected $headerIcon = '';
    protected $linkURL = '';
    protected $btnOptions = array();
    
    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function setHeaderIcon($headerIcon) {
        $this->headerIcon = $headerIcon;
    }
    
    public function setLinkURL($linkURL) {
        $this->linkURL = $linkURL;
    }
    
    public function setBtnOptions($btnOptions) {
        $this->btnOptions = $btnOptions;
    }

    protected function openViewWrap() {
        parent::openViewWrap();
        $this->addHTML('<div class="grid_box">');
    }

    protected function closeViewWrap() {
        $this->addHTML('</div><!--.grid_box-->');
        parent::closeViewWrap();
    }

    protected function buildViewHeader() {
        $this->addHTML('<div class="box_header">');
        $this->addHTML('<h2 class="title">');
        $this->addHTML($this->getTextWithSVGIcon($this->headerIcon, $this->title, '23px', '23px'));
        if (!empty($this->btnOptions)) {
            $this->addHTML('<div class="right_btns">');
            $this->addHTML($this->buildHeaderBtn($this->btnOptions));
            $this->addHTML('</div>');
        }
        $this->addHTML('</h2>');
        $this->addHTML('</div>');
    }

    protected function buildHeaderBtn($btnOptions) {
        $btnTitle = '';
        if (isset($btnOptions['title'])) {
            $btnTitle = $btnOptions['title'];
        }
        $btnHoverTitle = $btnTitle;
        if (isset($btnOptions['hoverTitle'])) {
            $btnHoverTitle = $btnOptions['hoverTitle'];
        }
        $btnIcon = 'info';
        if (isset($btnOptions['icon'])) {
            $btnIcon = $btnOptions['icon'];
        }
        $linkURL = '';
        if (isset($btnOptions['link'])) {
            $linkURL = $btnOptions['link'];
        }
        $classNames = '';
        if (isset($btnOptions['class_names'])) {
            $classNames = $btnOptions['class_names'];
        }
        
        $otherData = '';
        if (isset($btnOptions['other_data'])) {
            $otherData = ' '.$btnOptions['other_data'];
        }
        
        $target = '';
        if (isset($btnOptions['target'])) {
            $target = 'target="' . $btnOptions['target'] . '"';
        }

        $btnText = $this->getTextWithSVGIcon($btnIcon, '', '18px', '18px');
        return '<a href="' . $linkURL . '" title="' . $btnTitle . '" class="grid_header_link ' . $classNames . '" ' . $otherData . ' ' . $target . '>' . $btnText . '</a>';
    }

    protected function buildViewBody() {
        if ($this->useBodyPlaceholder) {
            $this->buildBodyPlaceholder();
        } else {
            $this->buildBodyContent();
        }
    }

    public function buildBodyPlaceholder() {
        $this->addHTML('<div class="box_content">');
        if (!empty($this->contentSourceURL)) {
            $url = $this->contentSourceURL;
        } else {
            $url = GI_URLUtils::buildURL(array(
                'controller'=>'dashboard',
                'action'=>'getWidgetContent',
                'ref'=>$this->ref,
            ));
        }
        $this->addHTML('<div class="ajaxed_contents auto_load" data-url="'.$url.'"></div>'); 
        $this->addHTML('</div>');
    }

    public function buildBodyContent() {

    }

}
