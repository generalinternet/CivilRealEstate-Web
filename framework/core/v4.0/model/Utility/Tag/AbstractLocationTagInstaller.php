<?php
/**
 * Description of AbstractLocationTagInstaller
 * 
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractLocationTagInstaller extends AbstractTagInstaller {

    protected static $tagType = 'location';
    protected static $tagDefinitions = array(
        array(
            'title' => 'Worldwide',
            'ref' => 'worldwide',
            'colour' => 'ff00ff',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
            ),
        ),
        array(
            'title' => 'Canada',
            'ref' => 'canada',
            'colour' => 'ff00ff',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
                'north_america',
            ),
        ),
        array(
            'title' => 'USA',
            'ref' => 'usa',
            'colour' => 'ff00ff',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
                'north_america',
            ),
        ),
        array(
            'title' => 'Mexico',
            'ref' => 'mexico',
            'colour' => 'ff00ff',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
                'north_america',
            ),
        ),
        array(
            'title' => 'North America',
            'ref'=>'north_america',
            'colour'=>'ff00ff',
            'system'=>0,
            'pos'=>0,
            'parent_tag_refs'=>array(
                'worldwide'
            ),
        ),
        
    );
    

    

}
