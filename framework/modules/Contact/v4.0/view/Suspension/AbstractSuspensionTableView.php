<?php

abstract class AbstractSuspensionTableView extends MainWindowView {
    
    protected $suspensions;
    
    public function __construct($suspensions = NULL) {
        parent::__construct();
        $this->suspensions = $suspensions;
    }
    
    protected function addViewBodyContent() {
       if (empty($this->suspensions)) {
           $this->addHTML('<p>No suspensions found</p>');
           return;
       }
       $this->buildSuspensionsTable();
    }
    protected function buildSuspensionsTable() {
        $this->addHTML('<div class="flex_table ui_table">');
        $this->buildTableHeader();
        $this->buildTableBody();
        $this->buildTableFooter();
        $this->addHTML('</div>');
    }
    
    protected function buildTableHeader() {
        $this->addHTML('<div class="flex_row flex_head">')
                ->addHTML('<div class="flex_col">Type</div>')
                ->addHTML('<div class="flex_col">Start</div>')
                ->addHTML('<div class="flex_col">End</div>')
                ->addHTML('<div class="flex_col">Added By?</div>')
                ->addHTML('<div class="flex_col lrg">Reason</div>')
                ->addHTML('<div class="flex_col">Status</div>')
                ->addHTML('<div class="flex_col sml"></div>') //Functions
                        ->addHTML('</div>');
    }

    protected function buildTableBody() {
        foreach ($this->suspensions as $suspension) {
            $this->buildTableRow($suspension);
        }
    }

    protected function buildTableRow(AbstractSuspension $suspension) {
        $this->addHTML('<div class="flex_row">');

        $this->addHTML('<div class="flex_col">');
        $this->addHTML($suspension->getTypeTitle());
        $this->addHTML('</div>');

        $this->addHTML('<div class="flex_col">');
        $this->addHTML(GI_Time::formatDateTimeForDisplay($suspension->getProperty('start_date_time')));
        $this->addHTML('</div>');

        $this->addHTML('<div class="flex_col">');
        $endDateTime = $suspension->getProperty('end_date_time');
        if (!empty($endDateTime)) {
            $this->addHTML(GI_Time::formatDateTimeForDisplay($endDateTime));
        } else {
            $this->addHTML('--');
        }
        $this->addHTML('</div>');
        
        $this->addHTML('<div class="flex_col">');
        $this->addHTML($suspension->getAddedByName());
        $this->addHTML('</div>');

        $this->addHTML('<div class="flex_col lrg">');
        $this->addHTML('<p>' . $suspension->getProperty('notes') . '</p>');
        $this->addHTML('</div>');

        $this->addHTML('<div class="flex_col">');
        if ($suspension->isActive()) {
            $this->addHTML('Current');
        } else {
            $this->addHTML('Expired');
        }
        $this->addHTML('</div>');

        $this->addHTML('<div class="flex_col sml">');
        //Functions
        $this->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        if ($suspension->isEditable() && $suspension->isActive()) {
            $this->addEditButton($suspension);
        }
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        if ($suspension->isDeleteable() && $suspension->isActive()) {
            $this->addRemoveButton($suspension);
        }
        $this->addHTML('</div>')
                ->addHTML('</div>');
        $this->addHTML('</div>'); 

        $this->addHTML('</div>'); //Row
    }

    protected function addEditButton(AbstractSuspension $suspension) {
        $editURL = $suspension->getEditURL();
        $this->addHTML('<a href="' . $editURL . '" title="Edit" class="custom_btn open_modal_form">' . GI_StringUtils::getIcon('pencil', false) . '</a>');
    }
    
    protected function addRemoveButton(AbstractSuspension $suspension) {
        $removeURL = $suspension->getRemoveURL();
        $this->addHTML('<a href="' . $removeURL . '" title="Remove" class="custom_btn open_modal_form">' . GI_StringUtils::getIcon('unlocked', false) . '</a>');
    }

    protected function buildTableFooter() {
        
    }

}