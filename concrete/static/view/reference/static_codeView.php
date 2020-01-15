<?php

class StaticCodeView extends GI_View {
    
    /**
     * @var GI_Form
     */
    protected $form = NULL;
    protected $currentStyle = 'default';

    public function __construct() {
        parent::__construct();
        $this->form = new GI_Form('code_form');
        $this->currentStyle = SYNTAXHIGHLIGHTER_STYLE;
        $this->buildForm();
        $this->addSiteTitle('Code');
        if($this->form->wasSubmitted()){
            $this->currentStyle = filter_input(INPUT_POST, 'style');
            $this->addCSS('resources/external/js/syntaxhighlighter/themes/' . $this->currentStyle . '.css');
        }
    }
    
    protected function getExampleCode(){
        $code = '<html>
<head>
    <title>Code Demo</title>
</head>
 
<body>
 
<?
/***********************************
 ** Comment
 **********************************/
 
$stringWithUrl = "http://generalinternet.ca";
$stringWithOutUrl = \'General Internet\';
function coolFunction($string) {
    global $globalVar;
 
    $boolean = false;
    // comment again.
    if($string && !$boolean) {
        $string = str_replace("    ", "", $string);
        $string = str_replace("\n", "", $string);
        $string = str_replace(chr(13), "", $string);
    }
}
coolFunction("crazy string");      // Start Code Buffering
session_start();

?>
 
<!-- Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. -->
 
</body>
</html>';
        return $code;
    }
    
    protected function buildForm(){
        $form = $this->form;
        $form->addHTML('<div class="columns fifths">');
            $form->addField('lang', 'dropdown', array(
                'displayName' => 'Language',
                'optionGroups' => CodeLangDefinitions::getCodeLanguages(),
                'formElementClass' => 'column',
                'required' => true
            ));
            $form->addField('style', 'dropdown', array(
                'displayName' => 'Style',
                'options' => array(
                    'default' => 'default',
                    'django' => 'django',
                    'eclipse' => 'eclipse',
                    'emacs' => 'emacs',
                    'fadetogrey' => 'fadetogrey',
                    'mdultra' => 'mdultra',
                    'midnight' => 'midnight',
                    'rdark' => 'rdark',
                    'swift' => 'swift'
                ),
                'value' => $this->currentStyle,
                'hideNull' => true,
                'formElementClass' => 'column',
                'required' => true
            ));
            $form->addField('start_line', 'integer_pos', array(
                'displayName' => 'Starting Line Number',
                'value' => 1,
                'required' => true,
                'formElementClass' => 'column'
            ));
            $form->addField('highlight', 'text', array(
                'displayName' => 'Lines to highlight',
                'placeHolder' => 'ex. "2, 3, 4, 6-15"',
                'value' => '',
                'formElementClass' => 'column'
            ));
            $form->addField('html_script', 'onoff', array(
                'displayName' => 'Html-Script?',
                'onoffStyleAsCheckbox' => true,
                'formElementClass' => 'column'
            ));
        $form->addHTML('</div>');
        $form->addField('code', 'textarea', array(
            'placeHolder' => 'Enter code here...',
            'required' => true,
            'value' => $this->getExampleCode()
        ));
        $form->addHTML('<span class="submit_btn">Submit</span>');
    }
    
    protected function buildView() {
        $this->addHTML('<div class="view_wrap">');
            $this->addHTML('<div class="view_header">');
                $this->addMainTitle('Code');
            $this->addHTML('</div>');
        if(!is_null($this->form)){
            $this->addHTML('<div class="view_body">');
            if($this->form->wasSubmitted()){
                $code = filter_input(INPUT_POST, 'code');
                $codeLang = filter_input(INPUT_POST, 'lang');
                $startLine = filter_input(INPUT_POST, 'start_line');
                $highlight = filter_input(INPUT_POST, 'highlight');
                $htmlScript = filter_input(INPUT_POST, 'html_script');
                $htmlScriptBool = 'false';
                if($htmlScript){
                    $htmlScriptBool = 'true';
                }
                $highlightArray = explode(',', $highlight);
                $lineNumbers = array();
                foreach($highlightArray as $lineNumber){
                    if (strpos($lineNumber, '-') !== false) {
                        $numberToNumber = explode('-', $lineNumber);
                        for($i=trim($numberToNumber[0]); $i<=trim($numberToNumber[1]); $i++){
                            $lineNumbers[] = $i;
                        }
                    } else {
                        $lineNumbers[] = $lineNumber;
                    }
                }
                $this->addHTML('<h3>Posted Code <i class="sml_text">Lang: ' . $codeLang . '</i> <i class="sml_text">Style: ' . $this->currentStyle . '</i></h3>');
                $this->addHTML('<pre class="brush: ' . $codeLang . '; first-line: ' . $startLine . '; gutter: true; highlight: [' . implode(', ', $lineNumbers) . ']; html-script: ' . $htmlScriptBool . ';">');
                $this->addHTML(htmlspecialchars($code));
                $this->addHTML('</pre>');
                $this->addHTML('<hr/>');
            }
            
            $formHTML = $this->form->getForm();        
            $this->addHTML($formHTML);
            $this->addHTML('</div>');
        }
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
