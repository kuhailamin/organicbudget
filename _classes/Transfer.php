<?php

class Transfer {

    private $id;
    private $date;
    private $from_account;
    private $to_account;
    private $amount;

    function __construct($id = "", $date = "", $from_account = "",$to_account="",$amount=0) {
        $this->id = $id;
        $this->date = $date;
        $this->from_account = $from_account;
        $this->to_account=$to_account;
        $this->amount=$amount;
    }
    
    function to_array($database,$number_format,$time_format,$date_format,$user_id,$time_zone_offset) {
        $currency_id="";
        $from_account_name="";
        $to_account_name="";
        $result = $database->select_fields_where("Account", "CurrencyID, Name", "ID='$this->from_account' AND UserID='$user_id'");
        if(!$result){
            //do nothing
        }
        else{
            $row=mysqli_fetch_array($result);
            $currency_id=$row["CurrencyID"];
            $from_account_name=$row["Name"];
        }
        
        $result = $database->select_fields_where("Account", "Name", "ID='$this->to_account' AND UserID='$user_id'");
        if(!$result){
            //do nothing
        }
        else{
            $row=mysqli_fetch_array($result);
            $to_account_name=$row["Name"];
        }        
        
        $formatted_date=Utility::get_formatted_date_time($date_format,$time_format, $this->date,$time_zone_offset);
        $local_date_object=Utility::get_local_date_object($this->date, $time_zone_offset);        
        $formatted_amount=Utility::get_formatted_money($currency_id, $this->amount, $number_format); 
        $array = array("type"=>"transfer","from_account_name"=>ucfirst($from_account_name),
            "to_account_name"=>ucfirst($to_account_name),"date"=>$this->date,
            "id"=>$this->id,"from_account_id"=>$this->from_account,"formatted_date"=>$formatted_date,
            "year"=>date_format($local_date_object,"Y"),"month"=>date_format($local_date_object,"m"), "day"=>date_format($local_date_object,"j"),
            "hour"=>date_format($local_date_object,"G"),"minute"=>date_format($local_date_object,"i"),             
            "to_account_id"=>$this->to_account,"amount"=>$this->amount,"formatted_amount"=>$formatted_amount
            );
        return $array;        
    }    
    
    

  

}
