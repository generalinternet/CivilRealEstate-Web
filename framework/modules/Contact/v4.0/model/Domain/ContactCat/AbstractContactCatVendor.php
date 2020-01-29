<?php

/**
 * Description of AbstractContactCatVendor
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactCatVendor extends AbstractContactCat {
    
    protected static $newUserDefaultRoleSystemTitle = 'vendor';

    /**
     * @param GI_Form $form
     * @return \ContactCatFormView
     */
    public function getFormView($form, $otherData = array()) {
        $formView = new ContactCatVendorFormView($form, $this, $otherData);
        return $formView;
    }

    /**
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = true) {
        $title = 'Vendor';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    /**
     * 
     * @return \ContactCatVendorDetailView
     */
    public function getDetailView() {
        $detailView = new ContactCatVendorDetailView($this);
        return $detailView;
    }

    public function getQuickbooksId() {
        return $this->getProperty('contact_cat_vendor.quickbooks_id');
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                
                'header_title'=>'<span class="qb_sml_logo dark"></span>',
                'css_header_class' => 'qb_logo_col',
                'method_name' => 'getQuickbooksExportedStatusHTML',
            ),
        );
        $UITableCols = parent::getUITableCols();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public static function getQnASearchForm(GI_DataSearch $dataSearch, $type = NULL, &$redirectArray = array()){
        return parent::getQnASearchForm($dataSearch, $type, $redirectArray);
    }
    
    /** Profile **/

    public function getProfileDetailView() {
        $contact = $this->getContact();
        return new ContactOrgVendorProfileDetailView($contact);
    }

    protected function getProfileFormViewObject(GI_Form $form, $curStep = 1) {
        $contact = $this->getContact();
        if (empty($contact)) {
            return NULL;
        }
        $view = new ContactOrgVendorProfileFormView($form, $contact);
        $view->setCurStep($curStep);
        return $view;
    }

    public function isVendor() {
        return true;
    }

}
