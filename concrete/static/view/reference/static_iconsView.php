<?php

class StaticIconsView extends GI_View {
    
    public function __construct() {
        $this->addSiteTitle('Icons');
        parent::__construct();
    }
    
    public function buildView(){
        $icons = array(
            'search',
            'trash', //delete,
            'pencil', //edit
            'plus', //add
            'minus', //subtract
            'check',
            'copy',
            'sort', //reorder
            'print',
            'swap',
            'upload',
            'download',
            'eks', //close, remove
            'money', //payment
            'number', //hash, count
            'arrow_up',
            'arrow_down',
            'arrow_left',
            'arrow_right',
            'box',
            'truck',
            'worker',
            'filter',
            'time', //clock,
            'cards', //credit
            'gear', //settings
            'share',
            'paperclip', //attachment
            'link',
            'void', //cancel
            'unlocked',
            'locked',
            'star', //rate
            'flag', //claim
            'calculate', //calculator, recalculate
            'zip',
            'email',
            'visible', //eye
            'invisible', //hidden
            /*other sizes*/
            'close_sml', //remove_sml
            'contract', //minimize
            'expand', //maximize
            'menu',
            'bos',
            'gi'
        );
        
        $this->addHTML('<div class="view_wrap">');
            $this->addHTML('<div class="view_body">');
        
        $this->addHTML('<span class="custom_btn" title="CSS Class: icon avatar"><span class="icon_wrap"><span class="icon avatar"></span></span></span> ');
        $this->addHTML('<span class="custom_btn" title="CSS Class: icon avatar"><span class="icon_wrap"><span class="icon bos_avatar"></span></span></span> ');
        $this->addHTML('<span class="custom_btn" title="CSS Class: icon avatar"><span class="icon_wrap"><span class="icon avatar dark"></span></span></span> ');
        $this->addHTML('<span class="custom_btn" title="CSS Class: icon avatar"><span class="icon_wrap"><span class="icon bos_avatar dark"></span></span></span> ');
        
        $this->addHTML('<span class="custom_btn lock_btn" title=""><span class="icon_wrap"><span class="icon unlocked"></span></span></span> ');
        $this->addHTML('<span class="custom_btn unlock_btn" title=""><span class="icon_wrap"><span class="icon locked"></span></span></span> ');
        $this->addHTML('<br/>');
        
        //standard WHITE
        foreach($icons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon ' .  $icon . '"><span class="icon_wrap"><span class="icon ' . $icon . '"></span></span></span> ');
        }
        $this->addHTML('<br/>');
        //light gray
        foreach($icons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon light_gray ' .  $icon . '"><span class="icon_wrap"><span class="icon light_gray ' . $icon . '"></span></span></span> ');
        }
        $this->addHTML('<br/>');
        //gray
        foreach($icons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon gray ' .  $icon . '"><span class="icon_wrap"><span class="icon gray ' . $icon . '"></span></span></span> ');
        }
        $this->addHTML('<br/>');
        //dark gray
        foreach($icons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon dark_gray ' .  $icon . '"><span class="icon_wrap"><span class="icon dark_gray ' . $icon . '"></span></span></span> ');
        }
        $this->addHTML('<br/>');
        //black
        foreach($icons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon black ' .  $icon . '"><span class="icon_wrap"><span class="icon black ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        //primary
        foreach($icons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon primary ' .  $icon . '"><span class="icon_wrap border circle"><span class="icon primary ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        //red
        foreach($icons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon red ' .  $icon . '"><span class="icon_wrap border circle"><span class="icon red ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        //green
        foreach($icons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon green ' .  $icon . '"><span class="icon_wrap border circle"><span class="icon green ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        $this->addHTML('<hr/>');
        $this->addHTML('<h2>New Icons</h2>');
        $newIcons = array(
            'plus_circle',
            'minus_circle',
            'arrow_down_circle',
            'arrow_left_circle',
            'arrow_right_circle',
            'arrow_up_circle',
            'barcode',
//            'bug',
            'binoculars',
            'bell',
            'image',
//            'person',
            'building', //organization
//            'dashboard_simple',
            'dashboard',
            'hold',
            'closed',
            'padlock',
//            'estimates',
//            'invoices',
            'accounting',
//            'clipboard',
//            'clipboard_check',
//            'clipboard_cross',
            'calendar',
            'calendar_add',
            'calendar_remove',
            'handshake',
            'post',
            'reply',
            'reply_all',
            'pdf',
            'wrench',
//            'more',
//            'projects',
//            'hot',
//            'hot_add',
//            'hot_remove',
            'folder',
            'folder_add',
            'folder_back',
            'folder_share',
            'info',
            'question',
            'exclamation',
            'caution',
            'add_multiple',
            'split',
            'combine',
            'export',
            'import',
            'expand sml', //maximize sml
            'contract sml', //minimize sml
            'arrow_up full',
            'arrow_down full',
            'arrow_left full',
            'arrow_right full',
            'arrow_up border',
            'arrow_down border',
            'arrow_left border',
            'arrow_right border',
            'grid',
            'list',
            'grip',
            'clipboard work_order',
            'clipboard text',
            'clipboard money',
            'male', //individual
            'female', //individual female,
            'play',
            'stop',
            'pause',
            'to_start',
            'to_end',
            'rewind',
            'fast_forward',
            'phone',
            'account',
            'login',
            'logout',
            'chart'
//            'bell_outline'
        );
        
        foreach($newIcons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon ' .  $icon . '"><span class="icon_wrap"><span class="icon ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        
        foreach($newIcons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon light_gray ' .  $icon . '"><span class="icon_wrap"><span class="icon light_gray ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        
        foreach($newIcons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon gray ' .  $icon . '"><span class="icon_wrap"><span class="icon gray ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        
        foreach($newIcons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon dark_gray ' .  $icon . '"><span class="icon_wrap"><span class="icon dark_gray ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        
        foreach($newIcons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon black ' .  $icon . '"><span class="icon_wrap"><span class="icon black ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        
        foreach($newIcons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon primary ' .  $icon . '"><span class="icon_wrap border circle"><span class="icon primary ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        
        foreach($newIcons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon red ' .  $icon . '"><span class="icon_wrap border circle"><span class="icon red ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        
        foreach($newIcons as $icon){
            $this->addHTML('<span class="custom_btn" title="CSS Class: icon green ' .  $icon . '"><span class="icon_wrap border circle"><span class="icon green ' . $icon . '"></span></span></span> ');
        }
        
        $this->addHTML('<br/>');
        $this->addHTML('<hr/>');
        $this->addHTML('<div id="svg_icon_section">');
        $this->addHTML('<h2>SVG Icons</h2>');
        
        $svgIcons = array(
            'account',
            'accounting',
            'activity',
            'arrow_down',
            'arrow_down_circle',
            'arrow_left',
            'arrow_left_circle',
            'arrow_right',
            'arrow_right_circle',
            'arrow_up',
            'arrow_up_circle',
            'barcode',
            'bell',
            'bell2',
            'bill',
            'binoculars',
            'bos',
            'bos_avatar',
            'box',
            'bug',
            'building',
            'business_man',
            'calculator',
            'calendar',
            'calendar_add',
            'calendar_remove',
            'cancel',
            'cards',
            'caution',
            'chart',
            'check',
            'clipboard',
            'clipboard_check',
            'clipboard_cross',
            'clipboard_money', //sales_order
            'clipboard_stock',
            'clipboard_text', //purchase_order
            'clipboard_work_order',
            'clock',
            'closed',
            'combine',
            'contact_details',
            'contacts',
            'contract',
            'copy',
            'copy_fat',
            'cross',
            'dashboard',
            'dashboard2',
            'discount',
            'dollars',
            'download',
            'estimates',
            'event',
            'exclamation_circle',
            'expand',
            'facebook',
            'facebook_round_border',
            'file',
            'file_contact',
            'filter',
            'flag',
            'folder',
            'folder_add',
            'folder_back',
            'folder_share',
            'gear',
            'gi',
            'grid',
            'handshake',
            'hold',
            'hot',
            'hot_add',
            'hot_remove',
            'image',
            'info',
            'info_circle',
            'input',
            'insta',
            'insta_round_border',
            'invisible',
            'invoice',
            'link',
            'linkedin',
            'linkedin_round_border',
            'locked_fat',
            'login',
            'logout',
            'mail',
            'menu',
            'minus',
            'minus_circle',
            'more',
            'multiple',
            'number',
            'office',
            'packaging',
            'padlock',
            'paperclip',
            'pdf',
            'pencil',
            'person',
            'phone',
            'plus',
            'plus_circle',
            'post',
            'print',
            'project',
            'question',
            'quote',
            'receiving',
            'reply',
            'reply_all',
            'search',
            'share',
            'shipping',
            'sortable',
            'split',
            'star',
            'swap',
            'timesheet',
            'tool',
            'trash',
            'truck',
            'twitter',
            'twitter_round_border',
            'unlocked',
            'upload',
            'visible',
            'void',            
            'warehouse',
            'worker',
            'wrench',
            'zip',
        );
        
        $this->addHTML('<div class="content_padding">');
        foreach($svgIcons as $svgIcon){
            $this->addHTML('<span style="padding:1em;background-color:#7899b0;display:inline-block;" title="'.$svgIcon.'">'.GI_StringUtils::getSVGIcon($svgIcon).'</span>');
        }
        $this->addHTML('</div>');
        
        $this->addHTML('</div>');
        
        $newIconsURL = GI_URLUtils::buildURL(array(
            'controller' => 'static',
            'action' => 'fontIcons'
        ));
        $this->addHTML('<hr/>');
        $this->addHTML('<b><a href="' .  $newIconsURL . '">View Font Icons</a></b>');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
