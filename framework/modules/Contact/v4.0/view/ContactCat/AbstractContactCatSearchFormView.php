<?php
/**
 * Description of AbstractContactCatSearchFormView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactCatSearchFormView extends AbstractContactSearchFormView {
    
    public function __construct(\GI_Form $form, $queryValues = array()) {
        $this->setBoxId('contact_cat_search_box');
        parent::__construct($form, $queryValues);
    }
    
}
