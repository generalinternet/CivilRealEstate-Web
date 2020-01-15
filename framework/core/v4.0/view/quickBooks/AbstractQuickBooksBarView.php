<?php
/**
 * Description of AbstractQuickBooksBarView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    1.1.1
 */
abstract class AbstractQuickBooksBarView extends GI_View {
    
    /** @var GI_Model */
    protected $exportableModel = NULL;
    protected $exportableBarHTML = '';
    protected $exportURL = NULL;
    protected $exportBtnTitle = NULL;
    protected $exportBtnHTML = NULL;
    
    protected $importURL = NULL;
    protected $importBtnTitle = NULL;
    protected $importBtnHTML = NULL;
    
    protected $linkURL = NULL;
    protected $linkBtnTitle = NULL;
    protected $linkBtnHTML = NULL;
    
    protected $unlinkURL = NULL;
    protected $unlinkBtnTitle = NULL;
    protected $unlinkBtnHTML = NULL;
    
    protected $footerHTML = NULL;
    
    /**
     * @param GI_Model $exportableModel
     * @return \AbstractQuickBooksBarView
     */
    public function setExportableModel(GI_Model $exportableModel){
        $this->exportableModel = $exportableModel;
        return $this;
    }
    
    /**
     * @param string $exportURL
     * @return \AbstractQuickBooksBarView
     */
    public function setExportURL($exportURL, $exportBtnTitle = NULL){
        $this->exportURL = $exportURL;
        $this->exportBtnTitle = $exportBtnTitle;
        return $this;
    }
    
    public function setExportBtnHTML($exportButtonHTML) {
        $this->exportBtnHTML = $exportButtonHTML;
    }
    
    public function setImportURL($importURL, $importBtnTitle = NULL) {
        $this->importURL = $importURL;
        $this->importBtnTitle = $importBtnTitle;
    }
    
    public function setImportBtnHTML($importButtonHTML) {
        $this->importBtnHTML = $importButtonHTML;
    }
    
    public function setLinkURL($linkURL, $linkBtnTitle = NULL) {
        $this->linkURL = $linkURL;
        $this->linkBtnTitle = $linkBtnTitle;
    }
    
    public function setLinkBtnHTML($linkBtnHTML) {
        $this->linkBtnHTML = $linkBtnHTML;
    }
    
    public function setUnLinkURL($unLinkURL, $unLinkBtnTitle = NULL) {
        $this->unlinkURL = $unLinkURL;
        $this->unlinkBtnTitle = $unLinkBtnTitle;
    }
    
    public function setUnLinkBtnHTML($unLinkBtnHTML) {
        $this->unlinkBtnHTML = $unLinkBtnHTML;
    }

    public function setFooterHTML($footerHTML) {
        $this->footerHTML = $footerHTML;
    }
    
    protected function verifyExportableModelFranchiseId() {
        if (!ProjectConfig::getIsFranchisedSystem()) {
            return true;
        }
        $exportableModel = $this->exportableModel;
        if (empty($exportableModel)) {
            return true;
        }

        $franchise = Login::getCurrentFranchise();
        $franchiseId = NULL;
        if (!empty($franchise)) {
            $franchiseId = $franchise->getId();
        }

        if ($exportableModel->getProperty('franchise_id') == $franchiseId) {
            return true;
        }

        return false;
    }

    protected function buildView() {
        if (!empty(ProjectConfig::getQBClientId()) && $this->verifyExportableModelFranchiseId()) {
            $this->openViewWrap();
            $this->addQBLogo();
            $this->addExportableModelInfo();
            $this->addQBConnectBtns();
           // $this->addQBSettingsButton();
            $this->closeViewWrap();
            if (!empty($this->footerHTML)) {
                $this->addHTML($this->footerHTML);
            }
        }
    }

    protected function addQBLogo(){
        $this->addHTML('<a href="'.ProjectConfig::getQBAccountURL().'" class="qb_logo" target="_blank"></a>');
    }
    
    protected function addQBConnectBtns(){
        if(Permission::verifyByRef('connect_to_quickbooks')){
            $this->addHTML('<span class="connect_btns">');
                $this->addHTML(QBConnection::getConnectToQuickbooksButton());
                $this->addHTML('<span class="qb_connection_status"></span>');
            $this->addHTML('</span>');
        }
    }

    protected function addExportableModelInfo() {
        if ($this->exportableModel) {
            $this->addHTML('<span class="qb_model_title">' . $this->exportableModel->getQBExportTitle() . '</span>');
            if (!empty($this->exportableModel->getQuickbooksId())) {
                $class = 'qb_check';
                $title = '';
                if ($this->exportableModel->getRequiresQuickbooksReExport()) {
                    $class .= ' red';
                    $title = 'Requires Re-Export';
                }
                $this->addHTML('<span class="'.$class.'" title="'.$title.'"></span>');
                $date = $this->exportableModel->getQuickbooksExportDate();
                if($date){
                    $this->addHTML('<span class="qb_export_date">Exported ' . GI_Time::formatDateForDisplay($date) . '</span>');
                }
            }
            $this->addLinkBtn();
            $this->addUnlinkBtn();
            $this->addImportBtn();
            $this->addExportBtn();
        }
        if(!empty($this->exportableBarHTML)){
            $this->addHTML($this->exportableBarHTML);
        }
    }
    
    protected function addExportBtn(){
        if (!empty($this->exportBtnHTML)) {
            $this->addHTML($this->exportBtnHTML);
        } else if (!empty($this->exportURL)){
            $exportBtnTitle = $this->exportBtnTitle;
            if(is_null($exportBtnTitle)){
                $exportBtnTitle = 'Export';
                $connected = $this->exportableModel->getQuickbooksId();
                if($connected){
                    $exportBtnTitle = 'Re-Export';
                }
            }
            $this->addHTML('<a href="' . $this->exportURL . '" class="qb_btn open_modal_form" title="Export to Quickbooks">' . $exportBtnTitle . '</a>');
        }
    }
    
    protected function addImportBtn() {
        if (!empty($this->importBtnHTML)) {
            $this->addHTML($this->importBtnHTML);
        } else if (!empty($this->importURL)) {
            $importBtnTitle = $this->importBtnTitle;
            if (is_null($importBtnTitle)) {
                $importBtnTitle = 'Import';
            }
            $this->addHTML('<a href="' . $this->importURL . '" class="qb_btn open_modal_form" title="Import from Quickbooks">' . $importBtnTitle . '</a>');
        }
    }

    protected function addLinkBtn() {
        if (!empty($this->linkBtnHTML)) {
            $this->addHTML($this->linkBtnHTML);
        } else if (!empty($this->linkURL)) {
            $linkBtnTitle = $this->linkBtnTitle;
            if (is_null($linkBtnTitle)) {
                $linkBtnTitle = 'Link';
            }
            $this->addHTML('<a href="' . $this->linkURL . '" class="qb_btn open_modal_form" title="Link to Quickbooks Element">' . $linkBtnTitle . '</a>');
        }
    }

    protected function addUnlinkBtn() {
        if (!empty($this->unlinkBtnHTML)) {
            $this->addHTML($this->unlinkBtnHTML);
        } else if (!empty($this->unlinkURL)) {
            $unlinkBtnTitle = $this->unlinkBtnTitle;
            if (is_null($unlinkBtnTitle)) {
                $unlinkBtnTitle = 'Unlink';
            }
            $this->addHTML('<a href="' . $this->unlinkURL . '" class="qb_btn open_modal_form" title="Unlink from Quickbooks Element">' . $unlinkBtnTitle . '</a>');
        }
    }

    public function addExportableBarHTML($html){
        $this->exportableBarHTML .= $html;
        return $this;
    }
    
    protected function getQBBarClass(){
        if (QBConnection::isConnectionValid()) {
            return 'connected';
        }
        return;
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="qb_bar_wrap">');
        $this->addHTML('<div class="qb_bar ' . $this->getQBBarClass() . '">');
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
