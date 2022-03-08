<?php

class Target {
    
    public static $EVERY_MIN=1;
    public static $EVERY_MAX=12;
    public static $MIN_AMOUNT=0.01;
    public static $MAX_AMOUNT=999999;
    
    private $id;
    private $formatted_amount; //formatted amount
    private $amount_as_number; //amount as number
    private $period; //M=month, Y=Year
    private $formatted_period;
    private $every_as_number;
    private $cat_name;
    private $cat_id;
    private $cat_color;
    private $currency_id;
    private $equivalent; //equivalent amount in the user's prefered currency
    private $formatted_equivalent; //formatted equivalent amount
    private $rollover;

    function __construct($id,$currency_id,$amount_as_number=0,$every_as_number = 1, $period = "M",$cat_id="",$rollover=false) {
        $this->id=$id;
        $this->currency_id=$currency_id;
        $this->amount_as_number=$amount_as_number;
        $this->every_as_number = $every_as_number;
        $this->period = $period;
        $this->cat_id=$cat_id;  
        $this->set_formatted_period();
        $this->rollover=$rollover;
    }
    
    static function fix_every($every){
        $every=  intval($every);
        if($every<Target::$EVERY_MIN)
            $every=Target::$EVERY_MIN;
        if($every>Target::$EVERY_MAX)
            $every=Target::$EVERY_MAX;
        return $every;
    }

    static function fix_period($period){
        if($period=="Y" || $period=="M")
            return $period;
        return "M";
    }
    
    static function fix_rollover($rollover){
        if($rollover=="1" || $rollover=="0" ||$rollover==false || $rollover==true || $rollover=="false" ||$rollover=="true")
            return $rollover;
        return "0";
    }     
    
    function set_formatted_period(){
        switch($this->period){
            case "M":
                if($this->every_as_number>1)
                    $this->formatted_period.="$this->every_as_number months";
                else 
                    $this->formatted_period.="month";
                break;
            case "Y":
                if($this->every_as_number>1)
                    $this->formatted_period.="$this->every_as_number years";
                else 
                    $this->formatted_period.="year";
                break;                
        }
    }
    
    function set_formatted_amount($number_format){
        $this->formatted_amount=Utility::get_formatted_money($this->currency_id, $this->amount_as_number, $number_format);
    }
    
    function set_equivalent($user_currency_id,$number_format){
        $amount=0;
        switch($this->period){
            case "M":
                $amount=$this->amount_as_number/$this->every_as_number;
                break;
            case "Y":
                $amount=$this->amount_as_number/($this->every_as_number*12);
                break;
        }
        
        $this->equivalent=$_SESSION["user"]->convert_money($amount, $this->currency_id, $user_currency_id);
        //$this->equivalent=Utility::convert_money($amount, $this->currency_id, $user_currency_id);
        $this->formatted_equivalent=Utility::get_formatted_money($user_currency_id, $this->equivalent, $number_format)." /mo";
    }
    
    function get_equivalent(){
        return $this->equivalent;
    }
    
    function set_category_name_color($name,$color){
        $this->cat_name=is_null($name)?"Unknown":$name;
        $this->cat_color=is_null($color)?"#FFFFFF":$color;
    }    
    
    function set_category_info($database){
        $result=Category::get_category_name_color($database, $this->cat_id);
        $this->cat_name=$result["Name"];
        $this->cat_color=$result["Color"];
    }

    function to_array() {
        return array("id" => $this->id, "target_amount" => $this->formatted_amount,"target_amount_as_number"=>$this->amount_as_number,
            "target_formatted_period"=>$this->formatted_period,"target_period"=>$this->period,"target_every_as_number"=>$this->every_as_number,
            "target_equivalent"=>$this->equivalent,"target_formatted_equivalent"=>$this->formatted_equivalent,"target_rollover"=>$this->rollover,
            "category_id"=>$this->cat_id,"target_category_color"=>$this->cat_color,"target_category_name"=>ucfirst($this->cat_name),"target_currency_id"=>$this->currency_id);
    }
    
}

?>