<?php
/**
 * Description of AbstractRecentActivitySearchFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
class AbstractRecentActivitySearchFormView extends GI_SearchView {
    
    
    public function __construct(\GI_Form $form, $queryValues = array()) {
        $this->setBoxId('recent_activity_search_box');
        parent::__construct($form, $queryValues);
    }
    
    protected function buildForm() {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addUserField();
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addUserField(){
        $autoCompURLProps = array(
            'controller' => 'user',
            'action' => 'autocompUser',
            'ajax' => 1
        );
        $userAutoCompURL = GI_URLUtils::buildURL($autoCompURLProps);
        $this->form->addField('search_user_id', 'autocomplete', array(
            'displayName'=>'Search by User',
            'placeHolder' => 'start typing...',
            'autocompURL' => $userAutoCompURL,
            'value' => $this->getQueryValue('user_id'),
        ));
    }

}
