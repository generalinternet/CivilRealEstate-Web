<?php

class StaticErrorCodesView extends GI_View {
    
    public function __construct() {
        $this->addSiteTitle('Error Codes');
        parent::__construct();
        $this->addJS('resources/external/js/jSignature/flashcanvas.js');
        $this->addJS('resources/external/js/jSignature/jSignature.min.js');
    }
    
    protected function buildView(){
        $this->openViewWrap();
        $this->addMainTitle('Error Codes');
        $this->addHTML('<p>To redirect to an error page, use the following GI_URLUtils method.</p>');
        $this->addHTML('<pre class="brush: php; html-script: true; class-name: \'wrap_lines\';" >')
                ->addHTML('GI_URLUtils::redirectToError($errorCode);')
                ->addHTML('</pre>');
        
        $this->addHTML('<div class="flex_table">');
            $this->addHTML('<div class="flex_row flex_head">')
                    ->addHTML('<span class="flex_col sml">Code</span>')
                    ->addHTML('<span class="flex_col">Error</span>')
                    ->addHTML('</div>');
            
        $errorCodes = Lang::getErrorCodes();
        foreach($errorCodes as $errorCode => $errorTitle){
            $this->addHTML('<div class="flex_row">')
                    ->addHTML('<span class="flex_col sml"><b>' . $errorCode . '</b></span>')
                    ->addHTML('<span class="flex_col">' . $errorTitle . '</span>')
                    ->addHTML('</div>');
        }
        $this->addHTML('</div>');
        $this->closeViewWrap();
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
