<?php


class CurrencyList {
    
    public static $DEFAULT_COUNTRY="US";
    
    public static $currencies=array(
      "29"=>array("ID"=>"29","Name"=>"Algerian dinar","Abbreviation"=>"DZD","Symbol"=>"DZD","AfterNumber"=>true),
      "36"=>array("ID"=>"36","Name"=>"Australian dollar","Abbreviation"=>"AUD","Symbol"=>"A$","AfterNumber"=>false),
      "12"=>array("ID"=>"12","Name"=>"Bahraini dinar","Abbreviation"=>"BHD","Symbol"=>"BD","AfterNumber"=>true), 
      "24"=>array("ID"=>"24","Name"=>"Bangladeshi taka","Abbreviation"=>"BDT","Symbol"=>"&#x9f3;","AfterNumber"=>false),
      "38"=>array("ID"=>"38","Name"=>"Brazilian real","Abbreviation"=>"BRL","Symbol"=>"R$","AfterNumber"=>false),   
      "3"=>array("ID"=>"3","Name"=>"British Pound Sterling","Abbreviation"=>"GBP","Symbol"=>"&pound;","AfterNumber"=>false),
      "2"=>array("ID"=>"2","Name"=>"Canadian Dollar","Abbreviation"=>"CAD","Symbol"=>"C$","AfterNumber"=>false),
      "22"=>array("ID"=>"22","Name"=>"Chinese Yan","Abbreviation"=>"CNY","Symbol"=>"&#65509;","AfterNumber"=>false),
      "4"=>array("ID"=>"4","Name"=>"Danish krone","Abbreviation"=>"DKK","Symbol"=>"DKK","AfterNumber"=>true),
      "20"=>array("ID"=>"20","Name"=>"Egyptian pound","Abbreviation"=>"EGP","Symbol"=>"E&pound;","AfterNumber"=>false),
      "6"=>array("ID"=>"6","Name"=>"Euro","Abbreviation"=>"EUR","Symbol"=>"&euro;","AfterNumber"=>false),
      "21"=>array("ID"=>"21","Name"=>"Indian rupee","Abbreviation"=>"INR","Symbol"=>"&#8377;","AfterNumber"=>false),
      "35"=>array("ID"=>"35","Name"=>"Indonesian rupiah","Abbreviation"=>"IDR","Symbol"=>"Rp","AfterNumber"=>false),
      "26"=>array("ID"=>"26","Name"=>"Iranian rial","Abbreviation"=>"IRR","Symbol"=>"IRR","AfterNumber"=>true),
      "14"=>array("ID"=>"14","Name"=>"Israeli new shekel","Abbreviation"=>"ILS","Symbol"=>"&#8362;","AfterNumber"=>false),
      "32"=>array("ID"=>"32","Name"=>"Japanese yen","Abbreviation"=>"JPY","Symbol"=>"&yen;","AfterNumber"=>false),
      "7"=>array("ID"=>"7","Name"=>"Jordanian dinar","Abbreviation"=>"JOD","Symbol"=>"JD","AfterNumber"=>true),
      "33"=>array("ID"=>"33","Name"=>"Korean Republic Won","Abbreviation"=>"KRW","Symbol"=>"&#8361;","AfterNumber"=>false),
      "18"=>array("ID"=>"18","Name"=>"Kuwaiti dinar","Abbreviation"=>"KWD","Symbol"=>"KD","AfterNumber"=>true),
      "17"=>array("ID"=>"17","Name"=>"Lebanese pound","Abbreviation"=>"LBP","Symbol"=>"LBP","AfterNumber"=>true),
      "34"=>array("ID"=>"34","Name"=>"Malaysian ringgit","Abbreviation"=>"MYR","Symbol"=>"RM","AfterNumber"=>true),
      "30"=>array("ID"=>"30","Name"=>"Mauritanian ouguiya","Abbreviation"=>"UM","Symbol"=>"UM","AfterNumber"=>true),
      "27"=>array("ID"=>"27","Name"=>"Moroccan dirham","Abbreviation"=>"MAD","Symbol"=>"MAD","AfterNumber"=>true),
      "37"=>array("ID"=>"37","Name"=>"Nigerian naira","Abbreviation"=>"NGN","Symbol"=>"&#x20a6;","AfterNumber"=>false),        
      "11"=>array("ID"=>"11","Name"=>"Omani rial","Abbreviation"=>"OMR","Symbol"=>"OMR","AfterNumber"=>true),
      "25"=>array("ID"=>"25","Name"=>"Pakistani rupee","Abbreviation"=>"PKR","Symbol"=>"PKR","AfterNumber"=>true),
      "10"=>array("ID"=>"10","Name"=>"Qatari riyal","Abbreviation"=>"QAR","Symbol"=>"QAR","AfterNumber"=>true),
      "23"=>array("ID"=>"23","Name"=>"Russian ruble","Abbreviation"=>"RUB","Symbol"=>"RUB","AfterNumber"=>true),
      "8"=>array("ID"=>"8","Name"=>"Saudi Riyal","Abbreviation"=>"SAR","Symbol"=>"SAR","AfterNumber"=>true),
      "31"=>array("ID"=>"31","Name"=>"Somali shilling","Abbreviation"=>"SOS","Symbol"=>"SOS","AfterNumber"=>true),
      "5"=>array("ID"=>"4","Name"=>"Swedish krone","Abbreviation"=>"DKK","Symbol"=>"DKK","AfterNumber"=>true),
      "15"=>array("ID"=>"15","Name"=>"Syrian pound","Abbreviation"=>"SYP","Symbol"=>"SYP","AfterNumber"=>true),
      "28"=>array("ID"=>"28","Name"=>"Tunisian dinar","Abbreviation"=>"TND","Symbol"=>"TND","AfterNumber"=>true),
      "16"=>array("ID"=>"16","Name"=>"Turkish lira","Abbreviation"=>"TRY","Symbol"=>"&#8378;","AfterNumber"=>false),
      "9"=>array("ID"=>"9","Name"=>"UAE dirham","Abbreviation"=>"AED","Symbol"=>"AED","AfterNumber"=>true),
      "1"=>array("ID"=>"1","Name"=>"US Dollar","Abbreviation"=>"USD","Symbol"=>"$","AfterNumber"=>false),
      "13"=>array("ID"=>"13","Name"=>"Yemeni rial","Abbreviation"=>"YER","Symbol"=>"YER","AfterNumber"=>true)   
    );
    
    public static $countries_currencies=array(
      "US"=>array("currency"=>"1","number_format"=>"1","time_format"=>"1","date_format"=>"1","language"=>"en"),
      "GB"=>array("currency"=>"3","number_format"=>"6","time_format"=>"1","date_format"=>"2","language"=>"en"),
      "JP"=>array("currency"=>"32","number_format"=>"5","time_format"=>"1","date_format"=>"5","language"=>"en") 
    ); 
    
    public static function get_default_currency($country_code){
        if(array_key_exists($country_code,CurrencyList::$countries_currencies)) 
                return CurrencyList::$countries_currencies[$country_code]["currency"];
        else
           return CurrencyList::$countries_currencies[CurrencyList::$DEFAULT_COUNTRY]["currency"]; 
    }
    
    public static function get_default_number_format($country_code){
        if(array_key_exists($country_code,CurrencyList::$countries_currencies)) 
                return CurrencyList::$countries_currencies[$country_code]["number_format"];
        else
            return CurrencyList::$countries_currencies[CurrencyList::$DEFAULT_COUNTRY]["number_format"]; 
    }
    
    public static function get_default_time_format($country_code){
        if(array_key_exists($country_code,CurrencyList::$countries_currencies)) 
                return CurrencyList::$countries_currencies[$country_code]["time_format"];
        else
            return CurrencyList::$countries_currencies[CurrencyList::$DEFAULT_COUNTRY]["time_format"];
    }
    
    public static function get_default_date_format($country_code){
        if(array_key_exists($country_code,CurrencyList::$countries_currencies)) 
                return CurrencyList::$countries_currencies[$country_code]["date_format"];
        else
            return CurrencyList::$countries_currencies[CurrencyList::$DEFAULT_COUNTRY]["date_format"];
    } 
    
    public static function get_default_language($country_code){
        if(array_key_exists($country_code,CurrencyList::$countries_currencies))       
                return CurrencyList::$countries_currencies[$country_code]["language"];
        else
            return CurrencyList::$countries_currencies[CurrencyList::$DEFAULT_COUNTRY]["language"];        
    }    
    
    public static function get_currency($id){
        return CurrencyList::$currencies[$id];
    }
    
    public static function get_user_currencies(){
        $array = array();
        foreach(CurrencyList::$currencies as $key => $value) {
            $array[] = array("id" => $value["ID"], "name" => $value["Name"], "abbreviation" => $value["Abbreviation"],"symbol"=>$value["Symbol"]);
            
        }
        return $array;
    }    

    
    public static function get_currencies(){
        $array = array();
        foreach(CurrencyList::$currencies as $key => $value) {
            $array[] = array("ID" => $value["ID"], "Name" => $value["Name"], "Abbreviation" => $value["Abbreviation"],"Symbol"=>$value["Symbol"]);
            
        }
        return $array;
    }
    


}
