<?php

include "CurrencyList.php";

class Utility {

    public static $SUCCESS="success";
    public static $FAIL="fail";
    public static $SESSION_INACTIVE="session_inactive";
    public static $RESULT="result";
    public static $MIN_NUMBER_FORMAT = 1;
    public static $MAX_NUMBER_FORMAT = 12;
    public static $MIN_DATE_FORMAT = 1;
    public static $MAX_DATE_FORMAT = 15;
    public static $MIN_TIME_FORMAT = 1;
    public static $MAX_TIME_FORMAT = 4;
    public static $default_number_format = 1;
    public static $default_date_format = "n/j/Y"; //default
    public static $default_short_date_format = "n/j"; //default
    public static $date_formats = array(1 => "n/j/Y", 2 => "j/n/Y", 3 => "n/j/y", 4 => "j/n/y",
        5 => "Y/n/j", 6 => "n-j-Y", 7 => "j-n-Y", 8 => "n-j-y", 9 => "j-n-y", 10 => "Y-n-j",
        11 => "n.j.Y", 12 => "j.n.Y", 13 => "n.j.y", 14 => "j.n.y", 15 => "Y.n.j"
    );
    public static $short_date_formats = array(1 => "n/j", 2 => "j/n", 3 => "n/j", 4 => "j/n",
        5 => "n/j", 6 => "n-j", 7 => "j-n", 8 => "n-j", 9 => "j-n", 10 => "n-j",
        11 => "n.j", 12 => "j.n", 13 => "n.j", 14 => "j.n", 15 => "n.j"
    );
    public static $default_time_format = "g:i a";
    public static $time_formats = array(1 => "g:i a", 2 => "g:i A", 3 => "G:i", 4 => "H:i");
    public static $supported_languages = array(
        'aa' => 'Afar',
        'ab' => 'Abkhaz',
        'ae' => 'Avestan',
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'am' => 'Amharic',
        'an' => 'Aragonese',
        'ar' => 'Arabic',
        'as' => 'Assamese',
        'av' => 'Avaric',
        'ay' => 'Aymara',
        'az' => 'Azerbaijani',
        'ba' => 'Bashkir',
        'be' => 'Belarusian',
        'bg' => 'Bulgarian',
        'bh' => 'Bihari',
        'bi' => 'Bislama',
        'bm' => 'Bambara',
        'bn' => 'Bengali',
        'bo' => 'Tibetan Standard, Tibetan, Central',
        'br' => 'Breton',
        'bs' => 'Bosnian',
        'ca' => 'Catalan; Valencian',
        'ce' => 'Chechen',
        'ch' => 'Chamorro',
        'co' => 'Corsican',
        'cr' => 'Cree',
        'cs' => 'Czech',
        'cu' => 'Old Church Slavonic, Church Slavic, Church Slavonic, Old Bulgarian, Old Slavonic',
        'cv' => 'Chuvash',
        'cy' => 'Welsh',
        'da' => 'Danish',
        'de' => 'German',
        'dv' => 'Divehi; Dhivehi; Maldivian;',
        'dz' => 'Dzongkha',
        'ee' => 'Ewe',
        'el' => 'Greek, Modern',
        'en' => 'English',
        'eo' => 'Esperanto',
        'es' => 'Spanish; Castilian',
        'et' => 'Estonian',
        'eu' => 'Basque',
        'fa' => 'Persian',
        'ff' => 'Fula; Fulah; Pulaar; Pular',
        'fi' => 'Finnish',
        'fj' => 'Fijian',
        'fo' => 'Faroese',
        'fr' => 'French',
        'fy' => 'Western Frisian',
        'ga' => 'Irish',
        'gd' => 'Scottish Gaelic; Gaelic',
        'gl' => 'Galician',
        'gn' => 'GuaranÃ­',
        'gu' => 'Gujarati',
        'gv' => 'Manx',
        'ha' => 'Hausa',
        'he' => 'Hebrew (modern)',
        'hi' => 'Hindi',
        'ho' => 'Hiri Motu',
        'hr' => 'Croatian',
        'ht' => 'Haitian; Haitian Creole',
        'hu' => 'Hungarian',
        'hy' => 'Armenian',
        'hz' => 'Herero',
        'ia' => 'Interlingua',
        'id' => 'Indonesian',
        'ie' => 'Interlingue',
        'ig' => 'Igbo',
        'ii' => 'Nuosu',
        'ik' => 'Inupiaq',
        'io' => 'Ido',
        'is' => 'Icelandic',
        'it' => 'Italian',
        'iu' => 'Inuktitut',
        'ja' => 'Japanese (ja)',
        'jv' => 'Javanese (jv)',
        'ka' => 'Georgian',
        'kg' => 'Kongo',
        'ki' => 'Kikuyu, Gikuyu',
        'kj' => 'Kwanyama, Kuanyama',
        'kk' => 'Kazakh',
        'kl' => 'Kalaallisut, Greenlandic',
        'km' => 'Khmer',
        'kn' => 'Kannada',
        'ko' => 'Korean',
        'kr' => 'Kanuri',
        'ks' => 'Kashmiri',
        'ku' => 'Kurdish',
        'kv' => 'Komi',
        'kw' => 'Cornish',
        'ky' => 'Kirghiz, Kyrgyz',
        'la' => 'Latin',
        'lb' => 'Luxembourgish, Letzeburgesch',
        'lg' => 'Luganda',
        'li' => 'Limburgish, Limburgan, Limburger',
        'ln' => 'Lingala',
        'lo' => 'Lao',
        'lt' => 'Lithuanian',
        'lu' => 'Luba-Katanga',
        'lv' => 'Latvian',
        'mg' => 'Malagasy',
        'mh' => 'Marshallese',
        'mi' => 'Maori',
        'mk' => 'Macedonian',
        'ml' => 'Malayalam',
        'mn' => 'Mongolian',
        'mr' => 'Marathi (Mara?hi)',
        'ms' => 'Malay',
        'mt' => 'Maltese',
        'my' => 'Burmese',
        'na' => 'Nauru',
        'nb' => 'Norwegian BokmÃ¥l',
        'nd' => 'North Ndebele',
        'ne' => 'Nepali',
        'ng' => 'Ndonga',
        'nl' => 'Dutch',
        'nn' => 'Norwegian Nynorsk',
        'no' => 'Norwegian',
        'nr' => 'South Ndebele',
        'nv' => 'Navajo, Navaho',
        'ny' => 'Chichewa; Chewa; Nyanja',
        'oc' => 'Occitan',
        'oj' => 'Ojibwe, Ojibwa',
        'om' => 'Oromo',
        'or' => 'Oriya',
        'os' => 'Ossetian, Ossetic',
        'pa' => 'Panjabi, Punjabi',
        'pi' => 'Pali',
        'pl' => 'Polish',
        'ps' => 'Pashto, Pushto',
        'pt' => 'Portuguese',
        'qu' => 'Quechua',
        'rm' => 'Romansh',
        'rn' => 'Kirundi',
        'ro' => 'Romanian, Moldavian, Moldovan',
        'ru' => 'Russian',
        'rw' => 'Kinyarwanda',
        'sa' => 'Sanskrit (Sa?sk?ta)',
        'sc' => 'Sardinian',
        'sd' => 'Sindhi',
        'se' => 'Northern Sami',
        'sg' => 'Sango',
        'si' => 'Sinhala, Sinhalese',
        'sk' => 'Slovak',
        'sl' => 'Slovene',
        'sm' => 'Samoan',
        'sn' => 'Shona',
        'so' => 'Somali',
        'sq' => 'Albanian',
        'sr' => 'Serbian',
        'ss' => 'Swati',
        'st' => 'Southern Sotho',
        'su' => 'Sundanese',
        'sv' => 'Swedish',
        'sw' => 'Swahili',
        'ta' => 'Tamil',
        'te' => 'Telugu',
        'tg' => 'Tajik',
        'th' => 'Thai',
        'ti' => 'Tigrinya',
        'tk' => 'Turkmen',
        'tl' => 'Tagalog',
        'tn' => 'Tswana',
        'to' => 'Tonga (Tonga Islands)',
        'tr' => 'Turkish',
        'ts' => 'Tsonga',
        'tt' => 'Tatar',
        'tw' => 'Twi',
        'ty' => 'Tahitian',
        'ug' => 'Uighur, Uyghur',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        've' => 'Venda',
        'vi' => 'Vietnamese',
        'vo' => 'VolapÃ¼k',
        'wa' => 'Walloon',
        'wo' => 'Wolof',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba',
        'za' => 'Zhuang, Chuang',
        'zh' => 'Chinese',
        'zu' => 'Zulu',
    );

    public static function get_month_name($month_number) {
        switch ($month_number) {
            case 1:
                return "January";
                break;
            case 2:
                return "February";
                break;
            case 3:
                return "March";
                break;
            case 4:
                return "April";
                break;
            case 5:
                return "May";
                break;
            case 6:
                return "June";
                break;
            case 7:
                return "July";
                break;
            case 8:
                return "August";
                break;
            case 9:
                return "September";
                break;
            case 10:
                return "October";
                break;
            case 11:
                return "November";
                break;
            case 12:
                return "December";
                break;
        }
        return "January";
    }

    public static function contains($text, $match) {
        if (trim($text) == "" || trim($match) == "")
            return false;
        $text = strtolower(trim($text));
        $match = strtolower(trim($match));
        return strpos($text, $match) !== false;
    }

    public static function isDate($date, $format = 'm/d/Y') {
        $d = DateTime::createFromFormat($format, $date);
        return $d != false;
    }

    public static function parseDate($date, $format = 'm/d/Y') {
        return DateTime::createFromFormat($format, $date);
    }

    public static function fix_language($lang) {
        foreach (Utility::$supported_languages as $language_key => $language_value) {
            if ($language_key == $lang) //found the language
                return $lang;
        }
        return "en"; //default language 
    }

    public static function get_default_category_name($lang = "en") {
        $default_category_name = "Miscellaneous";
        if ($lang == "ar")
            $default_category_name = "غير مصنف";
        return $default_category_name;
    }

    public static function get_formatted_date_NO_YEAR($date_format_id, $date_object) {
        $date_format = Utility::$default_short_date_format;

        $date_format_id = intval($date_format_id);
        if ($date_format_id >= 1 && $date_format_id <= 15)
            $date_format = Utility::$short_date_formats[$date_format_id];

        return date_format($date_object, $date_format);
    }

    public static function get_formatted_date($date_format_id, $date_object) {
        $date_format = Utility::$default_date_format;
        $date_format_id = intval($date_format_id);
        if ($date_format_id >= 1 && $date_format_id <= 15)
            $date_format = Utility::$date_formats[$date_format_id];

        return date_format($date_object, $date_format);
    }

    public static function get_formatted_time($time_format_id, $date_object) {
        $time_format = Utility::$default_time_format;

        $time_format_id = intval($time_format_id);
        if ($time_format_id >= 1 && $time_format_id <= 4)
            $time_format = Utility::$time_formats[$time_format_id];

        return date_format($date_object, $time_format);
    }

    public static function get_formatted_date_time_NO_TIME_ZONE($date_format_id, $time_format_id, $date) {
        $date_object = DateTime::createFromFormat("Y-m-d G:i:s", $date);
        $formated_date = Utility::get_formatted_date($date_format_id, $date_object);
        $formatted_time = Utility::get_formatted_time($time_format_id, $date_object);
        return $formated_date . "  " . $formatted_time;
    }

    public static function get_formatted_date_time($date_format_id, $time_format_id, $date, $time_zone_offset) {
        $date_object = DateTime::createFromFormat("Y-m-d G:i:s", $date);
        $minute_difference = intval($time_zone_offset);
        date_modify($date_object, (-1 * $minute_difference) . " minute");
        $formated_date = Utility::get_formatted_date($date_format_id, $date_object);
        $formatted_time = Utility::get_formatted_time($time_format_id, $date_object);
        return $formated_date . "  " . $formatted_time;
    }

    public static function get_local_date_object($date, $time_zone_offset) {
        $date_object = DateTime::createFromFormat("Y-m-d G:i:s", $date);
        $minute_difference = intval($time_zone_offset);
        date_modify($date_object, (-1 * $minute_difference) . " minute");
        return $date_object;
    }

    public static function to_UTC_date_time($date, $time_zone_offset, $format = "Y-m-d G:i:s") {
        $date_object = DateTime::createFromFormat($format, $date);
        $minute_difference = intval($time_zone_offset);
        date_modify($date_object, $minute_difference . " minute");
        return date_format($date_object, "Y-m-d G:i:s");
    }

    public static function to_formatted_date_time($date_object, $format = "Y-m-d G:i:s") {
        return date_format($date_object, "Y-m-d G:i:s");
    }

    public static function to_UTC_date_time_object($date, $time_zone_offset, $format = "Y-m-d G:i:s") {
        $date_object = DateTime::createFromFormat($format, $date);
        $minute_difference = intval($time_zone_offset);
        date_modify($date_object, $minute_difference . " minute");
        return $date_object;
    }

    public static function get_years($start_year, $end_year) {
        $years = array();
        if ($end_year < $start_year || $end_year === $start_year)
            $years[] = array("year"=>$start_year);
        else {
            $current_year = $start_year;
            while ($current_year <= $end_year) {
                $year_item=array("year"=>$current_year);
                $years[] = $year_item;
                ++$current_year;
            }
        }
        return $years;
    }

    public static function get_year_component($date_object) {
        return intval(date_format($date_object, "Y"));
    }

    public static function get_month_component($date_object) {
        return intval(date_format($date_object, "n"));
    }

    public static function month_difference($date_1, $date_2) {
        $month_1 = Utility::get_month_component($date_1);
        $year_1 = Utility::get_year_component($date_1);

        $month_2 = Utility::get_month_component($date_2);
        $year_2 = Utility::get_year_component($date_2);

        if ($year_1 == $year_2)
            return abs($month_1 - $month_2);
        else if ($year_1 > $year_2) {
            return ($year_1 - $year_2) * 12 + $month_1 - $month_2;
        } else if ($year_1 < $year_2) {
            return ($year_2 - $year_1) * 12 + $month_2 - $month_1;
        }
        return 0;
    }

    public static function to_date_object($date, $format = "Y-m-d G:i:s") {
        $date_object = DateTime::createFromFormat($format, $date);
        return $date_object;
    }

    public static function to_UTC_date_object($date, $time_zone_offset = 0, $format = "Y-m-d G:i:s") {
        $date_object = DateTime::createFromFormat($format, $date);
        $minute_difference = intval($time_zone_offset);
        if ($minute_difference != 0)
            date_modify($date_object, $minute_difference . " minute");
        return $date_object;
    }

    public static function get_next_month($month, $year) {
        $next_month = $month + 1;
        $next_year = $year;
        if ($next_month > 12) {
            $next_month = 1;
            $next_year++;
        }
        $result = array("month" => $next_month, "year" => $next_year);
        return $result;
    }

    public static function get_formatted_money($currency_id, $amount, $number_format) {
        $negative=false;
        if(floatval($amount)<0){
            $negative=true;
            $amount=$amount*-1;
        }
        $money = Utility::formatNumber($amount, $number_format);

        $result = CurrencyList::get_currency($currency_id . "");
        $final = Utility::formatMoney($money, $result["Symbol"], $result["AfterNumber"], $number_format,$negative);
        return $final;
    }

    public static function formatMoney($money, $symbol, $after_number, $number_format,$negative) {
        $final="";
        if ($after_number == true || $number_format == 3 || $number_format == 4)
            $final= $money. " " . $symbol;
        else
            $final= $symbol . $money;
        if($negative)
            $final="-$final";
        return $final;
    }

    public static function formatNumber($number, $number_format) {
        $formated_number = $number;
        switch ($number_format) {
            case 1: //North American Format
            case 6: //British Format
            case 9: //China
            case 7: //Middle East
            case 11: //South Asia
            case 12: //Korea
                $formated_number = number_format($number, 2, ".", ",");
                break;
            case 2: //European
            case 8: //South America
            case 3: //German
            case 10: //North Africa
                $formated_number = number_format($number, 2, ",", ".");
                break;
            case 4: //French
                $formated_number = number_format($number, 2, ",", " ");
                break;
            case 5: //Japanese
                $formated_number = number_format($number, 0, ",", " ");
                break;
            default:
                $formated_number = number_format($number, 2, ".", ",");
        }
        return $formated_number;
    }

    public static function load_user_currencies() {

        return CurrencyList::get_user_currencies();
    }

    public static function load_currencies() {

        return CurrencyList::get_currencies();
    }

    public static function convert_money_currency_symbol($amount, $from, $to) {


     $url = file_get_contents('https://free.currencyconverterapi.com/api/v5/convert?q=' . $from . '_' . $to . '&compact=ultra&apiKey=5ff533471a1e50e2d035');
    $json = json_decode($url, true);
    $rate = implode(" ",$json);
    $total = $rate * $amount;
    return round($total);
    }
    
    public static function get_country_code_of_user(){
        $ip = $_SERVER['REMOTE_ADDR'];
        $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));
        return $details->country;       
    }

    public static function convert_money($amount, $from_currency_id, $to_currency_id) {

        $from_currency = CurrencyList::get_currency($from_currency_id . "");
        $from = $from_currency["Abbreviation"];

        $to_currency = CurrencyList::get_currency($to_currency_id . "");
        $to = $to_currency["Abbreviation"];

        if ($from == $to)
            return $amount;
        
     $url = file_get_contents('https://free.currencyconverterapi.com/api/v5/convert?q=' . $from . '_' . $to . '&compact=ultra&apiKey=5ff533471a1e50e2d035');
    $json = json_decode($url, true);
    $rate = implode(" ",$json);
    $total = $rate * $amount;
    return round($total);
    }

}
