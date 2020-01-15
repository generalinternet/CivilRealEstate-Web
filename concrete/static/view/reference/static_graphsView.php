<?php

class StaticGraphsView extends GI_View {
    
    public function __construct() {
        $this->addSiteTitle('Graphs');
        parent::__construct();
        $this->addFinalContent('<script>
syntaxhighlighterConfig.gutter = false;
</script>');
        $this->addCSS('resources/external/js/morris/morris.css');
        $this->addJS('resources/external/js/morris/morris.min.js');
        $this->addJS('resources/external/js/raphael.min.js');
    }
    
    public function addHTMLAndCodeBlock($html, $title = NULL){
        $this->addHTML('<div class="html_and_code_block">');
        $this->addHTML($html);
        if(!empty($title)){
            $this->addHTML('<h5>' . $title . '</h5>');
        }
        $this->addHTML('<pre class="brush: php; html-script: true; class-name: \'wrap_lines\';" >')
                ->addHTML(htmlentities($html))
                ->addHTML('</pre>');
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView(){
        $this->openViewWrap();
        $this->addHTML('<div class="view_header">');
        $this->addMainTitle('Graphs');
        $this->addHTML('</div>');
        $this->addHTML('<div class="view_body">');

            $this->addHTML('<div class="columns havles">');
            $this->addHTML('<div class="column">');
            $this->addDonutGraph();
            $this->addHTML('</div>');
            $this->addHTML('<div class="column">');
                $dynamicGraphURL = GI_URLUtils::buildURL(array(
                    'controller' => 'test',
                    'action' => 'getGraphData',
                    'type' => 'donut'
                ));
                $this->addHTML('<div class="auto_load ajaxed_contents" data-url="' . $dynamicGraphURL . '">Missing Graph.</div>');
            $this->addHTML('</div>');
            $this->addHTML('</div>');
//        $this->addBarGraph();      
            $this->addHTML('</div>');
        $this->closeViewWrap();
    }
    
    protected function addDonutGraph(){
        $this->addHTMLAndCodeBlock('<div id="donut_graph"></div>', 'Graph Wrap');
        $this->addDynamicJS('new Morris.Donut({
            element: "donut_graph",
            data: [
                {value: 100, label: "Apples" },
                {value: 200, label: "Bananas" },
                {value: 300, label: "Cranberry" }
            ],
            colors: ["#3f95ff","#347ad1","#4c8ee0"],
            resize: true,
            formatter: function(x, data){
                return "$"+numberWithCommas(data.value.toFixed(2));
            }
        });');
        $graphPHP = '$this->addDynamicJS(\'new Morris.Donut({
    element: "donut_graph",
    data: [
        {value: 100, label: "Apples" },
        {value: 200, label: "Bananas" },
        {value: 300, label: "Cranberry" }
    ],
    colors: ["#3f95ff","#347ad1","#4c8ee0"],
    resize: true,
    formatter: function(x, data){
        return "$"+numberWithCommas(data.value.toFixed(2));
    }
});\');
';
        $this->addHTML('<div class="html_and_code_block">');
        $this->addHTML('<h5>Graph JS</h5>');
        $this->addHTML('<pre class="brush: php; class-name: \'wrap_lines\';" >')
        ->addHTML(htmlentities($graphPHP))
        ->addHTML('</pre>');
        $this->addHTML('</div>');
    }
    
    protected function addBarGraph(){
        $this->addHTMLAndCodeBlock('<div id="bar_graph"></div>', 'Graph Wrap');
        $this->addDynamicJS('new Morris.Bar({
            element: "bar_graph",
            data: [
                { y: "2014", a: 50, b: 90},
                { y: "2015", a: 65,  b: 75},
                { y: "2016", a: 50,  b: 50},
            ],
            colors: ["#3f95ff","#347ad1","#4c8ee0"],
            resize: true,
            formatter: function(x, data){
                return "$"+numberWithCommas(data.value.toFixed(2));
            },
            xkey: "y",
            ykeys: ["a", "b"],
            labels: ["A", "B"]
        });');
        $graphPHP = '$this->addDynamicJS(\'new Morris.Bar({
    element: "bar_graph",
    data: [
        {value: 100, label: "Apples" },
        {value: 200, label: "Bananas" },
        {value: 300, label: "Cranberry" }
    ],
    colors: ["#3f95ff","#347ad1","#4c8ee0"],
    resize: true,
    formatter: function(x, data){
        return "$"+numberWithCommas(data.value.toFixed(2));
    }
});\');
';
        $this->addHTML('<div class="html_and_code_block">');
        $this->addHTML('<h5>Graph JS</h5>');
        $this->addHTML('<pre class="brush: php; class-name: \'wrap_lines\';" >')
        ->addHTML(htmlentities($graphPHP))
        ->addHTML('</pre>');
        $this->addHTML('</div>');
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="view_wrap">');
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