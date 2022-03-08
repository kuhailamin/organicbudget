<?php

include "Budget.php";
include "TargetList.php";


class BudgetList {
    
    public static $MAX_NUMBER_BUDGETS = 10;
    public static $MIN_NUMBER_BUDGETS = 1;

    function __construct() {
        
    }
    
    function add_budget($database, $name, $start_month,$start_year,$end_month, $end_year, $userID,$target_category_ids,
            $target_amounts,$target_currency_ids,$target_periods,$target_everys,$rollovers,$indices){
        
        $result = array();
        $name=trim(strtolower($name));
        $name_valid = $this->validate_budget_name($database, $name, $userID);
        $max_num_budgets_valid=$this->validate_max_num_budgets($database, $userID);
        $dates= Budget::fixDates($start_month,$start_year, $end_month,$end_year);
        
        $result["error_message"]=$max_num_budgets_valid=="valid"?"":$max_num_budgets_valid;
        $result["budget-name"] = $name_valid;
        $result["budget-start-month"] = "valid";$result["budget-end-month"] = "valid";
        $result["result"] = "fail";
        if ($name_valid == "valid" && $max_num_budgets_valid=="valid") {
            $values = array("Name" => $name,"Start" => $dates["Start"], "End" => $dates["End"], "UserID" => $userID);
            $final = $database->insert("Budget", $values);
            if(!$final)
                return $result;
            $result["result"] = "success";
            $id=$database->last_insert_id();   
            $result["id"]=$id;
            $target_list=new TargetList();
            $result["targets"]=$target_list->add_targets($database, $id, $userID, $target_category_ids, $target_amounts, $target_currency_ids, $target_periods, $target_everys,$rollovers,$indices);
        }
        return $result;
    }
    
    function edit_budget($database,$id,$name, $start_month, $start_year, $end_month, $end_year,$userID, $target_ids,$target_category_ids,
            $target_amounts,$target_currency_ids,$target_periods,$target_everys,$target_rollovers,$indices) {
        $result = array();
        $name=trim(strtolower($name));
        $name_valid = $this->validate_budget_edit_name($database, $name, $userID,$id);
        $dates= Budget::fixDates($start_month,$start_year, $end_month,$end_year);
        
        $result["result"] = "fail";
        
        if ($name_valid == "valid") {
            $values = array("Name" => $name,"Start" => $dates["Start"], "End" => $dates["End"], "UserID" => $userID);
            $final = $database->update("Budget", $values, "ID='$id'");
            if(!$final)
                return $result;
            
            $budget = new Budget($id,$name,"",$dates["Start"],$dates["End"]);
            $result=$budget->to_array($database); 

            $result["result"] = "success";
            $target_list=new TargetList();
            $result["targets"]=$target_list->edit_targets($database, $id, $userID, $target_ids, $target_category_ids, $target_amounts, $target_currency_ids, $target_periods, $target_everys, $target_rollovers, $indices);
        }
        
        $result["budget-name"] = $name_valid;$result["budget-start-month"] = "valid";$result["budget-end-month"] = "valid";
        return $result;
    }    
    
     function delete_budget($database, $id,$userID) {
        $result = array();
        $result["result"] = "fail";
        if(!$this->budget_id_exists($database, $id, $userID)) //the budget doesn't belong to the user
                return $result;
        $min_num_budgets_valid=$this->validate_min_num_budgets($database, $userID);
        $result["error_message"]=$min_num_budgets_valid=="valid"?"":$min_num_budgets_valid;
        
        if($min_num_budgets_valid=="valid"){
            $final = Budget::delete_all_targets($database, $id);
            $final = $database->delete_where("Budget","ID='$id' AND UserID='$userID'");
            if(!$final)
                return $result;
            $result["result"] = "success";
        }
        return $result;
    }      
    
    function get_user_currencies($database,$user_id,$user_currency_id){
        $SQL="SELECT Currency.ID,Currency.Symbol FROM Currency INNER JOIN Account ON Currency.ID=Account.CurrencyID ";
        $SQL.="WHERE Account.UserID='$user_id' ";
        $SQL.="UNION ";
        $SQL.=" SELECT Currency.ID,Currency.Symbol FROM Currency INNER JOIN Target ON Currency.ID=Target.CurrencyID INNER JOIN Budget ON Target.BudgetID=Budget.ID ";
        $SQL.=" WHERE Budget.UserID='$user_id'";
        
        $result = $database->send_SQL($SQL);
        
        //$result=$database->distinct_inner_join_SQL("Currency","Account","Currency.ID, Currency.Symbol","Currency.ID=Account.CurrencyID","UserID='$user_id'");
        $array = array();
        $array[]=array("user_currency_id"=>$user_currency_id);
        
        if (!$result)
            return $array;
        while($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $item=array("id"=>$row["ID"],"symbol"=>$row["Symbol"]);
            $array[]=$item;           
        }
        return $array;           
    }
    
    function get_user_categories($database,$user_id){
        $result=$database->select_fields_where("Category", "ID,Name","UserID='$user_id' AND Type='e'", "ORDER BY Name");
        $array = array();
        
        if (!$result)
            return $array;
        while($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $item=array("id"=>$row["ID"],"name"=>$row["Name"]);
            $array[]=$item;           
        }
        return $array;           
    }
    
    function get_income_categories($database,$user_id){
        $result=$database->select_fields_where("Category", "ID,Name","UserID='$user_id' AND Type='i'", "ORDER BY Name");
        $array = array();
        
        if (!$result)
            return $array;
        while($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $item=array("id"=>$row["ID"],"name"=>$row["Name"]);
            $array[]=$item;           
        }
        return $array;           
    }    
    
    function get_budget($database,$id,$user_id,$user_currency_id,$number_format,$order_by="Start") {
        
        return $this->get_budgets_condition($database, "UserID='$user_id' AND ID='$id'", $user_id, $user_currency_id, $number_format, $order_by);
    }

    function get_budgets($database,$user_id,$user_currency_id,$number_format,$order_by="Start") {    
        
        return $this->get_budgets_condition($database, "UserID='$user_id'", $user_id, $user_currency_id, $number_format, $order_by);
    }
    
    function get_budgets_condition($database,$condition,$user_id,$user_currency_id,$number_format,$order_by="Start") {
        $result = $database->select_fields_where("Budget", "*",$condition, "ORDER BY $order_by DESC");
        $array = array();
        if (!$result)
            return $array;
        
        $default_total_budget=  Utility::get_formatted_money($user_currency_id, 0, $number_format);

        
        while($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $budget = new Budget($row["ID"],$row["Name"],$row["Description"],$row["Start"],$row["End"]);
            $item=$budget->to_array($database); 
            $item["budget_total"]=$default_total_budget;
            $target_list=new TargetList();
            $item["targets"]=$target_list->get_targets($database, $number_format, $row["ID"], $user_currency_id);
            $array[]=$item;           
        }
        return $array;        
    }
    
    function budget_id_exists($database, $id, $userID) {
        $budget_count = $database->count("Budget", "ID='$id' AND UserID='$userID'");
        return $budget_count > 0;
    }  
    
    function budget_exists($database, $name, $userID) {
        $budget_count = $database->count("Budget", "UserID='$userID' AND Name='$name'");
        return $budget_count > 0;
    }
    
    function validate_max_num_budgets($database,$user_id) {
        $budget_count = $database->count("Budget", "UserID='$user_id'");
        if($budget_count>=  BudgetList::$MAX_NUMBER_BUDGETS){
            return "You can't add more than ".BudgetList::$MAX_NUMBER_BUDGETS." budgets";
        }
        return "valid";
    }
    
    function validate_min_num_budgets($database,$user_id) {
        $budget_count = $database->count("Budget", "UserID='$user_id'");
        if($budget_count<=BudgetList::$MIN_NUMBER_BUDGETS){
            return "You have to have at least ".BudgetList::$MIN_NUMBER_BUDGETS." budgets";
        }
        return "valid";
    }      
    
    function validate_budget_edit_name($database, $name, $userID,$budget_id) {
        $account_count = $database->count("Budget", "ID='$budget_id' AND LOWER(Name)=LOWER('$name') AND UserID='$userID'");
        if($account_count==1)//same budget as before
          return "valid";
        return $this->validate_budget_name($database, $name, $userID);
    }  
    
    function validate_budget_name($database, $name, $userID) {
        if ($name == "")
            return "Name is required";
        if ($this->budget_exists($database, $name, $userID))
            return "Budget name exists already";
        if (strlen($name) > 30)
            return "Name can't be longer than 30 characters";
        return "valid";
    }    

}

?>
