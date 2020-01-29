<?php

/**
 * Description of AbstractContactOrgProfileUICardView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContactOrgProfileUICardView extends AbstractContactProfileUICardView {
    
    protected $individualName = '';
    protected $emailAddress = NULL;
    protected $phoneNumber = NULL;
    protected $address = NULL;

    public function __construct(\GI_Model $model = NULL) {
        parent::__construct($model);
        $this->setURL($model->getViewProfileURL());
        $this->setAvatarHTML($model->getAvatarHTML());
        $this->emailAddress = $model->getEmailAddress();
        $this->phoneNumber = $model->getPhoneNumber();
        $this->address = $model->getAddress();
    }
    
    public function setIndividualName($name) {
        $this->individualName = $name;
    }

    protected function buildView() {
        $this->openCardWrap();
        $this->buildTab();
        $this->buildAvatar();
        $this->buildHeader();
        $this->buildSummary();
        $this->closeCardWrap();
    }

    protected function buildHeader() {
        $this->buildTopRight();
        $headerClass = $this->getHeaderClass();
        $this->addHTML('<div class="card_header ' . $headerClass . '">');
        $this->buildTitle();
        $this->addHTML('</div>');
    }

    protected function buildTitle() {
        $title = $this->model->getDisplayName();
        if ($title) {
            $titleClass = $this->getTitleClass();
            $this->addHTML('<span class="title ' . $titleClass . '">');
            $this->addHTML($title);
            $this->addHTML('</span>');
        }
    }
    
    protected function buildSummary() {
        $summaryClass = $this->getSummaryClass();
        $this->addHTML('<div class="card_summary ' . $summaryClass . '">');

        if (!empty($this->emailAddress) || !empty($this->phoneNumber) || !empty($this->address)) {
            $this->buildContactInfoSection();
        }
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->buildCompanyName();
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->buildIndividualName();
        $this->addHTML('</div>')
                ->addHTML('</div>');

        $this->addHTML('</div>');
    }

    protected function buildContactInfoSection() {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addHTML($this->model->getAddress());
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML($this->model->getPhoneNumber() . '<br/>');
        $this->addHTML($this->model->getEmailAddress());
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function buildCompanyName() {
        $name = $this->model->getName();
        if (!empty($name)) {
            $this->addHTML($name);
        }
    }

    protected function buildIndividualName() {
        if (empty($this->individualName)) {
            $primaryIndividual = $this->model->getPrimaryIndividual();
            if (!empty($primaryIndividual)) {
                $this->individualName = $primaryIndividual->getName();
            }
        }
        if (!empty($this->individualName)) {
            $this->addHTML($this->individualName);
        }
    }

}
