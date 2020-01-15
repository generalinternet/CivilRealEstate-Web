<?php

class StaticElementPaletteView extends GI_View {
    
    /**
     * @var GI_Form
     */
    protected $form = NULL;
    
    public function __construct() {
        $this->addSiteTitle('Element Palette');
        $this->form = new GI_Form('example_form');
        $this->buildForm();
        parent::__construct();
        $this->addJS('resources/external/js/jSignature/flashcanvas.js');
        $this->addJS('resources/external/js/jSignature/jSignature.min.js');
        $this->addFinalContent('<script>
syntaxhighlighterConfig.gutter = false;
</script>');
    }
    
    public function buildForm(){
        $form = $this->form;
        $form->addHTML('<hr/>');
        $form->addHTML('<h2>Form Elements</h2>');

        $form->startFieldset('Standard');
        $this->addTextFieldBlock();
        $this->addEmailFieldBlock();
        $this->addPasswordFieldBlock();
        $this->addPhoneFieldBlock();
        $this->addURLFieldBlock();
        $form->endFieldset();
        
        $form->startFieldset('Date/time');
        $this->addDateFieldBlock();
        $this->addTimeFieldBlock();
        $this->addDateTimeFieldBlock();
        $form->endFieldset();
        
        $form->startFieldset('Numeric');
        $this->addIntegerFieldBlock();
        $this->addDecimalFieldBlock();
        $this->addPercentageFieldBlock();
        $this->addMoneyFieldBlock();
        $form->endFieldset();

        $form->startFieldset('Selection');
        $options = array(
            'apple' => 'Apple',
            'banana' => 'Banana',
            'cranberry' => 'Cranberry'
        );

        $optionGroups = array(
            'Fruits' => array(
                'apple' => 'Apple',
                'banana' => 'Banana',
                'cranberry' => 'Cranberry'
            ),
            'Vegetables' => array(
                'artichoke' => 'Artichoke',
                'broccoli' => 'Broccoli',
                'carrots' => 'Carrots'
            )
        );
        
        $this->addCheckboxFieldBlock($options);
        $this->addRadioFieldBlock($optionGroups);
        $this->addCheckboxListFieldBlock($options);
        $this->addRadioListFieldBlock($optionGroups);
        $this->addCheckboxFieldWithDisabledOptionBlock($options);
        $this->addRadioFieldWithDisabledGroupBlock($optionGroups);
        $this->addDropdownFieldBlock($options);
        $this->addSelectFieldBlock($optionGroups);
        $this->addDropdownFieldWithDisabledOptionBlock($options);
        $this->addSelectFieldWithDisabledGroupBlock($optionGroups);
        $this->addAutocompleteFieldBlock();
        $this->addOnOffFieldBlock();
        $form->endFieldset();
        
        $form->startFieldset('Text');
        $this->addTextAreaFieldBlock();
        $this->addWYSIWYGFieldBlock();
        $form->endFieldset();
        
        $form->startFieldset('Other');
        $this->addTagFieldBlock();
        $this->addColourFieldBlock();
        $this->addSignatureFieldBlock();
        $this->addToggleThisFieldBlock();
        $this->addToggleThatFieldBlock();
        $this->addToggleOneOfTheseFieldBlock();
        $this->addApproxFieldBlock();
        $this->addHiddenFieldBlock();
        $this->addReCaptchaFieldBlock();
        $form->endFieldset();
        
        $this->addSelectRowsTable();
    }
    
    protected function addSelectRowsTable(){
        $form = $this->form;
        $form->startFieldset('Select Rows');
        //getting permissions as an example
            $permSearch = PermissionFactory::searchRestricted()
                    ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                    ->orderBy('title', 'ASC');
            $permissions = $permSearch->select();
            if($permissions){
                /* @var $firstPerm AbstractPermission */
                $firstPerm = $permissions[0];

                $uiTableCols = $firstPerm->getUITableCols();

                $pageBar = $permSearch->getPageBar(array(
                    'controller' => 'permission',
                    'action' => 'index',
                    'addSelectRowCol' => 1
                ));
                $pageBar->setUseAjax(true);
                $uiTable = new UITableView($permissions, $uiTableCols, $pageBar);
                $uiTable->addDefaultSelectRowColumn();
                $form->addHTML($uiTable->getHTMLView());
                
                $uiTableColPHP = '$onoffTableCol = UITableCol::buildUITableColFromArray(array(
    \'header_title\' => Model::getSelectAllRowsOnOff(),
    \'header_hover_title\' => \'Select All\',
    \'css_header_class\' => \'select_all_column\',
    \'method_name\' => \'getSelectRowOnOff\',
    \'css_class\' => \'select_row_column\',
    \'cell_hover_title_method_name\' => \'getSelectRowOnOffHoverTitle\'
));

//to put the column at the beginning of any uiTableCol array where $uiTableCols is an array of UITableCols
array_unshift($uiTableCols, $onoffTableCol);

//an alternative to adding the column manually (like above) where $uiTable is an instance of UITableView
//this method requires models to be set in $uiTable
$uiTable->addDefaultSelectRowColumn();
//alternatively you can provide the model class name to the same method
$uiTable->addDefaultSelectRowColumn(\'modelClassName\');
';
            $form->addHTML('<div class="html_and_code_block">');
            $form->addHTML('<h5>UI Column Settings</h5>');
            $form->addHTML('<pre class="brush: php; class-name: \'wrap_lines\';" >')
                    ->addHTML(htmlentities($uiTableColPHP))
                    ->addHTML('</pre>');
            $form->addHTML('</div>');
        } else {
            $form->addHTML('<div class="alert_message red"><p>Could not find any permissions to use as an example table.</p></div>');
        }
        $form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Pagination Bar Codeception Acceptance Test Code</h5>');
        $paginationBarTestCode = '$I->scrollTo(\'.pagination_bar\');
$currentPageBefore = $I->grabAttributeFrom(\'ul.pagination li.current a\', \'data-page\');
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    $elements = $webdriver->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector(\'.pagination_bar .pagination li\'));
    \PHPUnit\Framework\Assert::assertNotEmpty($elements);
    $currentPage = 0;
    foreach ($elements as $pageButton) {
        $links = $pageButton->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector(\'a\'));
            if (!empty($links)) {
                $link = $links[0];
                $pageNumber = $link->getAttribute(\'data-page\');
                if ($pageButton->getAttribute(\'class\') == \'current\') {
                    $currentPage = $pageNumber;
                } else if (!empty($currentPage) && $pageNumber > 4) {
                    $pageButton->click();
                    break;
                }
            }
    }
});
$I->waitForJS("return $.active == 0;", 60); //wait up to 60 seconds for any AJAX requests to complete
$currentPageAfter = $I->grabAttributeFrom(\'ul.pagination li.current a\', \'data-page\');
\PHPUnit\Framework\Assert::assertNotEquals($currentPageAfter, $currentPageBefore);';
        $pagniationBarTestCodeHTML = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($paginationBarTestCode)
                . '</pre>';
        $this->form->addHTML($pagniationBarTestCodeHTML);
        $form->addHTML('</div>')
                ->addHTML('<div class="column">');

        $form->addHTML('</div>')
                ->addHTML('</div>');
        $form->endFieldset();
    }

    protected function addSignatureBlock() {
        $signatureOptions = array(
            'displayName' => 'Signature Field'
        );
        
        $fieldHTML = $this->getFieldCodeBlockHTML('signature', 'signature', $signatureOptions);
        
        $jsPHP = '$this->addJS(\'resources/external/js/jSignature/flashcanvas.js\');
$this->addJS(\'resources/external/js/jSignature/jSignature.min.js\');';
        
        $valuePHP = '/* 
* @param string $signatureField : signature field name
* @param GI_Model $model
* @return AbstractFile
*/
$signatureFile = FileFactory::getSignatureFromModel($signatureField, $model);';
        
        $savePHP = '/*
* @param string $signatureField : signature field name
* @param GI_Model $model
* @param boolean $removeIfEmpty
* @return boolean
*/
FileFactory::saveSignatureToModel($signatureField, $model, $removeIfEmpty);';
        
        $this->form->addHTML('<div class="html_and_code_block">');
        $this->form->addField('signature', 'signature', $signatureOptions);
        $this->form->addHTML($fieldHTML);
        $this->form->addHTML('<h5>Required JS</h5>');
        $this->form->addHTML('<pre class="brush: php; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($jsPHP))
                ->addHTML('</pre>');
        $this->form->addHTML('<h5>Get Existing Signature (\'signatureFile\' for field settings)</h5>');
        $this->form->addHTML('<pre class="brush: php; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($valuePHP))
                ->addHTML('</pre>');
        $this->form->addHTML('<h5>Save/Replace Existing Signature</h5>');
        $this->form->addHTML('<pre class="brush: php; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($savePHP))
                ->addHTML('</pre>');
        $this->form->addHTML('</div>');
    }
    
    protected function addRadioTogglerBlock(){
        $radioTogglerOptions = array(
            'displayName' => 'Toggle This?',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            ),
            'value' => 'yes',
            'stayOn' => true,
            'fieldClass' => 'radio_toggler'
        );
        
        $fieldHTML = $this->getFieldCodeBlockHTML('toggle_this', 'radio', $radioTogglerOptions);
        
        $boxHTML = '<div class="radio_toggler_element form_element" data-group="toggle_this" data-element="yes">
    <p>"Yes" is selected.</p>
</div>
<div class="radio_toggler_element form_element" data-group="toggle_this" data-element="no">
    <p>"No" is selected.</p>
</div>';
        
        $this->form->addHTML('<div class="html_and_code_block">');
        $this->form->addField('toggle_this', 'radio', $radioTogglerOptions);
        $this->form->addHTML($boxHTML);
        $this->form->addHTML($fieldHTML);
        $this->form->addHTML('<pre class="brush: php; html-script: true; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($boxHTML))
                ->addHTML('</pre>');
        $this->form->addHTML('</div>');
    }
    
    protected function addDropdownTogglerBlock(){
        $togglerOptions = array(
            'displayName' => 'Toggle That?',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            ),
            'value' => 'yes',
            'fieldClass' => 'toggler'
        );
        
        $fieldHTML = $this->getFieldCodeBlockHTML('toggle_that', 'dropdown', $togglerOptions);
        
        $boxHTML = '<div class="toggler_element form_element" data-group="toggle_that" data-element="yes">
    <p>"Yes" is selected.</p>
</div>
<div class="toggler_element form_element" data-group="toggle_that" data-element="no">
    <p>"No" is selected.</p>
</div>';
        
        $this->form->addHTML('<div class="html_and_code_block">');
        $this->form->addField('toggle_that', 'dropdown', $togglerOptions);
        $this->form->addHTML($boxHTML);
        $this->form->addHTML($fieldHTML);
        $this->form->addHTML('<pre class="brush: php; html-script: true; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($boxHTML))
                ->addHTML('</pre>');
        $this->form->addHTML('</div>');
    }
    
    protected function addCheckboxTogglerBlock(){
        $options = array(
            'apple' => 'Apple',
            'banana' => 'Banana',
            'cranberry' => 'Cranberry'
        );
        $checkboxTogglerOptions = array(
            'displayName' => 'Toggle One of these?',
            'options' => $options,
            'value' => array('apple', 'cranberry'),
            'fieldClass' => 'checkbox_toggler'
        );
        
        $fieldHTML = $this->getFieldCodeBlockHTML('toggle_one_of_these', 'checkbox', $checkboxTogglerOptions);
        
        $boxHTML = '<div class="checkbox_toggler_element form_element" data-group="toggle_one_of_these" data-element="apple">
                        <p>"Apple" is selected.</p>
                    </div>
                    <div class="checkbox_toggler_element form_element" data-group="toggle_one_of_these" data-element="banana">
                        <p>"Banana" is selected.</p>
                    </div>
                    <div class="checkbox_toggler_element form_element" data-group="toggle_one_of_these" data-element="cranberry">
                        <p>"Cranberry" is selected.</p>
                    </div>';
        
        $this->form->addHTML('<div class="html_and_code_block">');
        $this->form->addField('toggle_one_of_these', 'checkbox', $checkboxTogglerOptions);
        $this->form->addHTML($boxHTML);
        $this->form->addHTML($fieldHTML);
        $this->form->addHTML('<pre class="brush: php; html-script: true; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($boxHTML))
                ->addHTML('</pre>');
        $this->form->addHTML('</div>');
    }
    
    public function getArrayStringForCodeBlock($array, $tabs = 0){
        $arrayString = 'array(';
        $arrayValAdded = false;
        foreach($array as $key => $val){
            if($arrayValAdded){
                $arrayString .= ',';
            }
            $arrayString .= "\n";
            $arrayString .= "\t";
            for($i=0; $i<$tabs; $i++){
                $arrayString .= "\t";
            }
            if(!is_int($key)){
                $arrayString .= '\'' . $key . '\' => ';
            }
            if(is_array($val)){
                $newTabs = $tabs+1;
                $arrayString .= $this->getArrayStringForCodeBlock($val, $newTabs);
            } elseif(is_bool($val)){
                $arrayString .= var_export($val, true);
            } else {
                $arrayString .= '\'' . $val . '\'';
            }
            $arrayValAdded = true;
        }
        $arrayString .= "\n";
        for($i=0; $i<$tabs; $i++){
            $arrayString .= "\t";
        }
        $arrayString .= ')';
        
        return $arrayString;
    }
    
    public function addFieldAndCodeBlock($fieldType, $fieldOptions = array()){
        $form = $this->form;
        $form->addHTML('<div class="html_and_code_block">');
        $fieldName = $fieldType . '_field';
        if(isset($fieldOptions['fieldName'])){
            $fieldName = $fieldOptions['fieldName'];
        }
        $form->addField($fieldName, $fieldType, $fieldOptions);
        $fieldHTML = $this->getFieldCodeBlockHTML($fieldName, $fieldType, $fieldOptions);
        $form->addHTML($fieldHTML);
        $form->addHTML('</div>');
    }
    
    public function getFieldCodeBlockHTML($fieldName, $fieldType, $fieldOptions = array()){
        $optionString = $this->getArrayStringForCodeBlock($fieldOptions);
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities('$form->addField(\'' . $fieldName . '\', \'' . $fieldType . '\', ' . $optionString . ');')
                .'</pre>';
        
        return $html;
    }
    
    public function addHTMLAndCodeBlock($html){
        $this->addHTML('<div class="html_and_code_block">');
        $this->addHTML($html);
        $this->addHTML('<pre class="brush: php; html-script: true; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($html))
                ->addHTML('</pre>');
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView(){
        $this->addHTML('<div class="view_wrap">');
            $this->addHTML('<div class="view_body">');
            $this->addHTMLAndCodeBlock('<h1>Heading 1</h1>');
            $this->addHTMLAndCodeBlock('<h2>Heading 2</h2>');
            $this->addHTMLAndCodeBlock('<h3>Heading 3</h3>');
            $this->addHTMLAndCodeBlock('<h4>Heading 4</h4>');
            $this->addHTMLAndCodeBlock('<h5>Heading 5</h5>');
            $this->addHTMLAndCodeBlock('<h6>Heading 6</h6>');
            
            $this->addHTMLAndCodeBlock('<p>A paragraph (from the Greek paragraphos, “to write beside” or “written beside”) is a self-contained unit of a discourse in writing dealing with a particular point or idea. A paragraph consists of one or more sentences. Though not required by the syntax of any language, paragraphs are usually an expected part of formal writing, used to organize longer prose.</p>');
            
            $this->addHTMLAndCodeBlock('<blockquote>
    <p>A block quotation (also known as a long quotation or extract) is a quotation in a written document, that is set off from the main text as a paragraph, or block of text.</p>
    <p>It is typically distinguished visually using indentation and a different typeface or smaller size quotation. It may or may not include a citation, usually placed at the bottom.</p>
    <cite><a href="#!">Said no one, ever.</a></cite>
</blockquote>');
            $this->addHTMLAndCodeBlock('<dl>
    <dt>Definition List Title</dt>
    <dd>This is a definition list division.</dd>
</dl>');
            $this->addHTMLAndCodeBlock('<ol>
    <li>Ordered List Item 1</li>
    <li>Ordered List Item 2</li>
    <li>Ordered List Item 3</li>
</ol>');
            $this->addHTMLAndCodeBlock('<ul>
    <li>Unordered List Item 1</li>
    <li>Unordered List Item 2</li>
    <li>Unordered List Item 3</li>
</ul>');
            $this->addHTMLAndCodeBlock('<hr/>');
            $this->addHTMLAndCodeBlock('<div class="flex_table limit_mobile">
    <div class="flex_row flex_head">
        <div class="flex_col keep">Flex Head 1</div>
        <div class="flex_col">Flex Head 2</div>
        <div class="flex_col">Flex Head 3</div>
        <div class="flex_col keep">Flex Head 4</div>
    </div>
    <div class="flex_row">
        <div class="flex_col keep">Flex Cell 1</div>
        <div class="flex_col">Flex Cell 2</div>
        <div class="flex_col">Flex Cell 3</div>
        <div class="flex_col keep">Flex Cell 4</div>
    </div>
    <div class="flex_row">
        <div class="flex_col keep">Flex Cell 1</div>
        <div class="flex_col">Flex Cell 2</div>
        <div class="flex_col">Flex Cell 3</div>
        <div class="flex_col keep">Flex Cell 4</div>
    </div>
    <div class="flex_row">
        <div class="flex_col keep">Flex Cell 1</div>
        <div class="flex_col">Flex Cell 2</div>
        <div class="flex_col">Flex Cell 3</div>
        <div class="flex_col keep">Flex Cell 4</div>
    </div>
    <div class="flex_row flex_foot">
        <div class="flex_col keep">Flex Foot 1</div>
        <div class="flex_col">Flex Foot 2</div>
        <div class="flex_col">Flex Foot 3</div>
        <div class="flex_col keep">Flex Foot 4</div>
    </div>
</div>');
            $this->addHTMLAndCodeBlock('<table class="ui_table limit_mobile">
    <caption>Table Caption</caption>
    <thead>
        <tr>
            <th class="keep">Table Heading 1</th>
            <th>Table Heading 2</th>
            <th>Table Heading 3</th>
            <th class="keep">Table Heading 4</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th class="keep">Table Footer 1</th>
            <th>Table Footer 2</th>
            <th>Table Footer 3</th>
            <th class="keep">Table Footer 4</th>
        </tr>
    </tfoot>
    <tbody>
        <tr>
            <td class="keep">Table Cell 1</td>
            <td>Table Cell 2</td>
            <td>Table Cell 3</td>
            <td class="keep">Table Cell 4</td>
        </tr>
        <tr>
            <td class="keep">Table Cell 1</td>
            <td>Table Cell 2</td>
            <td>Table Cell 3</td>
            <td class="keep">Table Cell 4</td>
        </tr>
        <tr>
            <td class="empty keep"></td>
            <td>Table Cell 2</td>
            <td>Table Cell 3</td>
            <td class="keep">Table Cell 4</td>
        </tr>
        <tr>
            <td colspan="3">Table Cell 1</td>
            <td>Table Cell 2</td>
        </tr>
    </tbody>
</table>');
        
        $this->addHTML('<div class="auto_columns">');
            $this->addHTMLAndCodeBlock('<p><a href="#!">This is a text link</a>.</p>');
            $this->addHTMLAndCodeBlock('<p><strong>Strong is used to indicate strong importance.</strong></p>');
            $this->addHTMLAndCodeBlock('<p><em>This text has added emphasis.</em></p>');
            $this->addHTMLAndCodeBlock('<p>The <b>b element</b> is stylistically different text from normal text, without any special importance.</p>');
            $this->addHTMLAndCodeBlock('<p>The <i>i element</i> is text that is offset from the normal text.</p>');
            $this->addHTMLAndCodeBlock('<p>The <u>u element</u> is text with an unarticulated, though explicitly rendered, non-textual annotation.</p>');
            $this->addHTMLAndCodeBlock('<p><del>This text is deleted</del> and <ins>This text is inserted</ins>.</p>');
            $this->addHTMLAndCodeBlock('<p><s>This text has a strikethrough</s>.</p>');
            $this->addHTMLAndCodeBlock('<p>Superscript<sup>®</sup>.</p>');
            $this->addHTMLAndCodeBlock('<p>Subscript for things like H<sub>2</sub>O.</p>');
            $this->addHTMLAndCodeBlock('<p><small>This small text is small for for fine print, etc.</small></p>');
            $this->addHTMLAndCodeBlock('<p>Abbreviation: <abbr title="HyperText Markup Language">HTML</abbr></p>');
            $this->addHTMLAndCodeBlock('<p><q cite="http://generalinternet.ca">This text is a short inline quotation.</q></p>');
            $this->addHTMLAndCodeBlock('<p><cite>This is a citation.</cite></p>');
            $this->addHTMLAndCodeBlock('<p>The <dfn>dfn element</dfn> indicates a definition.</p>');
            $this->addHTMLAndCodeBlock('<p>The <mark>mark element</mark> indicates a highlight.</p>');
            $this->addHTMLAndCodeBlock('<p>The <var>variable element</var>, such as <var>x</var> = <var>y</var>.</p>');
            $this->addHTMLAndCodeBlock('<p>The time element: <time datetime="2013-04-06T12:32+00:00">2 weeks ago</time></p>');
            $this->addHTMLAndCodeBlock('<div class="wrap_btns">
    <span class="other_btn" title="Button">Button</span>
    <span class="other_btn disabled" title="Disabled Button">Disabled</span>
</div>');
            
            $this->addHTMLAndCodeBlock('<p>
    <span class="gray"><b>Gray</b> Text</span><br/>
    <span class="blue"><b>Blue</b> Text</span><br/>
    <span class="green"><b>Green</b> Text</span><br/>
    <span class="yellow"><b>Yellow</b> Text</span><br/>
    <span class="red"><b>Red</b> Text</span>
</p>');
            
            $this->addHTMLAndCodeBlock('<div class="alert_message"><p><b>Gray</b> alert message.</p></div>
<div class="alert_message blue"><p><b>Blue</b> alert message.</p></div>
<div class="alert_message green"><p><b>Green</b> alert message.</p></div>
<div class="alert_message yellow"><p><b>Yellow</b> alert message.</p></div>
<div class="alert_message red"><p><b>Red</b> alert message.</p></div>');
            
            $this->addHTMLAndCodeBlock('<p>
    <span class="status_box gray">Gray</span><br/>
    <span class="status_box blue">Blue</span><br/>
    <span class="status_box green">Green</span><br/>
    <span class="status_box yellow">Yellow</span><br/>
    <span class="status_box red">Red</span>
</p>');

        $this->addHTML('</div>');

        $this->addAdvancedSections();

        $this->addContentBlockSections();

        $this->addHTML($this->form->getForm());

        $this->addHTML('<hr/>');

        $this->addAjaxLoadedSignatureBlock();

        $this->addAjaxLoadedContentBlock();

            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    public function addAdvancedSections() {
        $this->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        //data-open-icon="arrow_right border" data-close-icon="arrow_down border"
        $this->addHTMLAndCodeBlock('<div class="advanced">
    <span class="custom_btn advanced_btn"><span class="icon_wrap"><span class="icon"></span></span><span class="btn_text">Details</span></span>
    <div class="advanced_content">
        <br/>
        <p>Hidden details.</p>
    </div>
</div>');
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addHTMLAndCodeBlock('<span class="custom_btn advanced_btn" data-adv-id="additional_details"><span class="icon_wrap"><span class="icon plus"></span></span><span class="btn_text">Details</span></span>
<div id="additional_details" class="advanced">
    <div class="advanced_content">
        <br/>
        <p>Hidden details.</p>
    </div>
</div>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    public function addContentBlockSections(){
        $phpBlocks = '$this->addContentBlock(\'Value 01\', \'Label 01\');
$this->addContentBlock(\'Value 02\', \'Label 03\');
$this->addContentBlock(NULL, \'Label 03\', true);
$this->addContentBlock(NULL, \'Label 04\');
$this->addContentBlock(NULL, \'Label 05\', true, NULL, \'****\');';
        
        $this->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        
                $this->addHTML('<div class="html_and_code_block">');
                $this->addContentBlock('Value 01', 'Label 01');
                $this->addContentBlock('Value 02', 'Label 03');
                $this->addContentBlock(NULL, 'Label 03', true);
                $this->addContentBlock(NULL, 'Label 04');
                $this->addContentBlock(NULL, 'Label 05', true, NULL, '****');
                $this->addHTML('<pre class="brush: php; class-name: \'wrap_lines\';" >')
                        ->addHTML(htmlentities($phpBlocks))
                        ->addHTML('</pre>');
                $this->addHTML('</div>');
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        
        $phpListBlocks = '$forceShow = true;
$emptyValue = \'N/A\';
$this->addHTML(\'<div class="list_block">\');
    $this->addContentBlockWithWrap(\'Value 01\', \'Label 01\');
    $this->addContentBlockWithWrap(\'Value 02\', \'Label 03\');
    $this->addContentBlockWithWrap(NULL, \'Label 03\', $forceShow);
    $this->addContentBlockWithWrap(NULL, \'Label 04\');
    $this->addContentBlockWithWrap(NULL, \'Label 05\', $forceShow, NULL, $emptyValue);
$this->addHTML(\'</div>\');';
        
        $this->addHTML('<div class="html_and_code_block">');
        $this->addHTML('<div class="list_block">');
        $this->addContentBlockWithWrap('Value 01', 'Label 01');
        $this->addContentBlockWithWrap('Value 02', 'Label 03');
        $this->addContentBlockWithWrap(NULL, 'Label 03', true);
        $this->addContentBlockWithWrap(NULL, 'Label 04');
        $this->addContentBlockWithWrap(NULL, 'Label 05', true, NULL, 'N/A');
        $this->addHTML('</div>');
        $this->addHTML('<pre class="brush: php; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($phpListBlocks))
                ->addHTML('</pre>');
        $this->addHTML('</div>');
        
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function getInceptionParagraph(){
        $url = GI_URLUtils::buildURL(array(
            'controller' => 'static',
            'action' => 'error',
            'errorCode' => 528491
        ));
        $inceptionQuotes = array(
            'I’m going to improvise. Listen, there’s something you should know about me... about <a href="' . $url . '" title="Inception">inception</a>. An idea is like a <i>virus, resilient, highly contagious</i>. The <b>smallest seed</b> of an idea can grow. It can grow to define or <u>destroy you</u>.',
            'To wake up from that after, after years, after decades... after we’d become <b>old souls</b> thrown back into youth like that... I knew something was wrong with her. She just wouldn’t admit it. Eventually, she told me the truth. She was <i>possessed</i> by an <a href="' . $url . '" title="Inception">idea</a>, this one, very simple idea, that changed everything. That our world wasn’t real. That she needed to wake up to come back to reality, that, in order to get back home, we had to <u>kill ourselves</u>.',
            'You’re waiting for a train, a train that will take you <i>far away</i>. You know where you hope this train will take you, but you <u>can’t be sure</u>. but it <b>doesn’t matter</b> - because we’ll be <a href="' . $url . '" title="Inception">together</a>.',
            'I guess I thought the <a href="' . $url . '" title="Inception">dream-space</a> would be all about the <b>visual</b>, but it’s more about <i>the feeling</i>. My question is what happens when you start messing with the <u>physics</u> of it.',
            'Well <a href="' . $url . '" title="Inception">dreams</a>, they <i>feel real</i> while we’re in them, right? It’s only when we wake up that we realize how things are <u>actually strange</u>. Let me ask you a question, you, you never really remember the beginning of a dream do you? You always wind up right in the <b>middle of what’s going on</b>.',
            'Because building a dream from your memory is the easiest way to lose your grasp on what’s real and what is a dream.',
            'Nah, I can’t let you touch it, that would defeat the purpose. See only I know the <i>balance</i> and <i>weight</i> of this particular <b>loaded die</b>. That way when you look at your <u>totem</u>, you know beyond a doubt you’re not in someone else’s dream.',
            'Remember, you are the dreamer, you build this world. I am the subject, my mind populates it. You can literally talk to my subconscious. That’s one of the ways we extract information from the subject.',
            'I can access your mind through your dreams.'
        );
        $randMax = count($inceptionQuotes) - 1;
        $dreamKey = mt_rand(0, $randMax);
        while ($dreamKey == $this->lastUsedDreamKey) {
            $dreamKey = mt_rand(0, $randMax);
        }

        $this->lastUsedDreamKey = $dreamKey;
        return '<p>' . $inceptionQuotes[$dreamKey] . '</p>';
    }

    protected function addTextFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('text', array(
            'displayName' => 'Text Field',
            'placeHolder' => 'Enter text...'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_text_field\');
$sampleText = \'This is an example paragraph of text.\';
$this->addHTML(\'<div class="list_block">\');
$I->fillField(\'text_field\', $sampleText);
$I->seeInField(\'#field_text_field\', $sampleText);';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addEmailFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('email', array(
            'displayName' => 'Email Field',
            'placeHolder' => 'ex. john.smith@email.com',
            'required' => true
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addPasswordFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('password', array(
            'displayName' => 'Password Field',
            'placeHolder' => 'Enter password...'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addPhoneFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('phone', array(
            'displayName' => 'Phone Field',
            'placeHolder' => 'ex. (604) 123-4567'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addURLFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('url', array(
            'displayName' => 'URL Field',
            'placeHolder' => 'ex. www.website.com'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addDateFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $dateObj = new DateTime();
        $this->addFieldAndCodeBlock('date', array(
            'displayName' => 'Date Field',
            'placeHolder' => 'ex. ' . $dateObj->format('Y-m-d')
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#field_date_field\');
$I->click(\'#field_date_field\');
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    //Try to select March 15, 2018
    $yearDropdown = new Facebook\WebDriver\WebDriverSelect($webdriver->findElement(WebDriverBy::className(\'ui-datepicker-year\')));
    $yearDropdown->selectByVisibleText(\'2018\');
    $monthDropdown = new Facebook\WebDriver\WebDriverSelect($webdriver->findElement(WebDriverBy::className(\'ui-datepicker-month\')));
    $monthDropdown->selectByVisibleText(\'Mar\');
    $items = $webdriver->findElements(WebDriverBy::cssSelector(\'.ui-datepicker-calendar td\'));
    \PHPUnit\Framework\Assert::assertNotEmpty($items);
    foreach ($items as $date) {
        if ($date->getText() == \'15\') {
            $date->click();
            break;
        }
    }
});
$I->seeInField(\'#field_date_field\', \'2018-03-15\');';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addTimeFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $dateObj = new DateTime();
        $this->addFieldAndCodeBlock('time', array(
            'displayName' => 'Time Field',
            'placeHolder' => 'ex. ' . $dateObj->format('h:i a')
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#field_time_field\');
$I->click(\'#field_time_field\');
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    $hourDropdown = NULL;
    $minuteDropdown = NULL;
    $dropdownElements = $webdriver->findElements(WebDriverBy::className(\'ui-timepicker-select\'));
        foreach ($dropdownElements as $dropdownElement) {
            $unit = $dropdownElement->getAttribute(\'data-unit\');
            if ($unit == \'minute\') {
                $minuteDropdown = new Facebook\WebDriver\WebDriverSelect($dropdownElement);
            }
        }
    \PHPUnit\Framework\Assert::assertNotEmpty($minuteDropdown);
    $minuteDropdown->selectByVisibleText(\'38\');
    $dropdownElements = $webdriver->findElements(WebDriverBy::className(\'ui-timepicker-select\'));
    foreach ($dropdownElements as $dropdownElement) {
        $unit = $dropdownElement->getAttribute(\'data-unit\');
            if ($unit == \'hour\') {
                $hourDropdown = new Facebook\WebDriver\WebDriverSelect($dropdownElement);
            }
        }
    \PHPUnit\Framework\Assert::assertNotEmpty($hourDropdown);     
    $hourDropdown->selectByVisibleText(\'10 am\');
});
$I->seeInField(\'#field_time_field\', \'10:38 am\');';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addDateTimeFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $dateObj = new DateTime();
        $this->addFieldAndCodeBlock('datetime', array(
            'displayName' => 'Date/Time Field',
            'placeHolder' => 'ex. ' . $dateObj->format('Y-m-d h:i a')
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#field_datetime_field\');
$I->click(\'#field_datetime_field\');
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    //Try to select December 25, 2016 6:31 PM
    $yearDropdown = new Facebook\WebDriver\WebDriverSelect($webdriver->findElement(WebDriverBy::className(\'ui-datepicker-year\')));
    $yearDropdown->selectByVisibleText(\'2016\');
    $monthDropdown = new Facebook\WebDriver\WebDriverSelect($webdriver->findElement(WebDriverBy::className(\'ui-datepicker-month\')));
    $monthDropdown->selectByVisibleText(\'Dec\');    
    $items = $webdriver->findElements(WebDriverBy::cssSelector(\'.ui-datepicker-calendar td\'));
    \PHPUnit\Framework\Assert::assertNotEmpty($items);
        foreach ($items as $date) {
            if ($date->getText() == \'25\') {
                $date->click();
                break;
            }
        }
    $hourDropdown = NULL;
    $minuteDropdown = NULL;
    $dropdownElements = $webdriver->findElements(WebDriverBy::className(\'ui-timepicker-select\'));
    foreach ($dropdownElements as $dropdownElement) {
        $unit = $dropdownElement->getAttribute(\'data-unit\');
        if ($unit == \'minute\') {
            $minuteDropdown = new Facebook\WebDriver\WebDriverSelect($dropdownElement);
        }
    }
    \PHPUnit\Framework\Assert::assertNotEmpty($minuteDropdown);
    $minuteDropdown->selectByVisibleText(\'31\');
    $dropdownElements = $webdriver->findElements(WebDriverBy::className(\'ui-timepicker-select\'));
    foreach ($dropdownElements as $dropdownElement) {
        $unit = $dropdownElement->getAttribute(\'data-unit\');
        if ($unit == \'hour\') {
            $hourDropdown = new Facebook\WebDriver\WebDriverSelect($dropdownElement);
        }
    }
    \PHPUnit\Framework\Assert::assertNotEmpty($hourDropdown);
    $hourDropdown->selectByVisibleText(\'6 pm\');
});
$I->seeInField(\'#field_datetime_field\', \'2016-12-25 6:31 pm\');';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addIntegerFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('integer', array(
            'displayName' => 'Integer Field',
            'placeHolder' => 'ex. -3, -2, -1, 0, 1, 2, 3'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_integer_field\');
$I->pressKey(\'#field_integer_field\', array(
\'1\',\'.\',\'9\',
));
$I->dontSeeInField(\'#field_integer_field\', \'1.9\');
$I->seeInField(\'#field_integer_field\', \'19\');';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addDecimalFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('decimal', array(
            'displayName' => 'Decimal Field',
            'placeHolder' => 'ex. 1.1234, -1.1234'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_decimal_field\');
$I->pressKey(\'#field_decimal_field\', array(
\'6\',\'J\',\'2\',\'.\',\'3\',
));
$I->dontSeeInField(\'#field_decimal_field\', \'6J2.3\');
$I->seeInField(\'#field_decimal_field\', \'62.3\');';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addPercentageFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('percentage', array(
            'displayName' => 'Percentage Field',
            'placeHolder' => 'ex. 25, 50.5, -20'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_percentage_field\');
$I->pressKey(\'#field_percentage_field\', array(
\'2\',\'3\',\'5\',\'F\', \'.\',\'2\'
));
$I->dontSeeInField(\'#field_percentage_field\', \'235F.2\');
$I->seeInField(\'#field_percentage_field\', \'235.2\');';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addMoneyFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('money', array(
            'displayName' => 'Money Field',
            'placeHolder' => 'ex. 10.99, -20.10'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_money_field\');
$I->pressKey(\'#field_money_field\', array(
\'3\',\'1\',\'7\',\'.\',\'9\',\'1\',\'7\'
));
$I->dontSeeInField(\'#field_money_field\', \'317.917\');
$I->seeInField(\'#field_money_field\', \'317.91\');';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addCheckboxFieldBlock($options) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('checkbox', array(
            'displayName' => 'Checkbox Field',
            'options' => $options
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->seeElementInDOM(\'#felm_checkbox_field\');
$I->scrollTo(\'#felm_checkbox_field\');
//Select 2 options - This is one way of finding/selecting field options - using Webdriver\'s methods
$I->click(\'#felm_checkbox_field .options_wrap label:nth-child(1)\'); //Apple
$I->click(\'#felm_checkbox_field .options_wrap label:nth-child(3)\'); //Cranberry
//Verify that the options are selected
$I->seeInFormFields(\'#example_form\', [
    \'checkbox_field[]\' => [
        \'apple\',
        \'cranberry\'
    ]
]);
//Verify that an option is not selected
$I->dontSeeInFormFields(\'#example_form\', [
    \'checkbox_field[]\' => [
        \'banana\'
    ]
]);';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addRadioFieldBlock($optionGroups) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('radio', array(
            'displayName' => 'Radio Field',
            'optionGroups' => $optionGroups
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->seeElementInDOM(\'#felm_radio_field\');
$I->scrollTo(\'#felm_radio_field\');
$I->click(\'#felm_radio_field .option_group:nth-child(1) label:nth-child(3)\');
$I->seeInFormFields(\'#example_form\', [
\'radio_field\' => \'banana\'
        ]);';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addCheckboxListFieldBlock($options) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('checkbox', array(
            'fieldName' => 'checkbox_list',
            'displayName' => 'Checkbox List',
            'options' => $options,
            'formElementClass' => 'list_options'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addRadioListFieldBlock($optionGroups) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('radio', array(
            'fieldName' => 'radio_list',
            'displayName' => 'Radio List',
            'optionGroups' => $optionGroups,
            'formElementClass' => 'list_options column_groups'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addCheckboxFieldWithDisabledOptionBlock($options) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('checkbox', array(
            'fieldName' => 'checkbox_with_disabled',
            'displayName' => 'Checkbox Field (disabled option)',
            'options' => $options,
            'disabledOptions' => array(
                'banana'
            )
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->seeElementInDOM(\'#felm_checkbox_with_disabled\');
$I->scrollTo(\'#felm_checkbox_with_disabled\');
//This is another way of finding/selection field options - using Selenium\'s methods
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    $options = $webdriver->findElements(WebDriverBy::cssSelector(\'#felm_checkbox_with_disabled .options_wrap label\'));
    foreach ($options as $fieldOption) {
        if ($fieldOption->getText() == \'Apple\' || $fieldOption->getText() == \'Banana\') {
            $fieldOption->click();
        }
    }
});
//Verify that Apple is selected
$I->seeInFormFields(\'#example_form\', [
    \'checkbox_with_disabled[]\' => [
        \'apple\',
    ]
]);
//Verify that Banana and Cranberry are not selected
$I->dontSeeInFormFields(\'#example_form\', [
    \'checkbox_with_disabled[]\' => [
        \'banana\',
        \'cranberry\'
    ]
]);';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addRadioFieldWithDisabledGroupBlock($optionGroups) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('radio', array(
            'fieldName' => 'radio_with_disabled',
            'displayName' => 'Radio Field (disabled group)',
            'optionGroups' => $optionGroups,
            'disabledOptionGroups' => array(
                'Vegetables'
            )
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->seeElementInDOM(\'#felm_radio_with_disabled\');
$I->scrollTo(\'#felm_radio_with_disabled\');
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    $options = $webdriver->findElements(WebDriverBy::cssSelector(\'#felm_radio_with_disabled .options_wrap label\'));
    foreach ($options as $fieldOption) {
        if ($fieldOption->getText() == \'Cranberry\' || $fieldOption->getText() == \'Carrots\') {
            $fieldOption->click();
        }     
    }
});
$I->seeInFormFields(\'#example_form\', [
    \'radio_with_disabled\' => [\'cranberry\']
]);
$I->dontSeeInFormFields(\'#example_form\', [
    \'radio_with_disabled\' => [\'apple\', \'banana\', \'artichoke\', \'broccoli\', \'carrots\']
]);';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addDropdownFieldBlock($options) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('dropdown', array(
            'displayName' => 'Dropdown Field',
            'options' => $options
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_dropdown_field\');
$I->click(\'#felm_dropdown_field .button\');
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    $items = $webdriver->findElements(WebDriverBy::cssSelector(\'#felm_dropdown_field li\'));
    foreach ($items as $dropdownItem) {
        if ($dropdownItem->getText() == \'Cranberry\') {
            //Select the option
            $dropdownItem->click();
            //assert that the option is selected
            $classString = $dropdownItem->getAttribute(\'class\');
            \PHPUnit\Framework\Assert::assertContains(\'selected\', $classString);
            break;
        }
    }
});';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addSelectFieldBlock($optionGroups) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('select', array(
            'displayName' => 'Select Field',
            'optionGroups' => $optionGroups
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addDropdownFieldWithDisabledOptionBlock($options) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('dropdown', array(
            'fieldName' => 'dropdown_with_disabled',
            'displayName' => 'Dropdown Field (disabled option)',
            'options' => $options,
            'disabledOptions' => array(
                'banana'
            )
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addSelectFieldWithDisabledGroupBlock($optionGroups) {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('select', array(
            'fieldName' => 'select_with_disabled',
            'displayName' => 'Select Field (disabled group)',
            'optionGroups' => $optionGroups,
            'disabledOptionGroups' => array(
                'Vegetables'
            )
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addAutocompleteFieldBlock() {
        $autocompURL = GI_URLUtils::buildURL(array(
                    'controller' => 'autocomplete',
                    'action' => 'permission',
                    'ajax' => 1
        ));
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('autocomplete', array(
            'displayName' => 'Autocomplete Field',
            'placeHolder' => 'Start typing permission name...',
            'autocompURL' => $autocompURL
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#autocomplete_field_autocomp\');
$I->fillField(\'autocomplete_field_autocomp\', \'View Users\');
$I->waitForElement(\'#acresults_autocomplete_field li\');
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    $items = $webdriver->findElements(WebDriverBy::cssSelector(\'#acresults_autocomplete_field li\'));
    //select the item
    foreach ($items as $autoCompItem) {
        if ($autoCompItem->getText() == \'View Users\') {
            $autoCompItem->click();
                break;
        }
    }
    //confirm that an item has been selected
    $autocompField = $webdriver->findElement(WebDriverBy::id(\'field_autocomplete_field\'));
    \PHPUnit\Framework\Assert::assertNotEmpty($autocompField);
    if (!empty($autocompField)) {
        $value = $autocompField->getAttribute(\'value\');
        \PHPUnit\Framework\Assert::assertNotEmpty($value);
    }
});';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addOnOffFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('onoff', array(
            'displayName' => 'On/Off Field',
            'onoffStyleAsCheckbox' => true
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_onoff_field\');
$I->click(\'#felm_onoff_field .checkbox_box\');
$I->canSeeCheckboxIsChecked(\'#field_onoff_field\');';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addTextAreaFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('textarea', array(
            'displayName' => 'Textarea Field'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_textarea_field\');
$sampleText = \'This is an example paragraph of text.\';
$I->fillField(\'textarea_field\', $sampleText);
$I->seeInField(\'#field_textarea_field\', $sampleText);';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addWYSIWYGFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('wysiwyg', array(
            'displayName' => 'WYSIWYG Field',
            'wygBtnHTML' => true,
            'wygBtnUndo' => true,
            'wygBtnFormat' => true,
            'wygBtnBold' => true,
            'wygBtnItalic' => true,
            'wygBtnUnderline' => true,
            'wygBtnStrike' => true,
            'wygBtnSuperscript' => false,
            'wygBtnSubscript' => false,
            'wygBtnLink' => true,
            'wygBtnJustify' => false,
            'wygBtnLists' => true,
            'wygBtnRule' => false,
            'wygBtnCode' => false,
            'wygBtnTable' => false,
            'wygBtnUnformat' => true,
            'wygBtnFullscreen' => true
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addTagFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('tag', array(
            'displayName' => 'Tag Field'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_tag_field\');
$I->click(\'#felm_tag_field .ui-widget-content\');
$I->pressKey(\'#felm_tag_field .tagit-new .ui-autocomplete-input\', array(
\'T\',\'E\',\'S\',\'T\',\'1\',\'2\',\'3\',WebDriverKeys::TAB
));        
$I->see(\'TEST123\');';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addColourFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('colour', array(
            'displayName' => 'Colour Field'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#felm_colour_field\');
$I->click(\'#field_colour_field\');
$colourHexBefore = $I->grabAttributeFrom(\'#field_colour_field\', \'title\');
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    $colourWheelCursors = $webdriver->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector(\'.jQWCP-wWheelCursor\'));
    \PHPUnit\Framework\Assert::assertNotEmpty($colourWheelCursors);
    $colourWheelCursor = $colourWheelCursors[0];
    $action = new \Facebook\WebDriver\Interactions\WebDriverActions($webdriver);
    $action->moveToElement($colourWheelCursor, 5, 5)
        ->clickAndHold($colourWheelCursor)
        ->moveByOffset(20, 50)
        ->release($colourWheelCursor)
        ->perform();
});
$I->clickWithLeftButton(null, 300, 0); //Click to the right of the field to make the overlay close
$I->moveMouseOver(\'#field_colour_field\'); //Make the widget update the DOM element with the new colour
$colourHexAfter = $I->grabAttributeFrom(\'#field_colour_field\', \'title\');
\PHPUnit\Framework\Assert::assertNotEquals($colourHexAfter, $colourHexBefore);';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addSignatureFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addSignatureBlock();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addToggleThisFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addRadioTogglerBlock();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addToggleThatFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addDropdownTogglerBlock();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addToggleOneOfTheseFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addCheckboxTogglerBlock();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addApproxFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('onoff', array(
            'displayName' => 'Approx. Field',
            'formElementClass' => 'equals_or_tilde',
            'fieldName' => 'equals_or_tilde'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addHiddenFieldBlock() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('onoff', array(
            'displayName' => 'Hidden Field',
            'formElementClass' => 'hidden_or_visible',
            'fieldName' => 'hidden_or_visible'
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addReCaptchaFieldBlock(){
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addFieldAndCodeBlock('recaptcha', array());
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = 'Not Available';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->form->addHTML($html);
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addAjaxLoadedSignatureBlock() {
        $this->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $signatureURL = GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => 'signHere'
        ));
        $loadInDiv = '<div id="load_sign_form_here"><p>This is where the form will load.</p></div>';
        $loadInBtn = '<a href="' . $signatureURL . '" class="other_btn load_in_element" title="Sign Here" data-load-in-id="load_sign_form_here" data-hide-btn="1">Sign Here</a>';
        $loadInHTML = $loadInDiv . "\n\n" . $loadInBtn;
        $this->addHTML('<div class="html_and_code_block">');
        $this->addHTML($loadInDiv);
        $this->addHTML('<br/>');
        $this->addHTML($loadInBtn);
        $this->addHTML('<pre class="brush: php; html-script: true; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($loadInHTML))
                ->addHTML('</pre>');
        $this->addHTML('</div>');
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '$I->scrollTo(\'#load_sign_form_here\');
$I->click(\'Sign Here\'); //Located w/ the \'Title\' attribute
$I->waitForElementChange(\'#load_sign_form_here\', function(\Facebook\WebDriver\Remote\RemoteWebElement $el) {
    $className = $el->getAttribute(\'class\');
    if (strpos($className, \'ajaxed_contents loaded\') !== false) {
        return true;
    }
    return false;
}, 30);
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
    $element = $webdriver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector(\'#load_sign_form_here .jsignature_signarea\'));
    $action = new \Facebook\WebDriver\Interactions\WebDriverActions($webdriver);
    $action->moveToElement($element, 5,5)
        ->clickAndHold($element)
        ->moveByOffset(20, 50)
        ->moveByOffset(50, 50)
        ->release($element)
        ->perform();
});';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->addHTML($html);
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addAjaxLoadedContentBlock() {
        $this->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $loadedContentURL = GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => 'loadedContent'
        ));
        $autoLoadInDiv = '<div class="ajaxed_contents auto_load" data-url="' . $loadedContentURL . '"><p>This is where the content will load.</p></div>';
        $this->addHTML('<div class="html_and_code_block">');
        $this->addHTML($autoLoadInDiv);
        $this->addHTML('<pre class="brush: php; html-script: true; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($autoLoadInDiv))
                ->addHTML('</pre>');
        $this->addHTML('</div>');
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addHTML('<h5>Codeception Acceptance Test Code</h5>');
        $testCode = '';
        $html = '<pre class="brush: php; class-name: \'wrap_lines\';" >'
                . htmlentities($testCode)
                . '</pre>';
        $this->addHTML($html);
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

}
