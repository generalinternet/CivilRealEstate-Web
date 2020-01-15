<?php
/**
 * Description of AbstractDashboardChartWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardChartWidgetView extends AbstractDashboardWidgetView {
    
    protected $colours = array();

    public function __construct($ref) {
        parent::__construct($ref);
        $this->addCSS('resources/external/js/morris/morris.css');
        $this->addJS('resources/external/js/raphael.min.js');
        $this->addJS('resources/external/js/morris/morris.min.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/custom_morris.js');
    }
    
    /**
     * @param String[] $colours - an array of 6-digit hexadecimal values
     */
    public function setColours($colours) {
        $this->colours = $colours;
    }
    
    public function getColours() {
        return $this->colours;
    }
    
    protected function getColoursString() {
        if (empty($this->colours)) {
            return '"#3f95ff","#347ad1","#4c8ee0"';;
        }
        $tempArray = array();
        foreach ($this->colours as $colour) {
            $tempArray[] = '"#' . $colour . '"';
        }
        $string = implode(',', $tempArray);
        return $string;
    }

}
