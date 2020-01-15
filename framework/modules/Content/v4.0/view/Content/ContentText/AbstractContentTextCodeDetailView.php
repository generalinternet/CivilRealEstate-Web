<?php
/**
 * Description of AbstractContentTextCodeDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentTextCodeDetailView extends AbstractContentTextDetailView {
    
    protected function buildViewGuts() {
        $this->addHTML('<div class="clear"></div>');
        $code = $this->content->getProperty('content_text.content');
        $codeLang = $this->content->getProperty('content_text_code.language');
        $startingLine = $this->content->getProperty('content_text_code.starting_line');
        $highlightLines = $this->content->getHighlightLines();
        $this->addHTML('<pre class="brush: ' . $codeLang . '; first-line: ' . $startingLine . '; gutter: true; highlight: [' . implode(', ', $highlightLines) . ']">');
        $this->addHTML(htmlspecialchars($code));
        $this->addHTML('</pre>');
    }
    
}
