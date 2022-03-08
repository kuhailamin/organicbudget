<?php
include "Account.php";

class AccountList {
    
    public static $MIN_NUMBER_ACCOUNTS = 1;
    public static $MAX_NUMBER_ACCOUNTS = 10;

    function __construct() {
        
    }
    
    function add_account($database, $name, $des, $userID, $currency_ID, $money_type, $type, $initial_balance,$number_format) {
        $result = array();
        $name=trim(strtolower($name));
        $type=$this->fix_type($type);
        $money_type=$this->fix_money_type($money_type);
        $initial_balance=$this->fix_initial_balance($initial_balance);
        $currency_ID=$this->fix_currency_ID($database, $currency_ID);
        
        $name_valid = $this->validate_account_name($database, $name, $userID);
        $description_valid = $this->validate_account_description($des);
        $max_num_accounts_valid=$this->validate_max_num_accounts($database, $userID);
        
        $result["account-name"] = $name_valid;
        $result["account-description"] = $description_valid;
        $result["error_message"]=$max_num_accounts_valid==="valid"?"":$max_num_accounts_valid;
        $result["result"] = "fail";
        if ($name_valid == "valid" && $description_valid == "valid" && $max_num_accounts_valid=="valid") {
            $values = array("Name" => $name, "Description" => $des,"UserID"=>$userID,"MoneyType"=>$money_type,"Type"=>$type,
                "InitialBalance"=>$initial_balance,"CurrencyID"=>$currency_ID);
            $final = $database->insert("Account", $values);
            if(!$final)
                return $result;
            $result["result"] = "success";
            $result["id"]=$database->last_insert_id();
            $result["formatted_balance"]=Utility::get_formatted_money($currency_ID, $initial_balance, $number_format);
            $result["initial_balance_formatted"]=$result["formatted_balance"];
        }
        return $result;
    }
    
    function edit_account($database,$name, $des, $userID, $currency_ID, $money_type, $type, $initial_balance,$number_format,$id) {
        $result = array();
        $name=trim(strtolower($name));
        $type=$this->fix_type($type);
        $money_type=$this->fix_money_type($money_type);
        $initial_balance=$this->fix_initial_balance($initial_balance);
        $currency_ID=$this->fix_currency_ID($database, $currency_ID);
        
        $id_valid=$this->validate_account_id($database, $id);
        $name_valid = $this->validate_account_edit_name($database, $name, $userID,$id);
        $description_valid = $this->validate_account_description($des);        
        
        $result["id"] = $id;
        $result["account-name"] = $name_valid;
        $result["account-description"] = $description_valid;
        $result["result"] = "fail";
        if ($id_valid=="valid" && $name_valid == "valid" && $description_valid == "valid") {
            $values = array("Name" => $name, "Description" => $des,"MoneyType"=>$money_type,"Type"=>$type,
                "InitialBalance"=>$initial_balance,"CurrencyID"=>$currency_ID);
            $final = $database->update("Account", $values, "ID='$id' AND UserID='$userID'");
            if(!$final)
                return $result;
            $final_balance=Account::get_total_balance($database, $initial_balance, $currency_ID, $number_format, $id, $userID,$money_type);
            $result["formatted_balance"]=Utility::get_formatted_money($currency_ID, $final_balance, $number_format); 
            $result["balance_as_number"]=$final_balance;
            $result["initial_balance_formatted"]=Utility::get_formatted_money($currency_ID, $initial_balance, $number_format);

            $result["result"] = "success";
        }
        return $result;
    }
    
     function delete_account($database, $userID,$account_id) {
        $result = array();
        $result["result"] = "fail";
        $min_num_accounts_valid=$this->validate_min_num_accounts($database, $userID);
        $result["error_message"]=$min_num_accounts_valid=="valid"?"":$min_num_accounts_valid;
        if($min_num_accounts_valid=="valid"){
            $final = Account::delete_all_transactions($database, $account_id, $userID);
            $final = $database->delete_where("Account","ID='$account_id' AND UserID='$userID'");
            if(!$final)
                return $result;
            $result["result"] = "success";
        }
        return $result;
    }     
    
    function get_accounts($database, $user_ID,$number_Format,$currency_id) {
        $result = $database->select_fields_where("Account", "*", "UserID='$user_ID'", "ORDER BY Name");
        $array = array();
        if (!$result)
            return $array;
        $converted_account_balance=0;
        $account_summary_credit=0;
        $formatted_account_summary_credit="";
        $account_summary_debit=0;
        $formatted_account_summary_debit="";
        $account_summary_cash=0;
        $formatted_account_summary_cash="";
        $account_total=0;
        $formatted_account_total="";
        
        while($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $account = new Account($row["ID"],$row["Name"],$row["Description"],$row["UserID"],$row["CurrencyID"],
                                   $row["MoneyType"],$row["Type"],$row["InitialBalance"]);
            $account_array=$account->to_array($database,$number_Format);
            $account_balance=$account_array["account_balance_as_number"];
            $account_currency_id=$account_array["currency_id"];
            $account_type=intval($account_array["type_as_number"]);
            $account_money_type=intval($account_array["money_type_as_number"]);
            $converted_account_balance=Utility::convert_money($account_balance, $account_currency_id, $currency_id);
            $array[]=$account_array;
            if($account_type==1 && $account_money_type==1){
                $account_summary_debit=$account_summary_debit+$converted_account_balance;
            }
            else if($account_type==1 && $account_money_type==2){
                $account_summary_credit=$account_summary_credit+$converted_account_balance; 
            }
            else if($account_type==2){
                $account_summary_cash=$account_summary_cash+$converted_account_balance; 
            }
                       
        }
            $account_total=$account_summary_cash+$account_summary_debit+-1*$account_summary_credit; 
            $formatted_account_summary_debit=Utility::get_formatted_money($currency_id, $account_summary_debit, $number_Format);
            $formatted_account_summary_credit=Utility::get_formatted_money($currency_id, $account_summary_credit, $number_Format);
            $formatted_account_summary_cash=Utility::get_formatted_money($currency_id, $account_summary_cash, $number_Format);
            $formatted_account_total=Utility::get_formatted_money($currency_id, $account_total, $number_Format);
            if(count($array)>0){
                $array[0]["account_summary_debit"]=$formatted_account_summary_debit;
                $array[0]["account_summary_debit_as_number"]=$account_summary_debit;
                $array[0]["account_summary_credit"]=$formatted_account_summary_credit;
                $array[0]["account_summary_credit_as_number"]=$account_summary_credit;
                $array[0]["account_summary_cash"]=$formatted_account_summary_cash;
                $array[0]["account_summary_cash_as_number"]=$account_summary_cash;
                $array[0]["account_summary_total"]=$formatted_account_total;
            }
        return $array;
    }
    
    function validate_account_id($database, $account_id) {
        if ($this->account_id_exists($database, $account_id))
            return "valid";        
        return "Accout doesn't exist";
    }
    
    function validate_account_edit_name($database, $name, $userID,$account_id) {
        $account_count = $database->count("Account", "ID='$account_id' AND LOWER(Name)=LOWER('$name') AND UserID='$userID'");
        if($account_count==1)//same account as before
          return "valid";
        return $this->validate_account_name($database, $name, $userID);
    }    

    function validate_account_name($database, $name, $userID) {
        if ($name == "")
            return "Name is required";
        if ($this->account_exists($database, $name, $userID))
            return "Account name exists already";
        if (strlen($name) > 30)
            return "Name can't be longer than 30 characters";
        return "valid";
    }

    function validate_account_description($description) {
        if (strlen($description) > 200)
            return "Description can't be longer than 200 characters";
        return "valid";
    }
    
    function validate_max_num_accounts($database,$user_id) {
        $account_count = $database->count("Account", "UserID='$user_id'");
        if($account_count>=AccountList::$MAX_NUMBER_ACCOUNTS){
            return "You can't add more than ".AccountList::$MAX_NUMBER_ACCOUNTS." accounts";
        }
        return "valid";
    } 
    
    function validate_min_num_accounts($database,$user_id) {
        $account_count = $database->count("Account", "UserID='$user_id'");
        if($account_count<=AccountList::$MIN_NUMBER_ACCOUNTS){
            return "You have to have at least ".AccountList::$MIN_NUMBER_ACCOUNTS." account(s)";
        }
        return "valid";
    }     
    
    function fix_currency_ID($database,$currency_ID) {
        $account_count = $database->count("Currency", "ID='$currency_ID'");
        if($account_count==0){ //currency ID is not valid, just get the top most ID
            $result = $database->select_fields_TOP("Currency","ID");
            $row = mysqli_fetch_assoc($result);
            $currency_ID=$row["ID"];
        }
        return $currency_ID;
    }
    
    function fix_initial_balance($initial_balance) {
        if(is_numeric($initial_balance))
            return $initial_balance;
        return 0; //default initial balance
    }
    
    function fix_type($type) {
        if ($type == "1" || $type == "2")
            return $type;
        return "1";
    }
    
    function fix_money_type($type) {
        if ($type == "1" || $type == "2")
            return $type;
        return "1";
    }    
    
    function account_id_exists($database, $account_id) {
        $account_count = $database->count("Account", "ID='$account_id'");
        return $account_count ==1;
    }

    function account_exists($database, $name, $userID) {
        $account_count = $database->count("Account", "UserID='$userID' AND LOWER(Name)=LOWER('$name')");
        return $account_count > 0;
    }    
    
}
