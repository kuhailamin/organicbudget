<?php

include "Transfer.php";
include "Income.php";
include "TransactionItemList.php";

class TransactionList {
    
    public static $MAX_NUMBER_TRANSACTIONS_PER_MONTH = 300;
    public static $MAX_NUMBER_PAYEES_PER_COUNTRY=1000;

    function __construct() {
        
    }
    
    function edit_transfer($database, $transfer_id, $date_day, $date_month, $date_year, $time_hour, $time_minute, $time_zone_offset, $from_account_id, $to_account_id, $amount, $user_id) {
        $result = array();
        $date_array = $this->fix_date_time($date_year, $date_month, $date_day, $time_hour, $time_minute, $time_zone_offset);
        $date_string = $date_array["date_year"] . "-" . $date_array["date_month"] . "-" . $date_array["date_day"] . " " . $date_array["date_hour"] . ":" . $date_array["date_minute"] . ":00";
        $date_fixed = Utility::to_UTC_date_time($date_string, $time_zone_offset);
        $amount_valid = TransactionItemList::validate_amount($amount);
        $from_account_id_valid = $this->validate_account_id($database, $from_account_id, $user_id);
        $to_account_id_valid = $this->validate_account_id($database, $to_account_id, $user_id);
        $transfer_accounts_valid = $this->validate_transfer_accounts($database, $from_account_id, $to_account_id, $user_id);

        $result["transfer-amount"] = $amount_valid;
        $result["result"] = "fail";
        $result["date"] = $date_fixed;
        $result["from-account-id"] = $from_account_id_valid;
        $result["to-account-id"] = $to_account_id_valid;
        $result["transfer-accounts"] = $transfer_accounts_valid;
        $result["result"] = "fail";

        if ($amount_valid == "valid" && $from_account_id_valid == "valid" && $transfer_accounts_valid == "valid") {
            $values = array("FromAccount" => $from_account_id, "ToAccount" => $to_account_id, "Amount" => $amount,
                "Time" => $date_fixed);
            $final = $database->update("Transfer", $values, "ID='$transfer_id' AND UserID='$user_id'");
            if (!$final)
                return $result;
            $result["result"] = "success";
        }
        return $result;
    }    

    function add_transfer($database, $date_day, $date_month, $date_year, $time_hour, $time_minute, $time_zone_offset, $from_account_id, $to_account_id, $amount, $user_id) {
        $result = array();
        $date_array = $this->fix_date_time($date_year, $date_month, $date_day, $time_hour, $time_minute, $time_zone_offset);
        $date_string = $date_array["date_year"] . "-" . $date_array["date_month"] . "-" . $date_array["date_day"] . " " . $date_array["date_hour"] . ":" . $date_array["date_minute"] . ":00";
        $date_fixed = Utility::to_UTC_date_time($date_string, $time_zone_offset);
        $amount_valid = TransactionItemList::validate_amount($amount);
        $from_account_id_valid = $this->validate_account_id($database, $from_account_id, $user_id);
        $to_account_id_valid = $this->validate_account_id($database, $to_account_id, $user_id);
        $transfer_accounts_valid = $this->validate_transfer_accounts($database, $from_account_id, $to_account_id, $user_id);
        $max_num_transactions_valid=$this->validate_max_num_transactions($database, $user_id, $date_month, $date_year, $time_zone_offset);
        $result["error_message"]=$transfer_accounts_valid=="valid"?$max_num_transactions_valid=="valid"?"":$max_num_transactions_valid:$transfer_accounts_valid;
        
        $result["transfer-amount"] = $amount_valid;
        $result["result"] = "fail";
        $result["date"] = $date_fixed;
        $result["from-account-id"] = $from_account_id_valid;
        $result["to-account-id"] = $to_account_id_valid;
        $result["transfer-accounts"] = $transfer_accounts_valid;
        $result["result"] = "fail";

        if ($amount_valid == "valid" && $from_account_id_valid == "valid" && $transfer_accounts_valid == "valid" && $max_num_transactions_valid=="valid") {
            $values = array("FromAccount" => $from_account_id, "ToAccount" => $to_account_id, "Amount" => $amount,
                "UserID" => $user_id, "Time" => $date_fixed);
            $final = $database->insert("Transfer", $values);
            if (!$final)
                return $result;
            $result["result"] = "success";
            $result["id"] = $database->last_insert_id();
        }

        return $result;
    }

    function delete_transfer($database, $transfer_id,$user_id) {
        $result = array();
        $result["result"] = "fail";
        $transfer_valid = $this->validate_transfer_id($database, $transfer_id, $user_id);
        if($transfer_valid=="valid"){
            $final = $database->delete_where("Transfer","ID='$transfer_id' AND UserID='$user_id'");
            if(!$final)
                return $result;
        }
        $result["result"] = "success";
        return $result;
    }
    
    function delete_income($database, $income_id,$user_id) {
        $result = array();
        $result["result"] = "fail";
        $income_valid = $this->validate_income_id($database, $income_id, $user_id);
        if($income_valid=="valid"){
            $final = $database->delete_where("Income","ID='$income_id' AND UserID='$user_id'");
            if(!$final)
                return $result;
        }
        $result["result"] = "success";
        return $result;
    }     
    
    function edit_income($database, $income_id,$date_day, $date_month, $date_year, $time_hour, $time_minute, $time_zone_offset, $account_id, $category_name, $item_name, $amount, $category_type, $lang, $user_id) {
        $result = array();
        $date_array = $this->fix_date_time($date_year, $date_month, $date_day, $time_hour, $time_minute, $time_zone_offset);
        $date_string = $date_array["date_year"] . "-" . $date_array["date_month"] . "-" . $date_array["date_day"] . " " . $date_array["date_hour"] . ":" . $date_array["date_minute"] . ":00";
        $date_fixed = Utility::to_UTC_date_time($date_string, $time_zone_offset);
        $amount_valid = TransactionItemList::validate_amount($amount);
        $category_type = TransactionItemList::fix_category_type($category_type);
        $lang = Utility::fix_language($lang);
        $item_info = TransactionItemList::get_item_info($database, $item_name, $category_name, $category_type, $user_id);
        $item_id = $item_info["item_id"];
        $category_id = $item_info["category_id"];
        $item_valid = $item_id != -2 || $category_id != -2 ? "valid" : "Item or Category doesn't exist";
        if ($item_valid == "valid" && $category_id == -1) { //category name is given, but it wasn't found
            $category_id = TransactionItemList::create_category($database, $category_name, $user_id, $category_type);
            $item_id = TransactionItemList::create_item($database, $item_name, $category_id);
        } else if ($item_valid == "valid" && $category_id == -2) { //category name is not given, but item is given
            $category_id = TransactionItemList::get_category_id($database, Utility::get_default_category_name($lang), $user_id, $category_type);
            if ($category_id == -1) //it doesn't exist, create it
                $category_id = TransactionItemList::create_category($database, Utility::get_default_category_name($lang), $user_id, $category_type);
            $item_id = TransactionItemList::create_item($database, $item_name, $category_id);
        }
        else if ($item_valid == "valid" && $category_id != -1 && $category_id != -2 && $item_id == -1) { //category found, item given but not found
            $item_id = TransactionItemList::create_item($database, $item_name, $category_id);
        }

        $result["item_id"] = $item_id;
        $result["category_id"] = $category_id;
        $result["income-source"] = $item_valid;
        $result["income-amount"] = $amount_valid;
        $result["result"] = "fail";
        $result["date"] = $date_fixed;

        $account_id_valid = $this->validate_account_id($database, $account_id, $user_id);

        $result["account-id"] = $account_id_valid;
        $result["result"] = "fail";
        if ($item_valid == "valid" && $amount_valid == "valid" && $account_id_valid == "valid") {
            $values = array("CategoryID" => $category_id, "ItemID" => $item_id, "Amount" => $amount,
                "AccountID" => $account_id, "Time" => $date_fixed);
            $final = $database->update("Income", $values, "ID='$income_id' AND UserID='$user_id'");
            if (!$final)
                return $result;
            $result["result"] = "success";
        }

        return $result;
    }    

    function add_income($database, $date_day, $date_month, $date_year, $time_hour, $time_minute, $time_zone_offset, $account_id, $category_name, $item_name, $amount, $category_type, $lang, $user_id) {
        $result = array();
        $date_array = $this->fix_date_time($date_year, $date_month, $date_day, $time_hour, $time_minute, $time_zone_offset);
        $date_string = $date_array["date_year"] . "-" . $date_array["date_month"] . "-" . $date_array["date_day"] . " " . $date_array["date_hour"] . ":" . $date_array["date_minute"] . ":00";
        $date_fixed = Utility::to_UTC_date_time($date_string, $time_zone_offset);
        $amount_valid = TransactionItemList::validate_amount($amount);
        $max_num_transactions_valid=$this->validate_max_num_transactions($database, $user_id, $date_month, $date_year, $time_zone_offset);
        $result["error_message"]=$max_num_transactions_valid=="valid"?"":$max_num_transactions_valid;
                
        $category_type = TransactionItemList::fix_category_type($category_type);
        $lang = Utility::fix_language($lang);
        $item_info = TransactionItemList::get_item_info($database, $item_name, $category_name, $category_type, $user_id);
        $item_id = $item_info["item_id"];
        $category_id = $item_info["category_id"];
        $item_valid = $item_id != -2 || $category_id != -2 ? "valid" : "Item or Category doesn't exist";
        if ($item_valid == "valid" && $category_id == -1) { //category name is given, but it wasn't found
            $category_id = TransactionItemList::create_category($database, $category_name, $user_id, $category_type);
            $item_id = TransactionItemList::create_item($database, $item_name, $category_id);
        } else if ($item_valid == "valid" && $category_id == -2) { //category name is not given, but item is given
            $category_id = TransactionItemList::get_category_id($database, Utility::get_default_category_name($lang), $user_id, $category_type);
            if ($category_id == -1) //it doesn't exist, create it
                $category_id = TransactionItemList::create_category($database, Utility::get_default_category_name($lang), $user_id, $category_type);
            $item_id = TransactionItemList::create_item($database, $item_name, $category_id);
        }
        else if ($item_valid == "valid" && $category_id != -1 && $category_id != -2 && $item_id == -1) { //category found, item given but not found
            $item_id = TransactionItemList::create_item($database, $item_name, $category_id);
        }

        $result["item_id"] = $item_id;
        $result["category_id"] = $category_id;
        $result["income-source"] = $item_valid;
        $result["income-amount"] = $amount_valid;
        $result["result"] = "fail";
        $result["date"] = $date_fixed;

        $account_id_valid = $this->validate_account_id($database, $account_id, $user_id);

        $result["account-id"] = $account_id_valid;
        $result["result"] = "fail";
        if ($item_valid == "valid" && $amount_valid == "valid" && $account_id_valid == "valid" && $max_num_transactions_valid=="valid") {
            $values = array("CategoryID" => $category_id, "ItemID" => $item_id, "Amount" => $amount,
                "AccountID" => $account_id, "UserID" => $user_id, "Time" => $date_fixed);
            $final = $database->insert("Income", $values);
            if (!$final)
                return $result;
            $result["result"] = "success";
            $result["id"] = $database->last_insert_id();
        }

        return $result;
    }
    
    function delete_transaction_item($database, $transaction_item_id,$transaction_id,$user_id) {
        $result = array();
        $result["result"] = "fail";
        $transaction_valid = $this->validate_transaction_id($database, $transaction_id, $user_id);
        if($transaction_valid=="valid"){
            $final = $database->delete_where("TransactionItem","TransactionID='$transaction_id' AND ID='$transaction_item_id'");
            if(!$final)
                return $result;
        }
        $result["result"] = "success";
        return $result;
    }     
    
    function delete_transaction($database, $transaction_id,$user_id) {
        $result = array();
        $result["result"] = "fail";
        $transaction_valid = $this->validate_transaction_id($database, $transaction_id, $user_id);
        if($transaction_valid=="valid"){
            $final = $database->delete_where("TransactionItem","TransactionID='$transaction_id'");
            $final = $database->delete_where("Transaction","ID='$transaction_id' AND UserID='$user_id'");
            if(!$final)
                return $result;
        }
        $result["result"] = "success";
        return $result;
    }    

    function edit_transaction($database, $transaction_id, $payee, $date_day, $date_month, $date_year, $time_hour, $time_minute, $time_zone_offset, $account_id, $user_id,$transaction_item_ids,$item_names, $category_names, $item_amounts, $quantities,$indices,$category_type, $lang) {
        $result = array();
        $date_array = $this->fix_date_time($date_year, $date_month, $date_day, $time_hour, $time_minute, $time_zone_offset);
        $date_string = $date_array["date_year"] . "-" . $date_array["date_month"] . "-" . $date_array["date_day"] . " " . $date_array["date_hour"] . ":" . $date_array["date_minute"] . ":00";
        $date_fixed = Utility::to_UTC_date_time($date_string, $time_zone_offset);
        $result["date"] = $date_fixed;

        $payee_valid = $this->validate_payee_name($payee);
        $payee_id=$this->get_payee_id($database, $payee, $user_id,$payee_valid);
        $account_id_valid = $this->validate_account_id($database, $account_id, $user_id);
        $transaction_valid = $this->validate_transaction_id($database, $transaction_id, $user_id);

        $result["transaction-account"] = $account_id_valid;
        $result["transaction-payee"] = $payee_valid;
        $result["result"] = "fail";

        if ($transaction_valid == "valid" && $payee_valid == "valid" && $account_id_valid == "valid") {
            $values = array("Payee" => $payee, "Time" => $date_fixed, "AccountID" => $account_id, "PayeeID"=>$payee_id);
            $final = $database->update("Transaction", $values, "ID='$transaction_id' AND UserID='$user_id'");
            if (!$final)
                return $result;
            $result["result"] = "success";
            $transaction_item_list = new TransactionItemList();
            $result["items"] = $transaction_item_list->edit_transaction_items($database, $transaction_id, $user_id, $transaction_item_ids,$item_names, $category_names, $item_amounts, $quantities,$indices,$category_type, $lang);
        }
        return $result;
    }

    function add_transaction($database, $payee, $date_day, $date_month, $date_year, $time_hour, $time_minute, $time_zone_offset, $account_id, $item_names, $category_names, $amounts, $quantities,$indices,$user_id, $category_type, $lang) {
        $result = array();
        $date_array = $this->fix_date_time($date_year, $date_month, $date_day, $time_hour, $time_minute, $time_zone_offset);
        $date_string = $date_array["date_year"] . "-" . $date_array["date_month"] . "-" . $date_array["date_day"] . " " . $date_array["date_hour"] . ":" . $date_array["date_minute"] . ":00";
        $date_fixed = Utility::to_UTC_date_time($date_string, $time_zone_offset);
        $result["date"] = $date_fixed;

        $payee_valid = $this->validate_payee_name($payee);
        $payee_id=$this->get_payee_id($database, $payee, $user_id,$payee_valid);
        $account_id_valid = $this->validate_account_id($database, $account_id, $user_id);

        $result["account-id"] = $account_id_valid;
        $result["transaction-payee"] = $payee_valid;
        $result["result"] = "fail";
        $max_num_transactions_valid=$this->validate_max_num_transactions($database, $user_id, $date_month, $date_year, $time_zone_offset);
        $result["error_message"]=$max_num_transactions_valid=="valid"?"":$max_num_transactions_valid;
        
        if ($payee_valid == "valid" && $account_id_valid == "valid" && $max_num_transactions_valid=="valid") {
            $values = array("Payee" => $payee, "Time" => $date_fixed, "UserID" => $user_id, "AccountID" => $account_id, "PayeeID"=>$payee_id);
            $final = $database->insert("Transaction", $values);
            if (!$final)
                return $result;
            $result["result"] = "success";
            $transaction_id = $database->last_insert_id();
            $result["id"] = $transaction_id;
            $transaction_item_list = new TransactionItemList();
            $result["items"] = $transaction_item_list->add_transaction_items($database, $transaction_id, $user_id, $item_names, $category_names, $amounts, $quantities,$indices,$category_type, $lang);
        }

        return $result;
    }
    
    function validate_max_num_transactions($database,$user_id,$date_month,$date_year,$time_zone_offset) {
        $total_count=0;
        $start_date_string="$date_year-$date_month-1 00:00:00";
        $start_date_UTC=Utility::to_UTC_date_time($start_date_string,$time_zone_offset);
        $next_month=++$date_month;
        $next_year=$date_year;
        if($next_month>12){
            $next_month=1;
            ++$next_year;
        }
        $end_date_string="$next_year-$next_month-1 00:00:00";
        $end_date_UTC=Utility::to_UTC_date_time($end_date_string,$time_zone_offset);
        
        $total_count+= $database->count("Transaction", "UserID='$user_id' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC'");
        $total_count+= $database->count("Transfer", "UserID='$user_id' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC'");
        $total_count+= $database->count("Income", "UserID='$user_id' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC'");
         
        if($total_count>=TransactionList::$MAX_NUMBER_TRANSACTIONS_PER_MONTH){
            return "You can't add more than ".TransactionList::$MAX_NUMBER_TRANSACTIONS_PER_MONTH." transactions per month";
        }
        return "valid";
    }     
    
    function validate_transfer_id($database, $transfer_id, $user_id) {
        $transfer_count = $database->count("Transfer", "UserID='$user_id' AND ID='$transfer_id'");
        if ($transfer_count == 1)
            return "valid";
        return "invalid";
    }     
    
    function validate_income_id($database, $income_id, $user_id) {
        $income_count = $database->count("Income", "UserID='$user_id' AND ID='$income_id'");
        if ($income_count == 1)
            return "valid";
        return "invalid";
    }    

    function validate_transaction_id($database, $transaction_id, $user_id) {
        $transaction_count = $database->count("Transaction", "UserID='$user_id' AND ID='$transaction_id'");
        if ($transaction_count > 0)
            return "valid";
        return "invalid";
    }

    function validate_payee_name($payee) {
        $payee = trim($payee);
        if (strlen($payee) == 0)
            return "Payee is required";
        if (strlen($payee) > 30)
            return "Payee name can't be longer than 30 characters";
        return "valid";
    }
    
    function get_payee_id($database,$payee,$user_id,$payee_valid="valid") {
        if($payee_valid!="valid")
            return -1;
        $payee = trim($payee);
        if($payee=="")
            return -1;
        $payee_id=-1;
        $current_country_code=$_SESSION["user"]->get_current_country_code();
        $result=$database->select_fields_where("Payee", "ID", "LCase(Name)='".strtolower($payee)."' AND Country='$current_country_code' AND UserID='$user_id'");
        if(!$result)
            return $payee_id;
        $row_count = mysqli_num_rows($result);
        if($row_count==1){
            $row = mysqli_fetch_array($result);
            $payee_id=$row["ID"];
            return $payee_id;
        }
        $payee_count = $database->count("Payee", "UserID='$user_id' AND Country='$current_country_code'");
        if($payee_count>=TransactionList::$MAX_NUMBER_PAYEES_PER_COUNTRY)
            return $payee_id;
        
        $values = array("Name" => $payee, "Country" => $current_country_code, "UserID" => $user_id);
        $final = $database->insert("Payee", $values);
            if (!$final)
                return $payee_id;
        $payee_id=$database->last_insert_id();
        return $payee_id;
    }

    function validate_account_id($database, $account_id, $user_id) {
        $account_count = $database->count("Account", "ID='$account_id' AND UserID='$user_id'");
        if ($account_count == 1)
            return "valid";
        return "Account doesn't exist";
    }

    function validate_transfer_accounts($database, $from_account_id, $to_account_id, $user_id) {
        $error_message = "";
        if ($from_account_id == $to_account_id)
            return "The two accounts must be different";
        $from_result = $database->select_fields_where("Account", "CurrencyID", "UserID='$user_id' AND ID='$from_account_id'");
        if (!$from_result)
            return $error_message;
        $from_row = mysqli_fetch_assoc($from_result);
        $from_currency_id = $from_row["CurrencyID"];

        $to_result = $database->select_fields_where("Account", "CurrencyID", "UserID='$user_id' AND ID='$to_account_id'");
        if (!$to_result)
            return $error_message;
        $to_row = mysqli_fetch_assoc($to_result);
        $to_currency_id = $to_row["CurrencyID"];

        if ($from_currency_id != $to_currency_id)
            return "The accounts must have the same currency";
        return "valid";
    }

    function fix_date_time($date_year, $date_month, $date_day, $hour, $minute, $time_zone_offset) {
        $current_date = getdate();
        $date_array = array("date_year" => $date_year, "date_month" => $date_month, "date_day" => $date_day, "date_hour" => $hour, "date_minute" => $minute, "time_zone_offset" => $time_zone_offset);
        if (!checkdate($date_month, $date_day, $date_year)) {
            $date_array["date_year"] = $current_date["year"];
            $date_array["date_month"] = $current_date["mon"];
            $date_array["date_day"] = $current_date["mday"];
            $date_array["time_zone"] = date_default_timezone_get();
            $date_array["date_hour"] = $current_date["hours"];
            $date_array["date_minute"] = $current_date["minutes"];
            $date_array["time_zone_offset"] = "0";
        } else if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            $date_array["date_hour"] = $current_date["hours"];
            $date_array["date_minute"] = $current_date["minutes"];
            $date_array["time_zone_offset"] = "0";
        }

        if (intval($date_array["date_minute"]) < 10) { //it is important for the minute component to have two digits
            $date_array["date_minute"] = "0" . $date_array["date_minute"];
        }

        return $date_array;
    }
    
    function get_item_suggestions_from_payee($database, $payee, $user_id) {
        $final=array();
        $payee_id=$this->get_payee_id($database, $payee, $user_id);  
        if($payee_id!=-1){
            $SQL=" SELECT Item.ID AS 'item_id',Category.ID AS 'category_id', Item.Name AS 'item_name', Category.Name AS 'category_name', Category.Color AS 'category_color'";
            $SQL.="   FROM (SELECT * FROM Transaction WHERE Transaction.UserID='$user_id' AND Transaction.PayeeID='$payee_id' Order BY Transaction.Time DESC) AS TransactionTable";
            $SQL.=" INNER JOIN TransactionItem ";
            $SQL.=" ON TransactionItem.TransactionID=TransactionTable.ID";
            $SQL.=" INNER JOIN Category";
            $SQL.=" ON TransactionItem.CategoryID=Category.ID";
            $SQL.=" INNER JOIN Item";
            $SQL.=" ON TransactionItem.ItemID=Item.ID";
            $SQL.=" GROUP BY Item.ID";
            $SQL.=" Order By TransactionTable.Time DESC";
            $SQL.=" LIMIT 5";

            $result=$database->send_SQL($SQL);
            if(!$result)
                return $final;

        while ($row = mysqli_fetch_assoc($result)) {
            $item_name=empty($row["item_name"]) || is_null($row["item_name"])?$row["category_name"]:$row["item_name"];
            $item = array("item_id" => $row["item_id"], "category_id" => $row["category_id"], "item_name" => ucfirst($item_name), "category_name" => ucfirst($row["category_name"]), "category_color" => $row["category_color"]);
            $final[] = $item;
        }
        
    }
    return $final;
    }
    function get_payees($database, $user_id, $country_code) {
        $final=array();
        $SQL = "SELECT Name ";
        $SQL.=" FROM Payee ";
        $SQL.=" WHERE UserID='$user_id' AND Country='$country_code'";
        $SQL.=" ORDER BY Name";
        $result = $database->send_SQL($SQL);
        if(!$result)
            return $final;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $item=array("payee_name"=>ucfirst($row["Name"]));
            $final[] = $item;       
        }
        return $final;   
    }    

    function get_payee_suggestions($database, $payee, $user_id, $country_code) {
        $final=array();
        $SQL = "SELECT Name ";
        $SQL.=" FROM Payee ";
        $SQL.=" WHERE UserID='$user_id' AND Country='$country_code' AND LCASE(Name) LIKE '%$payee%'";
        $SQL.=" ORDER BY CASE WHEN LCASE(Name) LIKE '$payee%' THEN 1 WHEN LCASE(Name) LIKE '%$payee' THEN 3 ELSE 2 END LIMIT 5";
        $result = $database->send_SQL($SQL);
        if(!$result)
            return $final;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $item=array("payee_name"=>ucfirst($row["Name"]));
            $final[] = $item;       
        }
        return $final;   
    }

    function get_item_suggestions($database, $text, $user_id, $type) {
        $item_found=false;
        $category_found=false;
        $category_color="";
        $category_id="";
        $category_name="";
        $array = array();
        $text = trim(strtolower($text));
        if (strpos($text, "(") !== false){
            $item_text=trim(strstr($text, '(',true));
            $category_text = strstr($text, '(');
            $category_text=str_replace("(","",$category_text );
            $category_text=str_replace(")","",$category_text ); 
            $category_text=trim($category_text);
        }
        else{
            $item_text=$text;
            $category_text=$text;
        }

        $SQL = "SELECT Category.Color AS 'CategoryColor', Category.ID AS 'CategoryID', Category.Name AS 'CategoryName' ";
        $SQL.=" FROM Category ";
        $SQL.=" WHERE Category.UserID='$user_id' AND Type='$type' AND LCASE(Name) LIKE '%$category_text%'";
        $SQL.=" ORDER BY CASE WHEN LCASE(Name) LIKE '$category_text%' THEN 1 WHEN LCASE(Name) LIKE '%$category_text' THEN 3 ELSE 2 END LIMIT 3";
        $result = $database->send_SQL($SQL);

        if (!$result) {/* do nothing */
        }

        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $item = array("item_id" => -1, "category_id" => $row["CategoryID"],
                "item_name" => ucfirst($row["CategoryName"]), "category_name" => ucfirst($row["CategoryName"]), "category_color" => $row["CategoryColor"]);
            $array[] = $item;
            $category_name_row=strtolower(trim($row["CategoryName"]));
            if(Utility::contains($category_name_row, $category_text)){
                $category_found=true;
                $category_color=$row["CategoryColor"];
                $category_id=$row["CategoryID"];
                $category_name=$row["CategoryName"];
            }
            if($category_found && Utility::contains($category_name_row, $item_text))
                $item_found=true;
        }

        $SQL = "SELECT Category.Color AS 'CategoryColor', Item.ID AS 'ItemID', Category.ID AS 'CategoryID', Item.Name AS 'ItemName', Category.Name AS 'CategoryName' ";
        $SQL.=" FROM Category ";
        $SQL.=" INNER JOIN Item ";
        $SQL.=" ON Item.CategoryID=Category.ID ";
        $SQL.=" WHERE Category.UserID='$user_id' AND Type='$type' AND ( LCASE(Category.Name) LIKE '%$category_text%' OR LCASE(Item.Name) LIKE '%$item_text%')";
        $SQL.=" ORDER BY CASE WHEN LCASE(Item.Name) LIKE '$item_text%' THEN 1 WHEN LCASE(Item.Name) LIKE '%$item_text' THEN 3 ELSE 2 END LIMIT 4";
        $result = $database->send_SQL($SQL);

        if (!$result)
            return $array;

        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $item_name_row=strtolower(trim($row["ItemName"]));
            $category_name_row=strtolower(trim($row["CategoryName"]));
            if (trim($row["ItemName"]) != trim($row["CategoryName"])) { //don't add the records where the item name is equal to the category name. This has been added before
                //if item name == category name, there is no need to add the item, it has been added before
                if(strtolower($row["ItemName"])!==strtolower($row["CategoryName"])){
                    $item = array("item_id" => $row["ItemID"], "category_id" => $row["CategoryID"], "item_name" => ucfirst($row["ItemName"]), "category_name" => ucfirst($row["CategoryName"]), "category_color" => $row["CategoryColor"]);
                    $array[] = $item;
                    if(Utility::contains($category_name_row, $category_text) && Utility::contains($item_name_row, $item_text))
                        $item_found=true;
                }
            }
        }
        if($category_found && !$item_found && trim($item_text)!=""){
             $item = array("item_id" => -1, "category_id" => $category_id, "item_name" => ucfirst(trim($item_text)), "category_name" => ucfirst($category_name), "category_color" => $category_color);
            array_unshift($array,$item);
        }
        return $array;
    }

    function get_user_accounts($database, $user_id) {
        $result = $database->inner_join_SQL("Currency", "Account", "Account.ID, Account.Name, Currency.Abbreviation", "Currency.ID=Account.CurrencyID", "UserID='$user_id'");
        $array = array();

        if (!$result)
            return $array;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $item = array("id" => $row["ID"], "name" => $row["Name"], "currency" => $row["Abbreviation"]);
            $array[] = $item;
        }
        return $array;
    }

    function get_transaction($database, $month, $year, $user_ID, $number_format, $time_format, $date_format, $transaction_type, $time_zone_offset, $id,$transaction_account) {
        $array = array();
        $start_date = "$year-$month-1 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);
        $next_month_date = Utility::get_next_month($month, $year);
        $end_date = $next_month_date["year"] . "-" . $next_month_date["month"] . "-1 00:00:00";
        $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);
        $transaction_account_where=$transaction_account=="all"?"":" AND AccountID='$transaction_account'";
        $transfer_account_where=$transaction_account=="all"?"":" AND (FromAccount='$transaction_account' OR ToAccount='$transaction_account')";

        if ($transaction_type == "transactions" || $transaction_type == "all") {
            $result = $database->select_fields_where("Transaction", "*", "UserID='$user_ID' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC' AND ID='$id' $transaction_account_where");

            $row_count = mysqli_num_rows($result);
            for ($j = 0; $j < $row_count; ++$j) {
                $row = mysqli_fetch_array($result);
                $transaction = new Transaction($row["ID"], $row["Payee"], $row["Time"], $row["AccountID"]);
                $array[] = $transaction->to_array($database, $number_format, $time_format, $date_format, $user_ID, $time_zone_offset);
            }
        }

        //getting the transfers
        if ($transaction_type == "transfer" || $transaction_type == "all") {
            $result = $database->select_fields_where("Transfer", "*", "UserID='$user_ID' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC' AND ID='$id' $transfer_account_where", "ORDER BY Time DESC");
            $row_count = mysqli_num_rows($result);
            for ($j = 0; $j < $row_count; ++$j) {
                $row = mysqli_fetch_array($result);
                $transfer = new Transfer($row["ID"], $row["Time"], $row["FromAccount"], $row["ToAccount"], $row["Amount"]);
                $array[] = $transfer->to_array($database, $number_format, $time_format, $date_format, $user_ID, $time_zone_offset);
            }
        }
        //getting the income transactions
        if ($transaction_type == "income" || $transaction_type == "all") {
            $result = $database->select_fields_where("Income", "*", "UserID='$user_ID' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC' AND ID='$id' $transaction_account_where", "ORDER BY Time DESC");
            $row_count = mysqli_num_rows($result);
            for ($j = 0; $j < $row_count; ++$j) {
                $row = mysqli_fetch_array($result);
                $income = new Income($row["ID"], $row["Time"], $row["CategoryID"], $row["ItemID"], $row["AccountID"], $row["Amount"]);
                $array[] = $income->to_array($database, $number_format, $time_format, $date_format, $user_ID, $time_zone_offset);
            }
        }

        return $array;
    }

    function get_transactions($database, $month, $year, $user_ID, $number_format, $time_format, $date_format, $transaction_type, $time_zone_offset,$transaction_account) {
        $array = array();
        $start_date = "$year-$month-1 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);
        $next_month_date = Utility::get_next_month($month, $year);
        $end_date = $next_month_date["year"] . "-" . $next_month_date["month"] . "-1 00:00:00";
        $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);
        $transaction_account_where=$transaction_account=="all"?"":" AND AccountID='$transaction_account'";
        $transfer_account_where=$transaction_account=="all"?"":" AND (FromAccount='$transaction_account' OR ToAccount='$transaction_account')";

        if ($transaction_type == "transactions" || $transaction_type == "all") {
            //$result = $database->select_fields_where("Transaction", "*", "UserID='$user_ID' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC' $transaction_account_where", "ORDER BY Time DESC");
            $result=$database->inner_join_SQL("Transaction","Payee", "Transaction.ID, Transaction.Time AS 'transaction_time', Transaction.AccountID AS 'account_id' , Payee.Name AS 'payee_name'", "Transaction.PayeeID=Payee.ID", "Transaction.UserID='$user_ID' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC' $transaction_account_where","ORDER BY Time DESC");
            $row_count = mysqli_num_rows($result);
            for ($j = 0; $j < $row_count; ++$j) {
                $row = mysqli_fetch_array($result);
                //$transaction = new Transaction($row["ID"], $row["Payee"], $row["Time"], $row["AccountID"]);
                $transaction = new Transaction($row["ID"], $row["payee_name"], $row["transaction_time"], $row["account_id"]);
                $array[] = $transaction->to_array($database, $number_format, $time_format, $date_format, $user_ID, $time_zone_offset);
            }
        }

        //getting the transfers
        if ($transaction_type == "transfer" || $transaction_type == "all") {
            $result = $database->select_fields_where("Transfer", "*", "UserID='$user_ID' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC' $transfer_account_where", "ORDER BY Time DESC");
            $row_count = mysqli_num_rows($result);
            for ($j = 0; $j < $row_count; ++$j) {
                $row = mysqli_fetch_array($result);
                $transfer = new Transfer($row["ID"], $row["Time"], $row["FromAccount"], $row["ToAccount"], $row["Amount"]);
                $array[] = $transfer->to_array($database, $number_format, $time_format, $date_format, $user_ID, $time_zone_offset);
            }
        }
        //getting the income transactions
        if ($transaction_type == "income" || $transaction_type == "all") {
            $result = $database->select_fields_where("Income", "*", "UserID='$user_ID' AND Time>='$start_date_UTC' AND Time<'$end_date_UTC' $transaction_account_where", "ORDER BY Time DESC");
            $row_count = mysqli_num_rows($result);
            for ($j = 0; $j < $row_count; ++$j) {
                $row = mysqli_fetch_array($result);
                $income = new Income($row["ID"], $row["Time"], $row["CategoryID"], $row["ItemID"], $row["AccountID"], $row["Amount"]);
                $array[] = $income->to_array($database, $number_format, $time_format, $date_format, $user_ID, $time_zone_offset);
            }
        }

        return $array;
    }

}
