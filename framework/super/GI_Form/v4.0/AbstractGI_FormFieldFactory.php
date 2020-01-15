<?php
/**
 * Description of AbstractGI_FormFieldFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractGI_FormFieldFactory {
    
    public static function buildField($fieldType){
        switch($fieldType){
            case 'tag':
                break;
            case 'autocomplete':
                break;
            case 'alarm':
            case 'time':
                break;
            case 'date':
            case 'event':
                break;
            case 'datetime':
            case 'reminder':
                break;
            case 'checkbox':
            case 'radio':
                break;
            case 'decimal':
                break;
            case 'dropdown':
            case 'select':
                break;
            case 'email':
                break;
            case 'hidden':
                break;
            case 'id':
            case 'integer_pos':
                break;
            case 'integer':
            case 'integer_large':
                break;
            case 'money':
                break;
            case 'onoff':
                break;
            case 'password':
                break;
            case 'percentage':
                break;
            case 'phone':
                break;
            case 'text':
            default:
                break;
            case 'textarea':
                break;
            case 'wysiwyg':
                break;
            case 'url':
                break;
            case 'file':
                break;
            case 'signature':
                break;
            case 'colour':
            case 'color':
                break;
        }
    }
    
}
