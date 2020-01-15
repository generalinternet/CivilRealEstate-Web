<?php

class StaticAutoColumnsView extends GI_View {
    
    public function __construct() {
        $this->addSiteTitle('Columns');
        parent::__construct();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="view_wrap outlined_columns">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();
            $this->addHTML('<div class="view_header">');
            $this->addMainTitle('Auto-Columns');
            $this->addHTML('</div>');
            $this->addHTML('<div class="view_body">');
                $this->addHTML('<h2>Halves</h2>');
                $this->addHTML('<h3>auto_columns halves</h3>')
                        ->addHTML('<div class="auto_columns halves">')
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addHTML('</div>');

                $this->addHTML('<hr/>');
                $this->addHTML('<h2>Thirds</h2>');
                $this->addHTML('<h3>auto_columns thirds</h3>')
                        ->addHTML('<div class="auto_columns thirds">')
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addHTML('</div>');

                $this->addHTML('<hr/>');
                $this->addHTML('<h2>Quarters</h2>');
                $this->addHTML('<h3>auto_columns quarters</h3>')
                        ->addHTML('<div class="auto_columns quarters">')
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addColumnElement()
                        ->addHTML('</div>');

                $this->addHTML('<hr/>');
            $this->addHTML('</div>');
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function addColumnElement(){
        $this->addHTML('<div><p class="content_block">any child element</p></div>');
        return $this;
    }
    
}
