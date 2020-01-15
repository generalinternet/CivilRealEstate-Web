<?php

class StaticColoursView extends GI_View {
    
    protected $usedColours = array();
    protected $colourErrors = array();
    protected $r = array(0, 255);
    protected $g = array(0, 255);
    protected $b = array(0, 255);
    
    public function __construct() {
        $this->addSiteTitle('Colours');
        parent::__construct();
        $this->addFinalContent('<script>
syntaxhighlighterConfig.gutter = false;
            </script>');
    }
    
    public function buildView(){
        $this->addHTML('<div class="view_wrap">');
            $this->addHTML('<div class="view_body">');

                $this->addHTML('<h3>Unsorted</h3>');
                $this->addHTML('<p>Generate random colour.</p>');
                $this->addHTML('<pre class="brush: php;" >')
                        ->addHTML(htmlentities('GI_Colour::getRandomHexColour($rMin = 0, $rMax = 255, $gMin = 0, $gMax = 255, $bMin = 0, $bMax = 255);'))
                        ->addHTML('</pre>');

                if(isset($_SESSION['rand_colours']) && !empty($_SESSION['rand_colours'])){
                    $this->usedColours = $_SESSION['rand_colours'];
                } else {
                    for($i = 0; $i < 1000; $i++){
                        $this->getUniqueRandomColour();
                    }

                    $_SESSION['rand_colours'] = $this->usedColours;
                }

                foreach($this->usedColours as $colour){
                    $this->addColourSwatch($colour);
                }

                $this->addHTML('<hr/>');
                $this->addHTML('<h3>Sorted By HSV</h3>');
                $this->addHTML('<p>Sort array of hex colours by HSV.</p>');
                $this->addHTML('<pre class="brush: php" >')
                        ->addHTML(htmlentities('GI_Colour::sortColoursByHSV(&$hexColours = array());'))
                        ->addHTML('</pre>');

                GI_Colour::sortColoursByHSV($this->usedColours);
                foreach($this->usedColours as $colour){
                    $this->addColourSwatch($colour);
                }

                $this->addHTML('<hr/>');
                $this->addHTML('<h3>Sorted By Luminosity</h3>');
                $this->addHTML('<p>Sort array of hex colours by Luminosity.</p>');
                $this->addHTML('<pre class="brush: php" >')
                        ->addHTML(htmlentities('GI_Colour::sortColoursByLuminosity(&$hexColours = array(), $darkToLight = true);'))
                        ->addHTML('</pre>');

                GI_Colour::sortColoursByLuminosity($this->usedColours);
                foreach($this->usedColours as $colour){
                    $this->addColourSwatch($colour);
                }

                $this->addHTML('<hr/>');
                $this->addHTML('<h3>Step Sort</h3>');
                $this->addHTML('<p>Sort array of hex colours in steps.</p>');
                $this->addHTML('<pre class="brush: php" >')
                        ->addHTML(htmlentities('GI_Colour::sortColoursByStep(&$hexColours = array(), $steps = 8, $darkToLight = true, $blended = false);'))
                        ->addHTML('</pre>');

                GI_Colour::sortColoursByStep($this->usedColours);
                foreach($this->usedColours as $colour){
                    $this->addColourSwatch($colour);
                }

                $this->addHTML('<hr/>');
                $this->addHTML('<h3>Step Sort (Blended)</h3>');
                $this->addHTML('<p>Sort array of hex colours in steps and blend together.</p>');
                $this->addHTML('<pre class="brush: php" >')
                        ->addHTML(htmlentities('GI_Colour::sortColoursByStep(&$hexColours = array(), $steps = 8, $darkToLight = true, $blended = true);'))
                        ->addHTML('</pre>');

                GI_Colour::sortColoursByStep($this->usedColours, 8, true, true);
                foreach($this->usedColours as $colour){
                    $this->addColourSwatch($colour);
                }

                $this->addHTML('<hr/>');
                $this->addHTML('<h3>Detect Font Colour To Use</h3>');
                $this->addHTML('<p>Determine whether to use a light or dark font overtop of a hex colour.</p>');
                $this->addHTML('<pre class="brush: php" >')
                        ->addHTML(htmlentities('GI_Colour::useLightFont($hexColour = string);'))
                        ->addHTML('</pre>');

                GI_Colour::sortColoursByStep($this->usedColours, 8, true, true);
                foreach($this->usedColours as $colour){
                    $this->addColourSwatchWithLetter($colour);
                }
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function getRandomHexColour(){
        return GI_Colour::getRandomHexColour($this->r[0], $this->r[1], $this->g[0], $this->g[1], $this->b[0], $this->b[1]);
    }
    
    protected function getUniqueRandomColour(){
        $colour = $this->getRandomHexColour();
        while(isset($this->usedColours[$colour])){
            $this->addColourError($colour);
            if($this->colourErrors[$colour] > 10){
                return $colour;
            }
            $colour = $this->getRandomHexColour();
        }
        
        $this->usedColours[$colour] = $colour;
        return $colour;
    }
    
    protected function addColourError($colour){
        if(!isset($this->colourErrors[$colour])){
            $this->colourErrors[$colour] = 0;
        }
        $this->colourErrors[$colour]++;
    }
    
    public function addColourSwatch($colour){
        $this->addHTML('<span class="inline_block" style="background: #' . $colour . '; width: 0.2em; height: 2em;"></span>');
    }
    
    public function addColourSwatchWithLetter($colour){
        $fontColour = '000';
        if(GI_Colour::useLightFont($colour)){
            $fontColour = 'fff';
        }
        $letter = 'A';
        $this->addHTML('<span class="inline_block" style="background: #' . $colour . '; width: 3em; height: 3em;"><span style="color: #' . $fontColour . '; font-weight: bold; font-size: 1.5em; line-height: 2em; text-align: center; display: inline-block; width: 100%;" >' . $letter . '</span></span>');
    }
    
}
