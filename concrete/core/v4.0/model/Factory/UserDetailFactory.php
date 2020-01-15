<?php

class UserDetailFactory extends AbstractUserDetailFactory {
    public static $INVESTOR_TYPE_ACCREDITED = 'accredited';
    public static $INVESTOR_TYPE_NONACCREDITED = 'non-accredited';

    public static $OPITIONS_INVESTOR_TYPE = array(
        'accredited' => 'Accredited',
        'non-accredited' => 'Non-Accredited',
        'not-sure' => 'Not Sure',
    );
    
    public static $OPITIONS_INVESTOR_TYPE_RADIO = array(
        'accredited' => 'YES',
        'non-accredited' => 'NO',
        'not-sure' => 'NOT SURE',
    );
    
    public static $OPITIONS_AMOUNT = array(
        //'-25K' => 'Less then 25K',
        '25-50K' => '25-50K',
        '50-100K' => '50-100K',
        '100-250K' => '100-250K',
        '250-500K' => '250-500K',
        '500K+' => '500K+',
        //'1M+' => '1M+',
    );
    
    public static $OPITIONS_RISKLEVEL = array(
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    );
    
    public static $OPITIONS_TERMS = array(
        'short' => 'Short Term (0-12 Months)',
        'mid' => 'Mid Term (1-5 Years)',
        'long' => 'Long Term (5+ Years)',
    );
    
    public static $OPITIONS_REGION_CODE = array(
        'british_columbia' => 'BRITISH COLUMBIA',
        'alberta' => 'ALBERTA',
        'rest_of_canada' => 'REST OF CANADA',
        'usa' => 'U.S.A',
        'other' => 'OTHER',
    );
    
    public static $OPITIONS_GENDER = array(
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
        //'undefined' => 'Prefer not to say',
    );
    
    public static $OPITIONS_MARITAL_STATUS = array(
        'married' => 'Married',
        'single' => 'Single',
        //'undefined' => 'Prefer not to say',
    );
    
    public static $OPITIONS_CITIZENSHIP = array(
        'canadian' => 'Canadian',
        'us' => 'US Citizen',
        'pr' => 'Permanent Residency',
        'none' => 'Non-Citizen',
    );
    
    public static $OPITIONS_EDUCATION_LEVEL = array(
        'high_scholl' => 'High School',
        'diploma' => 'Diploma',
        'bachelor' => 'Bachelor',
        'masters' => 'Masters',
        'phd' => 'PhD',
    );
    
    public static $OPITIONS_EXPERIENCE = array(
        'low' => 'Low',
        'low_moderate' => 'Low-moderate',
        'moderate' => 'Moderate',
        'moderate_high' => 'Moderate-high',
        'high' => 'High',
    );
    
    public static $OPITIONS_OBJECTIVE = array(
        '1-5' => '1-5%',
        '5-10' => '5-10%',
        '10-20' => '10-20%',
        '20-50' => '20-50%',
        '50-100' => '50-100%',
    );
    
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'we':
                $model = new UserDetailWe($map);
                break;
            default:
                return parent::buildModelByTypeRef($typeRef, $map);
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'we':
                $typeRefs = array('we');
                break;
            default:
                return parent::getTypeRefArrayFromTypeRef($typeRef);
        }
        return $typeRefs;
    }
}