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
            'colour' => '3f96f2',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
            ),
        ),
        array(
            'title' => 'Europe',
            'ref' => 'europe',
            'colour' => '53bf44',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide'
            ),
        ),
        array(
            'title' => 'Asia',
            'ref' => 'asia',
            'colour' => '44bfb0',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide'
            ),
        ),
        array(
            'title' => 'Africa',
            'ref' => 'africa',
            'colour' => 'f29a3f',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide'
            ),
        ),
        array(
            'title' => 'North America',
            'ref' => 'north_america',
            'colour' => 'bf447c',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide'
            ),
        ),
        array(
            'title' => 'South America',
            'ref' => 'south_america',
            'colour' => 'f23f5b',
            'system' => 0,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide'
            ),
        ),
        array(
            'title' => 'European Union',
            'ref' => 'european_union',
            'colour' => 'b19d3e',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
                'europe'
            ),
        ),
        array(
            'title' => 'Caribbean',
            'ref' => 'caribbean',
            'colour' => 'e684bc',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
            ),
        ),
        array(
            'title' => 'Pacific Rim',
            'ref' => 'pacific_rim',
            'colour' => '7e026f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
            ),
        ),
        array(
            'title' => 'Baltic States',
            'ref' => 'baltic_states',
            'colour' => '0bf896',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
                'europe',
            ),
        ),
        array(
            'title' => 'Far East',
            'ref' => 'far_east',
            'colour' => 'e1c5f4',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
            ),
        ),
        array(
            'title' => 'Eastern Eurpope',
            'ref' => 'eastern_eurpope',
            'colour' => 'b24d73',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
                'europe'
            ),
        ),
        array(
            'title' => 'Western Europe',
            'ref' => 'western_europe',
            'colour' => 'a17634',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
                'europe'
            ),
        ),
        array(
            'title' => 'Central America',
            'ref' => 'central_america',
            'colour' => 'd01139',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
            ),
        ),
        array(
            'title' => 'Latin America',
            'ref' => 'latin_america',
            'colour' => '6b7fe7',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
            ),
        ),
        array(
            'title' => 'Oceania',
            'ref' => 'oceania',
            'colour' => 'f6284ba',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
            ),
        ),
        array(
            'title' => 'Middle East',
            'ref' => 'middle_east',
            'colour' => '07fbad',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
            ),
        ),
        array(
            'title' => 'Scandanavia',
            'ref' => 'scandanavia',
            'colour' => '7dfdad',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
                'europe',
            ),
        ),
        array(
            'title' => 'Asia-Pacific',
            'ref' => 'asia_pacific',
            'colour' => '00BFFF',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
            ),
        ),
        array(
            'title' => 'Afghanistan',
            'ref' => 'afghanistan',
            'colour' => 'd6a637',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Albania',
            'ref' => 'albania',
            'colour' => 'e0ce97',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
            ),
        ),
        array(
            'title' => 'Algeria',
            'ref' => 'algeria',
            'colour' => '7c9270',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa'
            ),
        ),
        array(
            'title' => 'Andorra',
            'ref' => 'andorra',
            'colour' => '880399',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe'
            ),
        ),
        array(
            'title' => 'Angola',
            'ref' => 'angola',
            'colour' => 'f33bb1',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa'
            ),
        ),
        array(
            'title' => 'Antigua and Barbuda',
            'ref' => 'antigua_and_barbuda',
            'colour' => '1f3a6f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
            ),
        ),
        array(
            'title' => 'Argentina',
            'ref' => 'argentina',
            'colour' => 'cf3e65',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Armenia',
            'ref' => 'armenia',
            'colour' => '1aa141',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
            ),
        ),
        array(
            'title' => 'Australia',
            'ref' => 'australia',
            'colour' => '7bcaad',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'worldwide',
                'pacific_rim',
                'oceania',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Austria',
            'ref' => 'austria',
            'colour' => '802f37',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
                'western_europe'
            ),
        ),
        array(
            'title' => 'Azerbaijan',
            'ref' => 'azerbaijan',
            'colour' => '50eaa4',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
            ),
        ),
        array(
            'title' => 'Bahamas',
            'ref' => 'bahamas',
            'colour' => '0f08c9',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
            ),
        ),
        array(
            'title' => 'Bahrain',
            'ref' => 'bahrain',
            'colour' => '92b0fd',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
               'middle_east'
            ),
        ),
        array(
            'title' => 'Bangladesh',
            'ref' => 'bangladesh',
            'colour' => '9f6acc',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Barbados',
            'ref' => 'barbados',
            'colour' => '8b17de',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
            ),
        ),
        array(
            'title' => 'Belarus',
            'ref' => 'belarus',
            'colour' => 'd08659',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'eastern_europe',
            ),
        ),
        array(
            'title' => 'Belgium',
            'ref' => 'belgium',
            'colour' => '1331d0',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
                'western_europe'
            ),
        ),
        array(
            'title' => 'Belize',
            'ref' => 'belize',
            'colour' => '7f1b1d',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'central_america'
            ),
        ),
        array(
            'title' => 'Benin',
            'ref' => 'benin',
            'colour' => '9dc8e9',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa'
            ),
        ),
        array(
            'title' => 'Bhutan',
            'ref' => 'bhutan',
            'colour' => 'a1cfba',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Bolivia',
            'ref' => 'bolivia',
            'colour' => '91696e',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Bosnia and Herzegovina',
            'ref' => 'bosnia_and_herzegovina',
            'colour' => '836220',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe'
            ),
        ),
        array(
            'title' => 'Botswana',
            'ref' => 'botswana',
            'colour' => '20138b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Brazil',
            'ref' => 'brazil',
            'colour' => 'ff82c9',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Brunei',
            'ref' => 'brunei',
            'colour' => '902ad8',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Bulgaria',
            'ref' => 'bulgaria',
            'colour' => 'fef380',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'eastern_europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Burkina Faso',
            'ref' => 'burkina_faso',
            'colour' => 'dc9f46',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Burundi',
            'ref' => 'burundi',
            'colour' => '59d5dc',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Cote d\'Ivoire',
            'ref' => 'cte_divoire',
            'colour' => '1f9ef0',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Cabo Verde (Cape Verde)',
            'ref' => 'cabo_verde',
            'colour' => '66fef2',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
            ),
        ),
        array(
            'title' => 'Cambodia',
            'ref' => 'cambodia',
            'colour' => '660ca3',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Cameroon',
            'ref' => 'cameroon',
            'colour' => 'd246ad',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Canada',
            'ref' => 'canada',
            'colour' => '6bf764',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'north_america',
            ),
        ),
        array(
            'title' => 'Central African Republic',
            'ref' => 'central_african_republic',
            'colour' => '06c4ac',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Chad',
            'ref' => 'chad',
            'colour' => 'e2b813',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Chile',
            'ref' => 'chile',
            'colour' => 'ebff2e',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'China',
            'ref' => 'china',
            'colour' => '2f3fb9',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Colombia',
            'ref' => 'colombia',
            'colour' => '32da3c',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Comoros',
            'ref' => 'comoros',
            'colour' => '6f8175',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Congo',
            'ref' => 'congo',
            'colour' => 'e7cc42',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Costa Rica',
            'ref' => 'costa_rica',
            'colour' => '227068',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'central_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Croatia',
            'ref' => 'croatia',
            'colour' => '54867f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Cuba',
            'ref' => 'cuba',
            'colour' => 'a41a00',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Cyprus',
            'ref' => 'cyprus',
            'colour' => 'fd8849',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Czechia (Czech Republic)',
            'ref' => 'czech_republic',
            'colour' => 'cff28a',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Democratic Republic of the Congo',
            'ref' => 'democratic_republic_of_the_congo',
            'colour' => '1f92eb',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Denmark',
            'ref' => 'denmark',
            'colour' => '80c905',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
                'european_union',
                'scandanavia',
            ),
        ),
        array(
            'title' => 'Djibouti',
            'ref' => 'djibouti',
            'colour' => '69f711',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Dominica',
            'ref' => 'dominica',
            'colour' => '6bd136',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
            ),
        ),
        array(
            'title' => 'Dominican Republic',
            'ref' => 'dominican_republic',
            'colour' => 'eca31e',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Ecuador',
            'ref' => 'ecuador',
            'colour' => '1a2f79',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Egypt',
            'ref' => 'egypt',
            'colour' => '18a19f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
                'middle_east',
            ),
        ),
        array(
            'title' => 'El Salvador',
            'ref' => 'el_salvador',
            'colour' => '6eda63',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'central_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Equatorial Guinea',
            'ref' => 'equatorial_guinea',
            'colour' => '69276f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Eritrea',
            'ref' => 'eritrea',
            'colour' => '2fb941',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Estonia',
            'ref' => 'estonia',
            'colour' => 'b2a834',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
                'baltic_states',
            ),
        ),
        array(
            'title' => 'Eswatini (Swaziland)',
            'ref' => 'eswatini_swaziland',
            'colour' => 'a3d932',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Ethiopia',
            'ref' => 'ethiopia',
            'colour' => '514811',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Fiji',
            'ref' => 'fiji',
            'colour' => '0f1b44',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Finland',
            'ref' => 'finland',
            'colour' => 'c70d82',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
                'scandanavia',
            ),
        ),
        array(
            'title' => 'France',
            'ref' => 'france',
            'colour' => '61422b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
                'european_union'
            ),
        ),
        array(
            'title' => 'Gabon',
            'ref' => 'gabon',
            'colour' => '971f44',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Gambia',
            'ref' => 'gambia',
            'colour' => '48c523',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Georgia',
            'ref' => 'georgia',
            'colour' => '3e2a44',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
            ),
        ),
        array(
            'title' => 'Germany',
            'ref' => 'germany',
            'colour' => '78896b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Ghana',
            'ref' => 'ghana',
            'colour' => 'b8cb98',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Greece',
            'ref' => 'greece',
            'colour' => '3ab91d',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union'
            ),
        ),
        array(
            'title' => 'Grenada',
            'ref' => 'grenada',
            'colour' => '232a79',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean'
            ),
        ),
        array(
            'title' => 'Guatemala',
            'ref' => 'guatemala',
            'colour' => '94c00b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'central_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Guinea',
            'ref' => 'guinea',
            'colour' => 'b6640f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Guinea-Bissau',
            'ref' => 'guinea-bissau',
            'colour' => 'eba6a6',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Guyana',
            'ref' => 'guyana',
            'colour' => 'b53582',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
            ),
        ),
        array(
            'title' => 'Haiti',
            'ref' => 'haiti',
            'colour' => '4e00e5',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Vatican City',
            'ref' => 'vatican_city',
            'colour' => '1243fb',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
            ),
        ),
        array(
            'title' => 'Honduras',
            'ref' => 'honduras',
            'colour' => 'c36eeb',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'central_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Hungary',
            'ref' => 'hungary',
            'colour' => 'babc88',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'eastern_europe',
                'european_union'
            ),
        ),
        array(
            'title' => 'Iceland',
            'ref' => 'iceland',
            'colour' => '15c1b7',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'scandanavia',
            ),
        ),
        array(
            'title' => 'India',
            'ref' => 'india',
            'colour' => '5a9b57',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Indonesia',
            'ref' => 'indonesia',
            'colour' => '6b8b09',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Iran',
            'ref' => 'iran',
            'colour' => 'b9d133',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'middle_east',
            ),
        ),
        array(
            'title' => 'Iraq',
            'ref' => 'iraq',
            'colour' => '0f1234',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'middle_east',
            ),
        ),
        array(
            'title' => 'Ireland',
            'ref' => 'ireland',
            'colour' => '6d9519',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
                'european_union'
            ),
        ),
        array(
            'title' => 'Israel',
            'ref' => 'israel',
            'colour' => 'be1770',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'middle_east',
            ),
        ),
        array(
            'title' => 'Italy',
            'ref' => 'italy',
            'colour' => '9ef111',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Jamaica',
            'ref' => 'jamaica',
            'colour' => '958ff7',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
            ),
        ),
        array(
            'title' => 'Japan',
            'ref' => 'japan',
            'colour' => 'b89319',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Jordan',
            'ref' => 'jordan',
            'colour' => 'bc3b96',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'middle_east',
            ),
        ),
        array(
            'title' => 'Kazakhstan',
            'ref' => 'kazakhstan',
            'colour' => 'c94ce7',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
            ),
        ),
        array(
            'title' => 'Kenya',
            'ref' => 'kenya',
            'colour' => 'c75d1a',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Kiribati',
            'ref' => 'kiribati',
            'colour' => '5fcafc',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Kuwait',
            'ref' => 'kuwait',
            'colour' => 'a21bf0',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'middle_east',
            ),
        ),
        array(
            'title' => 'Kyrgyzstan',
            'ref' => 'kyrgyzstan',
            'colour' => '0ff277',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
            ),
        ),
        array(
            'title' => 'Laos',
            'ref' => 'laos',
            'colour' => '118a5e',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Latvia',
            'ref' => 'latvia',
            'colour' => 'ffdc8b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
                'baltic_states',
            ),
        ),
        array(
            'title' => 'Lebanon',
            'ref' => 'lebanon',
            'colour' => '57e752',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'middle_east',
            ),
        ),
        array(
            'title' => 'Lesotho',
            'ref' => 'lesotho',
            'colour' => 'f224ac',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Liberia',
            'ref' => 'liberia',
            'colour' => '7f51fa',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Libya',
            'ref' => 'libya',
            'colour' => 'd87534',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Liechtenstein',
            'ref' => 'liechtenstein',
            'colour' => '3c189e',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe'
            ),
        ),
        array(
            'title' => 'Lithuania',
            'ref' => 'lithuania',
            'colour' => '6c910e',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
                'baltic_states',
            ),
        ),
        array(
            'title' => 'Luxembourg',
            'ref' => 'luxembourg',
            'colour' => '22a848',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
                'european_union'
            ),
        ),
        array(
            'title' => 'Madagascar',
            'ref' => 'madagascar',
            'colour' => '85a81d',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Malawi',
            'ref' => 'malawi',
            'colour' => '325b14',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Malaysia',
            'ref' => 'malaysia',
            'colour' => '44b39b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Maldives',
            'ref' => 'maldives',
            'colour' => 'e44420',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Mali',
            'ref' => 'mali',
            'colour' => '068246',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Malta',
            'ref' => 'malta',
            'colour' => '6ac196',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Marshall Islands',
            'ref' => 'marshall_islands',
            'colour' => '3b7168',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Mauritania',
            'ref' => 'mauritania',
            'colour' => 'e82fa8',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Mauritius',
            'ref' => 'mauritius',
            'colour' => '02acb1',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Mexico',
            'ref' => 'mexico',
            'colour' => 'd0a1bc',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'north_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Federated States of Micronesia',
            'ref' => 'micronesia',
            'colour' => 'a555f5',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Moldova',
            'ref' => 'moldova',
            'colour' => 'a4608b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'eastern_europe',
            ),
        ),
        array(
            'title' => 'Monaco',
            'ref' => 'monaco',
            'colour' => '7adb4b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe'
            ),
        ),
        array(
            'title' => 'Mongolia',
            'ref' => 'mongolia',
            'colour' => '0f0d8e',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Montenegro',
            'ref' => 'montenegro',
            'colour' => '91012c',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
            ),
        ),
        array(
            'title' => 'Morocco',
            'ref' => 'morocco',
            'colour' => 'e55393',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Mozambique',
            'ref' => 'mozambique',
            'colour' => 'd2b76c',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Myanmar (Burma)',
            'ref' => 'myanmar_burma',
            'colour' => '93dfef',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Namibia',
            'ref' => 'namibia',
            'colour' => 'fadd9d',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Nauru',
            'ref' => 'nauru',
            'colour' => 'a509f2',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Nepal',
            'ref' => 'nepal',
            'colour' => '0c6efe',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Netherlands',
            'ref' => 'netherlands',
            'colour' => 'ce839b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'New Zealand',
            'ref' => 'new_zealand',
            'colour' => '8e9231',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Nicaragua',
            'ref' => 'nicaragua',
            'colour' => '19eb19',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'cenral_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Niger',
            'ref' => 'niger',
            'colour' => '32a44b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Nigeria',
            'ref' => 'nigeria',
            'colour' => '05c855',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'North Korea',
            'ref' => 'north_korea',
            'colour' => 'f9e12d',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'North Macedonia',
            'ref' => 'north_macedonia',
            'colour' => '9f4b4a',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
            ),
        ),
        array(
            'title' => 'Norway',
            'ref' => 'norway',
            'colour' => '967a4f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'scandanavia',
            ),
        ),
        array(
            'title' => 'Oman',
            'ref' => 'oman',
            'colour' => 'f79f5c',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'middle_east',
            ),
        ),
        array(
            'title' => 'Pakistan',
            'ref' => 'pakistan',
            'colour' => '3fd257',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Palau',
            'ref' => 'palau',
            'colour' => '2d8878',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Panama',
            'ref' => 'panama',
            'colour' => '1e79e4',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'central_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Papua New Guinea',
            'ref' => 'papua_new_guinea',
            'colour' => 'c81b6b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Paraguay',
            'ref' => 'paraguay',
            'colour' => 'c602e6',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Peru',
            'ref' => 'peru',
            'colour' => '947ec0',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'pacific_rim',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Philippines',
            'ref' => 'philippines',
            'colour' => '51c6b3',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Poland',
            'ref' => 'poland',
            'colour' => 'f76048',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'eastern_europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Portugal',
            'ref' => 'portugal',
            'colour' => '57451c',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Puerto Rico',
            'ref' => 'puerto_rico',
            'colour' => '2bcc26',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Qatar',
            'ref' => 'qatar',
            'colour' => 'bd36b2',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'middle_east',
            ),
        ),
        array(
            'title' => 'Romania',
            'ref' => 'romania',
            'colour' => '91da08',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'eastern_europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Russia',
            'ref' => 'russia',
            'colour' => 'fee974',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'eastern_europe',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Rwanda',
            'ref' => 'rwanda',
            'colour' => 'b0e8ba',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Saint Kitts and Nevis',
            'ref' => 'saint_kitts_and_nevis',
            'colour' => '02f156',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
            ),
        ),
        array(
            'title' => 'Saint Lucia',
            'ref' => 'saint_lucia',
            'colour' => '605c77',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
            ),
        ),
        array(
            'title' => 'Saint Vincent and the Grenadines',
            'ref' => 'saint_vincent_and_the_grenadines',
            'colour' => '46670a',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
            ),
        ),
        array(
            'title' => 'Samoa',
            'ref' => 'samoa',
            'colour' => '8177d0',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'San Marino',
            'ref' => 'san_marino',
            'colour' => 'd16dd4',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'san marino',
            ),
        ),
        array(
            'title' => 'Sao Tome and Principe',
            'ref' => 'sao_tome_and_principe',
            'colour' => '5663fd',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Saudi Arabia',
            'ref' => 'saudi_arabia',
            'colour' => '9d5a0d',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'middle_east',
                'middle_east',
            ),
        ),
        array(
            'title' => 'Senegal',
            'ref' => 'senegal',
            'colour' => '3ff1fa',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Serbia',
            'ref' => 'serbia',
            'colour' => '6778ea',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
            ),
        ),
        array(
            'title' => 'Seychelles',
            'ref' => 'seychelles',
            'colour' => '88422a',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Sierra Leone',
            'ref' => 'sierra_leone',
            'colour' => '4c61c6',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Singapore',
            'ref' => 'singapore',
            'colour' => '878ae6',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Slovakia',
            'ref' => 'slovakia',
            'colour' => '9380fc',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
                'eastern_europe',
            ),
        ),
        array(
            'title' => 'Slovenia',
            'ref' => 'slovenia',
            'colour' => '2ba40b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Solomon Islands',
            'ref' => 'solomon_islands',
            'colour' => '8d132b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Somalia',
            'ref' => 'somalia',
            'colour' => '783af1',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'South Africa',
            'ref' => 'south_africa',
            'colour' => '925043',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'South Korea',
            'ref' => 'south_korea',
            'colour' => '85e6bc',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'South Sudan',
            'ref' => 'south_sudan',
            'colour' => '25b74e',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Spain',
            'ref' => 'spain',
            'colour' => 'f5e5f5',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
                'european_union',
            ),
        ),
        array(
            'title' => 'Sri Lanka',
            'ref' => 'sri_lanka',
            'colour' => 'd2c74a',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Sudan',
            'ref' => 'sudan',
            'colour' => 'a74306',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Suriname',
            'ref' => 'suriname',
            'colour' => '5bd9fa',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
            ),
        ),
        array(
            'title' => 'Sweden',
            'ref' => 'sweden',
            'colour' => '670479',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'european_union',
                'scandanavia',
            ),
        ),
        array(
            'title' => 'Switzerland',
            'ref' => 'switzerland',
            'colour' => 'd4fc11',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe',
            ),
        ),
        array(
            'title' => 'Syria',
            'ref' => 'syria',
            'colour' => 'a6e562',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'middle_east',
            ),
        ),
        array(
            'title' => 'Tajikistan',
            'ref' => 'tajikistan',
            'colour' => '6c33f8',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
            ),
        ),
        array(
            'title' => 'Taiwan',
            'ref' => 'taiwan',
            'colour' => '77dad5',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Tanzania',
            'ref' => 'tanzania',
            'colour' => '9b7698',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Thailand',
            'ref' => 'thailand',
            'colour' => '199913',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Timor-Leste (East Timor)',
            'ref' => 'timor-leste_east_timor',
            'colour' => '272523',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Togo',
            'ref' => 'togo',
            'colour' => 'e75241',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Tonga',
            'ref' => 'tonga',
            'colour' => 'b2335b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Trinidad and Tobago',
            'ref' => 'trinidad_and_tobago',
            'colour' => 'c58407',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'caribbean',
            ),
        ),
        array(
            'title' => 'Tunisia',
            'ref' => 'tunisia',
            'colour' => '5e3cf7',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Turkey',
            'ref' => 'turkey',
            'colour' => '550820',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'middle_east',
            ),
        ),
        array(
            'title' => 'Turkmenistan',
            'ref' => 'turkmenistan',
            'colour' => '46ab6f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
            ),
        ),
        array(
            'title' => 'Tuvalu',
            'ref' => 'tuvalu',
            'colour' => '4c87df',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Uganda',
            'ref' => 'uganda',
            'colour' => '3b9cd1',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Ukraine',
            'ref' => 'ukraine',
            'colour' => 'effbe0',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'eastern_europe',
            ),
        ),
        array(
            'title' => 'United Arab Emirates',
            'ref' => 'united_arab_emirates',
            'colour' => 'c0e9bf',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'middle_east',
            ),
        ),
        array(
            'title' => 'United Kingdom',
            'ref' => 'united_kingdom',
            'colour' => 'd1547b',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'europe',
                'western_europe'
            ),
        ),
        array(
            'title' => 'United States of America',
            'ref' => 'usa',
            'colour' => 'd78d6f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'north_america',
                'pacific_rim',
            ),
        ),
        array(
            'title' => 'Uruguay',
            'ref' => 'uruguay',
            'colour' => '60188a',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Uzbekistan',
            'ref' => 'uzbekistan',
            'colour' => '097d44',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
            ),
        ),
        array(
            'title' => 'Vanuatu',
            'ref' => 'vanuatu',
            'colour' => 'a4783f',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'oceania',
                'pacific_rim',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Venezuela',
            'ref' => 'venezuela',
            'colour' => '568c6d',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'south_america',
                'latin_america',
            ),
        ),
        array(
            'title' => 'Vietnam',
            'ref' => 'vietnam',
            'colour' => '516673',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Yemen',
            'ref' => 'yemen',
            'colour' => '457f46',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'middle_east',
            ),
        ),
        array(
            'title' => 'Zambia',
            'ref' => 'zambia',
            'colour' => '40446a',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Zimbabwe',
            'ref' => 'zimbabwe',
            'colour' => 'c1e850',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'africa',
            ),
        ),
        array(
            'title' => 'Hong Kong',
            'ref' => 'hong_kong',
            'colour' => '87CEEB',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'asia',
                'china',
                'pacific_rim',
                'far_east',
                'asia_pacific',
            ),
        ),
        array(
            'title' => 'Hawaii',
            'ref' => 'hawaii',
            'colour' => '9370DB',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'usa'
            ),
        ),
        array(
            'title' => 'Quebec',
            'ref' => 'quebec',
            'colour' => '000080',
            'system' => 1,
            'pos' => 0,
            'parent_tag_refs' => array(
                'canada',
                'pacific_rim',
            ),
        ),
    );

}
