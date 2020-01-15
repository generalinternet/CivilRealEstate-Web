<?php

class StaticColumnsView extends GI_View {
    
    public function __construct() {
        $this->addSiteTitle('Columns');
        parent::__construct();
    }
    
    public function buildView(){
        $this->addHTML('<div class="view_wrap outlined_columns">');
            $this->addHTML('<div class="view_header">');
                $this->addMainTitle('Columns');
            $this->addHTML('</div>');
            $this->addHTML('<div class="view_body">');
                $this->addHTML('<h2>Halves</h2>');
                $this->addHTML('<h3>columns halves</h3>')
                        ->addHTML('<div class="columns halves">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<hr/>');
                $this->addHTML('<h2>Thirds</h2>');

                $this->addHTML('<h3>columns thirds</h3>')
                        ->addHTML('<div class="columns thirds">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns thirds</h3>')
                        ->addHTML('<div class="columns thirds">')
                        ->addHTML('<div class="column two_thirds"><p class="content_block">column two_thirds</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns thirds</h3>')
                        ->addHTML('<div class="columns thirds">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_thirds"><p class="content_block">column two_thirds</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<hr/>');
                $this->addHTML('<h2>Quarters</h2>');

                $this->addHTML('<h3>columns quarters</h3>')
                        ->addHTML('<div class="columns quarters">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns quarters</h3>')
                        ->addHTML('<div class="columns quarters">')
                        ->addHTML('<div class="column two_quarters"><p class="content_block">column two_quarters</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns quarters</h3>')
                        ->addHTML('<div class="columns quarters">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_quarters"><p class="content_block">column two_quarters</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns quarters</h3>')
                        ->addHTML('<div class="columns quarters">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_quarters"><p class="content_block">column two_quarters</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns quarters</h3>')
                        ->addHTML('<div class="columns quarters">')
                        ->addHTML('<div class="column two_quarters"><p class="content_block">column two_quarters</p></div>')
                        ->addHTML('<div class="column two_quarters"><p class="content_block">column two_quarters</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns quarters</h3>')
                        ->addHTML('<div class="columns quarters">')
                        ->addHTML('<div class="column three_quarters"><p class="content_block">column three_quarters</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns quarters</h3>')
                        ->addHTML('<div class="columns quarters">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column three_quarters"><p class="content_block">column three_quarters</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<hr/>');
                $this->addHTML('<h2>Fifths</h2>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column three_fifths"><p class="content_block">column three_fifths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column three_fifths"><p class="content_block">column three_fifths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column three_fifths"><p class="content_block">column three_fifths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column three_fifths"><p class="content_block">column three_fifths</p></div>')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column two_fifths"><p class="content_block">column two_fifths</p></div>')
                        ->addHTML('<div class="column three_fifths"><p class="content_block">column three_fifths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column four_fifths"><p class="content_block">column four_fifths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns fifths</h3>')
                        ->addHTML('<div class="columns fifths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column four_fifths"><p class="content_block">column four_fifths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<hr/>');
                $this->addHTML('<h2>Sixths</h2>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column three_sixths"><p class="content_block">column three_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column four_sixths"><p class="content_block">column four_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column four_sixths"><p class="content_block">column four_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column four_sixths"><p class="content_block">column four_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column four_sixths"><p class="content_block">column four_sixths</p></div>')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column two_sixths"><p class="content_block">column two_sixths</p></div>')
                        ->addHTML('<div class="column four_sixths"><p class="content_block">column four_sixths</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column five_sixths"><p class="content_block">column five_sixths</p></div>')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('</div>');

                $this->addHTML('<h3>columns sixths</h3>')
                        ->addHTML('<div class="columns sixths">')
                        ->addHTML('<div class="column"><p class="content_block">column</p></div>')
                        ->addHTML('<div class="column five_sixths"><p class="content_block">column five_sixths</p></div>')
                        ->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
