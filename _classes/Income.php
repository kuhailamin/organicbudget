<?php

class Income {

    private $id;
    private $date;
    private $category_id;
    private $item_id;
    private $account_id;
    private $amount;

    function __construct($id = "",$date="",$category_id="",$item_id="",$account_id="",$amount=0) {
        $this->id = $id;
        $this->date=$date;
        $this->category_id=$category_id;
        $this->item_id=$item_id;
        $this->account_id=$account_id;
        $this->amount=$amount;
    }
    
    function to_array($database,$number_format,$time_format,$date_format,$user_id,$time_zone_offset) {
        $currency_id="";
        $account_name="";
        $category_name="";
        $item_name="";
        $result = $database->select_fields_where("Account", "CurrencyID, Name", "ID='$this->account_id' AND UserID='$user_id'");
        if(!$result){
            //do nothing
        }
        else{
            $row=mysqli_fetch_array($result);
            $currency_id=$row["CurrencyID"];
            $account_name=ucfirst($row["Name"]);
        }      

        $result = $database->select_fields_where("Category", "Name", "ID='$this->category_id' AND UserID='$user_id'");
        $category_name="Unknown"; //default category name
        $item_name="Unknown"; //default item name
        if(!$result){
            //do nothing
        }
        else{
            $row=mysqli_fetch_array($result);
            $category_name=is_null($row["Name"])?"Unknown":$row["Name"];
        }
        $result = $database->select_fields_where("Item", "Name", "ID='$this->item_id'");
        if(!$result){
            //do nothing
        }
        else{
            $row=mysqli_fetch_array($result);
            $item_name=is_null($row["Name"])?"Unknown":$row["Name"];
        }        
       
        $formatted_date=Utility::get_formatted_date_time($date_format,$time_format, $this->date,$time_zone_offset);
        $local_date_object=Utility::get_local_date_object($this->date, $time_zone_offset);
        $formatted_amount=Utility::get_formatted_money($currency_id, $this->amount, $number_format); 
        $array = array("type"=>"income","account_name"=>$account_name,"account_id"=>$this->account_id,
            "date"=>$this->date,"category_name"=>ucfirst($category_name),"category_id"=>$this->category_id,
            "item_id"=>$this->item_id,"income_name"=>ucfirst($item_name),"id"=>$this->id,"formatted_date"=>$formatted_date,
            "year"=>date_format($local_date_object,"Y"),"month"=>date_format($local_date_object,"m"), "day"=>date_format($local_date_object,"j"),
            "hour"=>date_format($local_date_object,"G"),"minute"=>date_format($local_date_object,"i"),            
            "amount"=>$this->amount,"formatted_amount"=>$formatted_amount
            );
        return $array;        
    }    
    
    
}
