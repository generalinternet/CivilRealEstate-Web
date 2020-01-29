<?php
/**
 * Description of AbstractGenericEmailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractGenericEmailView {
    
    protected $html = '';
    protected $title = '';
    protected $fontFamily = '\'Open Sans\', Arial, Helvetica, sans-serif';
    protected $textAlign = 'left';
    protected $colour = '#808080';
    protected $linkColour = '#7899b0';
    protected $linkWeight = 'bold';
    protected $pColour = NULL;
    protected $tableColour = NULL;
    protected $fontSize = '14px';
    protected $pFontSize = NULL;
    protected $tableFontSize = NULL;
    protected $bgColour = '#ebebeb';
    protected $mainBGColour = '#fff';
    protected $blockBGColour = '#ebebeb';
    protected $blockColour = '#5e5e5e';
    protected $mainPadding = '20px 30px';
    
    public function addHTML($html){
        $this->html .= $html;
        return $this;
    }
    
    public function setTitle($title){
        $this->title = $title;
        return $this;
    }
    
    public function getTitle(){
        return $this->title;
    }
    
    public function setFontFamily($fontFamily){
        $this->fontFamily = $fontFamily;
        return $this;
    }
    
    public function setTextAlign($textAlign){
        $this->textAlign = $textAlign;
        return $this;
    }
    
    public function setColour($colour){
        $this->colour = $colour;
        return $this;
    }
    
    public function setLinkColour($linkColour){
        $this->linkColour = $linkColour;
        return $this;
    }
    
    public function setPColour($pColour){
        $this->pColour = $pColour;
        return $this;
    }
    
    public function setTableColour($tableColour){
        $this->tableColour = $tableColour;
        return $this;
    }
    
    public function setLinkWeight($linkWeight){
        $this->linkWeight = $linkWeight;
        return $this;
    }
    
    public function setBlockColour($blockColour){
        $this->blockColour = $blockColour;
        return $this;
    }
    
    public function setBlockBGColour($blockBGColour){
        $this->blockBGColour = $blockBGColour;
        return $this;
    }

    protected function addHeader($title = '') {
        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $html .= '<meta http-equiv="X-UA-Compatible" content="IEedge">';
        $html .= '<title>' . $this->getTitle() . '</title>';
        $html .= '<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800,300i,400i,600i,700i,800i" rel="stylesheet">';
        $html .= '<style>';
        $html .= 'a{ color: ' . $this->linkColour . ';';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body style="background-color: #ebebeb; font-family: ' . $this->fontFamily . ';  font-size: 13px; line-height: 1.5; color: #3e3935; margin: 0; padding: 0;" bgcolor="#ebebeb">';
        $html .= '<table width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse;">';
        $html .= '<tr>';
        $html .= '<td align="center" bgcolor="' . $this->bgColour . '" style="font-family: ' . $this->fontFamily . '; border: 0; padding: 10px 5px;">';
        $html .= '<table width="500" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse; min-width: 500px; width: 500px;">';
        
        $html .= $this->getEmailHeader();
        
        $html .= '<tr>';
        $html .= '<td bgcolor="' . $this->mainBGColour . '" style="font-family: ' . $this->fontFamily . '; text-align: ' . $this->textAlign . '; background-color: ' . $this->mainBGColour . '; padding: ' . $this->mainPadding . '; border: 0;" align="' . $this->textAlign . '">';
        $this->addHTML($html);
        return $this;
    }
    
    protected function getEmailHeader(){
        $header = '<tr>';
        $header .= '<th bgcolor="' . $this->mainBGColour . '" style="font-family: ' . $this->fontFamily . '; text-align: ' . $this->textAlign . '; background-color: ' . $this->mainBGColour . '; padding: ' . $this->mainPadding . '; border-bottom: 1px solid ' . $this->bgColour . '; font-size: 18px; color: ' . $this->colour . ';" align="' . $this->textAlign . '">';
        $header .= EMAIL_TITLE . ' ';
        $header .= '</th>';
        $header .= '<tr>';
        return $header;
    }
    
    protected function addFooter() {
        $html = '</td>';
        $html .= '</tr>';
        $html .= $this->getEmailFooter();
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</body>';
        $html .= '</html>';
        $this->addHTML($html);
    }
    
    protected function getEmailFooter(){
        $footer = '<tr>';
        $footer .= '<td bgcolor="' . $this->bgColour . '" style="font-family: ' . $this->fontFamily . '; text-align: center; background-color: ' . $this->bgColour . '; padding: ' . $this->mainPadding . '; font-size: ' . $this->fontSize . '; color: ' . $this->colour . '; font-size: 10px;" align="center">';
        $date = new DateTime();
        $footer .= '&copy; ' . $date->format('Y') . ' ' . EMAIL_COMPANY;
        $footer .= '</td>';
        $footer .= '<tr>';
        return $footer;
    }
    
    public function beforeReturningView(){
        $html = $this->html;
        $this->html = '';
        $this->addHeader();
        $this->addHTML($html);
        $this->addFooter();
    }
    
    public function getHTMLView() {
        $this->beforeReturningView();
        return $this->html;
    }
    
    public function getStyleString($styleFor = NULL, $customColour = NULL){
        $styleString = 'style="';
        $styleString .= 'font-family: ' . $this->fontFamily . ';';
        $textAlign = $this->textAlign;
        switch($styleFor){
            case 'table':
                $colour = $this->tableColour;
                $fontSize = $this->tableFontSize;
                $styleString .= 'border-collapse: collapse;';
                $styleString .= 'margin-bottom: 20px;';
                break;
            case 'th':
            case 'td':
                $colour = $this->tableColour;
                $fontSize = $this->tableFontSize;
                break;
            case 'p':
                $colour = $this->pColour;
                $fontSize = $this->pFontSize;
                break;
            case 'a':
                $colour = $this->linkColour;
                $fontSize = $this->pFontSize;
                $styleString .= 'font-weight: ' . $this->linkWeight . ';';
                break;
            case 'button':
                $colour = $this->mainBGColour;
                $fontSize = $this->pFontSize;
                $textAlign = 'center';
                $styleString .= 'display: inline-block;';
                $styleString .= 'background-color: ' . $this->linkColour . ';';
                $styleString .= 'font-weight: ' . $this->linkWeight . ';';
                $styleString .= 'text-decoration: none;';
                $styleString .= 'padding: 5px 10px;';
                $styleString .= 'border-radius: 2px;';
                break;
            case 'block':
                $colour = $this->blockColour;
                $fontSize = $this->pFontSize;
                $styleString .= 'background-color: ' . $this->blockBGColour . ';';
                $styleString .= 'padding: 10px 20px;';
                break;
            default:
                $colour = $this->colour;
                $fontSize = $this->fontSize;
                break;
        }
        
        if(!empty($customColour)){
            $colour = $customColour;
        }
        
        if(empty($colour)){
            $colour = $this->colour;
        }
        
        $styleString .= 'color: ' . $colour . ';';
        
        if(empty($fontSize)){
            $fontSize = $this->fontSize;
        }
        
        $styleString .= 'text-align: ' . $textAlign . ';';
        $styleString .= 'font-size: ' . $fontSize . ';';
        
        $styleString .= '"';
        return $styleString;
    }
    
    public function startParagraph(){
        $this->addHTML('<p ' . $this->getStyleString('p') . '>');
        return $this;
    }
    
    public function closeParagraph(){
        $this->addHTML('</p>');
        return $this;
    }
    
    public function startTable($width = '100%'){
        $this->addHTML('<table width="' . $width . '" cellspacing="0" cellpadding="0" border="0" ' . $this->getStyleString('table') . '>');
        return $this;
    }
    
    public function closeTable(){
        $this->addHTML('</table>');
        return $this;
    }
    
    public function startRow(){
        $this->addHTML('<tr>');
        return $this;
    }
    
    public function closeRow(){
        $this->addHTML('</tr>');
        return $this;
    }
    
    public function startTH(){
        $this->addHTML('<th ' . $this->getStyleString('th') . '>');
        return $this;
    }
    
    public function closeTH(){
        $this->addHTML('</th>');
        return $this;
    }
    
    public function startTD(){
        $this->addHTML('<td ' . $this->getStyleString('td') . '>');
        return $this;
    }
    
    public function closeTD(){
        $this->addHTML('</td>');
        return $this;
    }
    
    public function addHeaderCell($headerHTML){
        $this->startTH()
                ->addHTML($headerHTML)
                ->closeTH();
        return $this;
    }
    
    public function addCell($cellHTML){
        $this->startTD()
                ->addHTML($cellHTML)
                ->closeTD();
        return $this;
    }
    
    public function addParagraph($pHTML){
        $this->startParagraph()
                ->addHTML($pHTML)
                ->closeParagraph();
        return $this;
    }
    
    public function startLink($link, $trackLink = true){
        $this->addHTML('<a href="' . $link . '" target="_blank" ' . $this->getStyleString('a'));
        if(!$trackLink){
            $this->addHTML(' mc:disable-tracking');
        }
        $this->addHTML(' >');
        return $this;
    }
    
    public function closeLink(){
        $this->addHTML('</a>');
        return $this;
    }
    
    public function addLink($label, $link, $trackLink = true){
        $this->startLink($link, $trackLink)
                ->addHTML($label)
                ->closeLink();
        return $this;
    }
    
    public function addLineBreak(){
        $this->addHTML('<hr style="border: 0; margin: 20px 0; border-bottom: 1px solid ' . $this->bgColour . ';" />');
        return $this;
    }
    
    public function startButton($link, $trackLink = true){
        $this->addHTML('<a href="' . $link . '" target="_blank" ' . $this->getStyleString('button'));
        if(!$trackLink){
            $this->addHTML(' mc:disable-tracking');
        }
        $this->addHTML(' >');
        return $this;
    }
    
    public function closeButton(){
        $this->addHTML('</a>');
        return $this;
    }
    
    public function addButton($label, $link, $trackLink = true){
        $this->startButton($link, $trackLink)
                ->addHTML($label)
                ->closeButton();
        return $this;
    }
    
    public function startBlock(){
        $this->rememberPColour = $this->pColour;
        $this->pColour = $this->blockColour;
        $this->startTable()
                ->startRow()
                ->addHTML('<td ' . $this->getStyleString('block') . '>');
        return $this;
    }
    
    public function closeBlock(){
        $this->pColour = $this->rememberPColour;
        $this->rememberPColour = NULL;
        $this->addHTML('</td>')
                ->closeRow()
                ->closeTable();
        return $this;
    }
    
    //<table width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse;">
}
