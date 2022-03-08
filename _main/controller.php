<?php

include "sanitization.php";
include "login.php";
include "../_classes/Utility.php";
include "../_classes/database.php";
include "../_classes/CategoryList.php";
include "../_classes/BudgetList.php";
include "../_classes/ItemList.php";
include "../_classes/TransactionList.php";
include "../_classes/User.php";
include "../_classes/AccountList.php";
include "../_classes/Analytics.php";
include "../_classes/Settings.php";

//session_start();
$database = new database($db_hostname, $db_database, $db_username, $db_password); //establish connection
$connection = $database->get_connection();

if (isset($_POST["type"])) {
    $type = sanitizeMYSQL($connection, $_POST["type"]);
    $returned_value = $type; //default value
    if($type!="login")
        session_start();

    switch ($type) { 
        case "stats":
            $returned_value = stats($database,sanitizeMYSQL($connection, $_POST["page"]));
            break;              
        case "generate_token":
            $returned_value = generate_token($database);
            break;        
        case "signup":
            $returned_value = signup($database, sanitizeMYSQL($connection, $_POST["email"]), sanitizeMYSQL($connection, $_POST["password"]), sanitizeMYSQL($connection, $_POST["confirm_password"]),sanitizeMYSQL($connection, $_POST["token"]));
            break;
        case "login":
            $returned_value = login($database, sanitizeMYSQL($connection, $_POST["email"]), sanitizeMYSQL($connection, $_POST["password"]));
            break;
        case "getemail":
            $returned_value = get_email();
            break;
        case "settings":
            $returned_value = settings();
            break;   
        case "edit_settings":
            $returned_value = edit_settings($database, sanitizeMYSQL($connection, $_POST["date_format"]), sanitizeMYSQL($connection, $_POST["number_format"]), sanitizeMYSQL($connection, $_POST["time_format"]), sanitizeMYSQL($connection, $_POST["currency_id"]));
            break; 
        case "recover_password":
            $returned_value = recover_password($database, sanitizeMYSQL($connection, $_POST["email"]));
            break;
        case "change_password":
            $returned_value = change_password($database, sanitizeMYSQL($connection, $_POST["old_password"]),sanitizeMYSQL($connection, $_POST["password"]),sanitizeMYSQL($connection, $_POST["confirmed_password"]));
            break; 
        case "delete_user_account":
            $returned_value = delete_user_account($database);
            break;          
        case "reset_password":
            $returned_value = reset_password($database, sanitizeMYSQL($connection, $_POST["email"]),sanitizeMYSQL($connection, $_POST["password"]),sanitizeMYSQL($connection, $_POST["confirmed_password"]),sanitizeMYSQL($connection, $_POST["code"]));
            break;          
        case "contact_us":
            $returned_value = contact_us(sanitizeMYSQL($connection, $_POST["from"]), sanitizeMYSQL($connection, $_POST["subject"]), sanitizeMYSQL($connection, $_POST["comments"]));
            break;         
        case "edit_orientation":
            $returned_value = edit_orientation($database, sanitizeMYSQL($connection, $_POST["orientation"]));
            break;          
        case "logout":
            $returned_value = log_out();
            break;
        case "categories":
            $returned_value = get_categories($database, sanitizeMYSQL($connection, $_POST["cat_type"]));
            break;
        case "add_category":
            $returned_value = add_category($database, sanitizeMYSQL($connection, $_POST["name"]), sanitizeMYSQL($connection, $_POST["description"]), sanitizeMYSQL($connection, $_POST["color"]), sanitizeMYSQL($connection, $_POST["cat_type"]));
            break;
        case "add_item":
            $returned_value = add_item($database, sanitizeMYSQL($connection, $_POST["name"]), sanitizeMYSQL($connection, $_POST["cat_id"]));
            break;
        case "edit_item":
            $returned_value = edit_item($database, sanitizeMYSQL($connection, $_POST["name"]), sanitizeMYSQL($connection, $_POST["item_id"]), sanitizeMYSQL($connection, $_POST["cat_id"]));
            break;
        case "delete_item":
            $returned_value = delete_item($database, sanitizeMYSQL($connection, $_POST["item_id"]), sanitizeMYSQL($connection, $_POST["cat_id"]));
            break;
        case "change_item_category":
            $returned_value = change_item_category($database, sanitizeMYSQL($connection, $_POST["item_id"]), sanitizeMYSQL($connection, $_POST["cat_id"]));
            break;        
        case "edit_category":
            $returned_value = edit_category($database, sanitizeMYSQL($connection, $_POST["name"]), sanitizeMYSQL($connection, $_POST["description"]), sanitizeMYSQL($connection, $_POST["color"]), sanitizeMYSQL($connection, $_POST["cat_type"]), sanitizeMYSQL($connection, $_POST["cat_id"]));
            break;
        case "delete_category":
            $returned_value = delete_category($database, sanitizeMYSQL($connection, $_POST["cat_id"]));
            break;
        case "accounts":
            $returned_value = get_accounts($database);
            break;
        case "currencies":
            $returned_value = get_currencies();
            break;
        case "add_account":
            $returned_value = add_account($database, sanitizeMYSQL($connection, $_POST["name"]), sanitizeMYSQL($connection, $_POST["description"]), sanitizeMYSQL($connection, $_POST["currency_id"]), sanitizeMYSQL($connection, $_POST["money_type"]), sanitizeMYSQL($connection, $_POST["account_type"]), sanitizeMYSQL($connection, $_POST["initial_balance"]));
            break;
        case "edit_account":
            $returned_value = edit_account($database, sanitizeMYSQL($connection, $_POST["name"]), sanitizeMYSQL($connection, $_POST["description"]), sanitizeMYSQL($connection, $_POST["currency_id"]), sanitizeMYSQL($connection, $_POST["money_type"]), sanitizeMYSQL($connection, $_POST["account_type"]), sanitizeMYSQL($connection, $_POST["initial_balance"]), sanitizeMYSQL($connection, $_POST["id"]));
            break;
        case "delete_account":
            $returned_value = delete_account($database, sanitizeMYSQL($connection, $_POST["id"]));
            break;
        case "add_transaction":
            $returned_value = add_transaction($database, sanitizeMYSQL($connection, $_POST["payee"]), sanitizeMYSQL($connection, $_POST["date_day"]), sanitizeMYSQL($connection, $_POST["date_month"]),sanitizeMYSQL($connection, $_POST["date_year"]),sanitizeMYSQL($connection, $_POST["time_hour"]),sanitizeMYSQL($connection, $_POST["time_minute"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeArray($connection, "item_names"),sanitizeArray($connection, "category_names"),sanitizeArray($connection, "amounts"),sanitizeArray($connection, "quantities"),sanitizeMYSQL($connection, $_POST["category_type"]),sanitizeArray($connection, "indices"));
            break;  
        case "edit_transaction":
            $returned_value = edit_transaction($database, sanitizeMYSQL($connection, $_POST["transaction_id"]), sanitizeMYSQL($connection, $_POST["payee"]),sanitizeMYSQL($connection, $_POST["date_day"]), sanitizeMYSQL($connection, $_POST["date_month"]),sanitizeMYSQL($connection, $_POST["date_year"]),sanitizeMYSQL($connection, $_POST["time_hour"]),sanitizeMYSQL($connection, $_POST["time_minute"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeArray($connection, "transaction_item_ids"),sanitizeArray($connection, "item_names"),sanitizeArray($connection, "category_names"),sanitizeArray($connection, "item_amounts"),sanitizeArray($connection, "quantities"),sanitizeMYSQL($connection, $_POST["category_type"]),sanitizeArray($connection, "indices"));
            break; 
        case "delete_transaction":
            $returned_value = delete_transaction($database, sanitizeMYSQL($connection, $_POST["transaction_id"]));
            break;  
        case "delete_transaction_item":
            $returned_value = delete_transaction_item($database,sanitizeMYSQL($connection, $_POST["transaction_item_id"]), sanitizeMYSQL($connection, $_POST["transaction_id"]));
            break;        
        case "add_transaction_item":
            $returned_value = add_transaction_item($database, sanitizeMYSQL($connection, $_POST["transaction_id"]),sanitizeMYSQL($connection, $_POST["item_name"]),sanitizeMYSQL($connection, $_POST["category_name"]),sanitizeMYSQL($connection, $_POST["amount"]),sanitizeMYSQL($connection, $_POST["quantity"]));
            break;   
        case "edit_transaction_item":
            $returned_value = edit_transaction_item($database, sanitizeMYSQL($connection, $_POST["transaction_item_id"]),sanitizeMYSQL($connection, $_POST["transaction_id"]),sanitizeMYSQL($connection, $_POST["item_name"]),sanitizeMYSQL($connection, $_POST["category_name"]),sanitizeMYSQL($connection, $_POST["amount"]),sanitizeMYSQL($connection, $_POST["quantity"]));
            break;        
        case "add_income":
            $returned_value = add_income($database, sanitizeMYSQL($connection, $_POST["date_day"]), sanitizeMYSQL($connection, $_POST["date_month"]),sanitizeMYSQL($connection, $_POST["date_year"]),sanitizeMYSQL($connection, $_POST["time_hour"]),sanitizeMYSQL($connection, $_POST["time_minute"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeMYSQL($connection, $_POST["item_name"]),sanitizeMYSQL($connection, $_POST["category_name"]),sanitizeMYSQL($connection, $_POST["amount"]),sanitizeMYSQL($connection, $_POST["category_type"]));
            break;  
        case "edit_income":
            $returned_value = edit_income($database, sanitizeMYSQL($connection, $_POST["income_id"]),sanitizeMYSQL($connection, $_POST["date_day"]), sanitizeMYSQL($connection, $_POST["date_month"]),sanitizeMYSQL($connection, $_POST["date_year"]),sanitizeMYSQL($connection, $_POST["time_hour"]),sanitizeMYSQL($connection, $_POST["time_minute"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeMYSQL($connection, $_POST["item_name"]),sanitizeMYSQL($connection, $_POST["category_name"]),sanitizeMYSQL($connection, $_POST["amount"]),sanitizeMYSQL($connection, $_POST["category_type"]));
            break;
       case "delete_income":
            $returned_value = delete_income($database, sanitizeMYSQL($connection, $_POST["income_id"]));
            break;         
        case "add_transfer":
            $returned_value = add_transfer($database, sanitizeMYSQL($connection, $_POST["date_day"]), sanitizeMYSQL($connection, $_POST["date_month"]),sanitizeMYSQL($connection, $_POST["date_year"]),sanitizeMYSQL($connection, $_POST["time_hour"]),sanitizeMYSQL($connection, $_POST["time_minute"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["from_account_id"]),sanitizeMYSQL($connection, $_POST["to_account_id"]),sanitizeMYSQL($connection, $_POST["amount"]));
            break;
        case "edit_transfer":
            $returned_value = edit_transfer($database, sanitizeMYSQL($connection, $_POST["transfer_id"]), sanitizeMYSQL($connection, $_POST["date_day"]), sanitizeMYSQL($connection, $_POST["date_month"]),sanitizeMYSQL($connection, $_POST["date_year"]),sanitizeMYSQL($connection, $_POST["time_hour"]),sanitizeMYSQL($connection, $_POST["time_minute"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["from_account_id"]),sanitizeMYSQL($connection, $_POST["to_account_id"]),sanitizeMYSQL($connection, $_POST["amount"]));
            break;
       case "delete_transfer":
            $returned_value = delete_transfer($database, sanitizeMYSQL($connection, $_POST["transfer_id"]));
            break;           
        case "transactions":
            $returned_value = get_transactions($database, sanitizeMYSQL($connection, $_POST["month"]), sanitizeMYSQL($connection, $_POST["year"]), sanitizeMYSQL($connection, $_POST["transaction_type"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["transaction_account"]));
            break; 
        case "transaction":
            $returned_value = get_transaction($database, sanitizeMYSQL($connection, $_POST["month"]), sanitizeMYSQL($connection, $_POST["year"]), sanitizeMYSQL($connection, $_POST["transaction_type"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["id"]),sanitizeMYSQL($connection, $_POST["transaction_account"]));
            break;     
        case "transaction_item":
            $returned_value = get_transaction_item($database, sanitizeMYSQL($connection, $_POST["transaction_id"]), sanitizeMYSQL($connection, $_POST["id"]));
            break;         
        case "item_suggestions":
            $returned_value = get_item_suggestions($database, sanitizeMYSQL($connection, $_POST["text"]), sanitizeMYSQL($connection, $_POST["item_type"]));
            break;  
        case "payee_suggestions":
            $returned_value = get_payee_suggestions($database, sanitizeMYSQL($connection, $_POST["payee"]));
            break;
        case "payees":
            $returned_value = get_payees($database);
            break;         
        case "item_suggestions_from_payee":
            $returned_value = get_item_suggestions_from_payee($database, sanitizeMYSQL($connection, $_POST["payee"]));
            break;          
        case "average_category":
            $returned_value=get_average_category($database,sanitizeMYSQL($connection, $_POST["category_id"]),sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]));
            break;
        case "budgets":
            $returned_value = get_budgets($database);
            break;
        case "one_budget":
            $returned_value = get_budget($database, sanitizeMYSQL($connection, $_POST["id"]));
            break;        
        case "targets":
            $returned_value = get_targets($database, sanitizeMYSQL($connection, $_POST["budget_id"]));
            break;
        case "user_currencies":
            $returned_value = get_user_currencies($database);
            break;       
        case "user_categories":
            $returned_value = get_user_categories($database);
            break; 
        case "income_categories":
            $returned_value = get_income_categories($database);
            break;           
        case "user_accounts":
            $returned_value = get_user_accounts($database);
            break;         
        case "add_budget":
            $returned_value = add_budget($database,sanitizeMYSQL($connection, $_POST["name"]), sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["end_month"]), 
                    sanitizeMYSQL($connection, $_POST["end_year"]), sanitizeArray($connection, "target_category_ids"),
            sanitizeArray($connection, "target_amounts"),sanitizeArray($connection, "target_currency_ids"),sanitizeArray($connection, "target_periods"),sanitizeArray($connection, "target_everys"),sanitizeArray($connection, "rollovers"),sanitizeArray($connection, "indices"));
            break;  
        case "edit_budget":
            $returned_value = edit_budget($database,sanitizeMYSQL($connection, $_POST["id"]),sanitizeMYSQL($connection, $_POST["name"]), sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["end_month"]), 
                    sanitizeMYSQL($connection, $_POST["end_year"]), sanitizeArray($connection, "target_ids"), sanitizeArray($connection, "target_category_ids"),
            sanitizeArray($connection, "target_amounts"),sanitizeArray($connection, "target_currency_ids"),sanitizeArray($connection, "target_periods"),sanitizeArray($connection, "target_everys"),sanitizeArray($connection, "rollovers"),sanitizeArray($connection, "indices"));
            break;         
        case "delete_budget":
            $returned_value = delete_budget($database, sanitizeMYSQL($connection, $_POST["id"]));
            break;  
        case "add_target":
            $returned_value = add_target($database,sanitizeMYSQL($connection, $_POST["budget_id"]),sanitizeMYSQL($connection, $_POST["amount"]),sanitizeMYSQL($connection, $_POST["category_id"]),sanitizeMYSQL($connection, $_POST["currency_id"]),sanitizeMYSQL($connection, $_POST["period"]),sanitizeMYSQL($connection, $_POST["every"]),sanitizeMYSQL($connection, $_POST["rollover"]));
            break; 
        case "edit_target":
            $returned_value = edit_target($database,sanitizeMYSQL($connection, $_POST["budget_id"]),sanitizeMYSQL($connection, $_POST["target_id"]),sanitizeMYSQL($connection, $_POST["amount"]),sanitizeMYSQL($connection, $_POST["category_id"]),sanitizeMYSQL($connection, $_POST["currency_id"]),sanitizeMYSQL($connection, $_POST["period"]),sanitizeMYSQL($connection, $_POST["every"]),sanitizeMYSQL($connection, $_POST["rollover"]));
            break; 
        case "delete_target":
            $returned_value = delete_target($database,sanitizeMYSQL($connection, $_POST["budget_id"]),sanitizeMYSQL($connection, $_POST["target_id"]));
            break;      
        case "analytics_monthly_expense":
            $returned_value=get_monthly_expense($database,sanitizeMYSQL($connection, $_POST["start_day"]),sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["end_day"]),sanitizeMYSQL($connection, $_POST["end_month"]),sanitizeMYSQL($connection, $_POST["end_year"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["filter_by"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeMYSQL($connection, $_POST["prev_month"]),sanitizeMYSQL($connection, $_POST["avg"]));
            break;
        case "analytics_monthly_expenses_stats":
            $returned_value=get_monthly_expenses_stats($database,sanitizeMYSQL($connection, $_POST["start_day"]),sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeMYSQL($connection, $_POST["period"]),sanitizeMYSQL($connection, $_POST["category"]));
            break; 
        case "analytics_monthly_income_vs_expenses_stats":
            $returned_value=get_monthly_income_vs_expenses_stats($database,sanitizeMYSQL($connection, $_POST["start_day"]),sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeMYSQL($connection, $_POST["period"]),sanitizeMYSQL($connection, $_POST["sort_by"]));
            break;  
        case "analytics_monthly_savings_stats":
            $returned_value=get_monthly_savings_stats($database,sanitizeMYSQL($connection, $_POST["start_day"]),sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeMYSQL($connection, $_POST["period"]));
            break;         
        case "budget_analytics":
            $returned_value=get_budget_analytics($database,sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeMYSQL($connection, $_POST["budget_id"]));
            break;    
        case "analytics_budgets":
            $returned_value=get_analytics_budgets($database,sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]));
            break; 
        case "analytics_expense_categories":
            $returned_value=get_analytics_expense_categories($database,sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["period"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]));
            break;         
        case "transactions_monthly_overview":
            $returned_value=get_transactions_monthly_overview($database,sanitizeMYSQL($connection, $_POST["start_month"]),sanitizeMYSQL($connection, $_POST["start_year"]),sanitizeMYSQL($connection, $_POST["account_id"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]));
            break;   
        case "transaction_years":
            $returned_value=get_transaction_years($database,sanitizeMYSQL($connection, $_POST["current_month"]),sanitizeMYSQL($connection, $_POST["current_year"]),sanitizeMYSQL($connection, $_POST["time_zone_offset"]));
            break;         
 
    }
    echo $returned_value;
}

function delete_budget($database, $id){
     $result = array();
    if (is_session_active()) {
        $budget_list = new BudgetList();
        $result = $budget_list->delete_budget($database,$id,$_SESSION["user"]->get_ID());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);      
}

function add_budget($database, $name, $start_month,$start_year,$end_month, $end_year,$target_category_ids,
            $target_amounts,$target_currency_ids,$target_periods,$target_everys,$rollovers,$indices){
     $result = array();
    if (is_session_active()) {
        $budget_list = new BudgetList();
        $result = $budget_list->add_budget($database, $name, $start_month, $start_year, $end_month, $end_year, $_SESSION["user"]->get_ID(), $target_category_ids, $target_amounts, $target_currency_ids, $target_periods, $target_everys,$rollovers,$indices);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);      
}

function add_target($database,$budget_id,$amount,$category_id,$currency_id,$period,$every,$rollover){
    $result = array();
    if (is_session_active()) {
        $target_list = new TargetList();
        $result = $target_list->add_target($database, $budget_id, $_SESSION["user"]->get_ID(), $category_id, $amount, $currency_id, $period, $every,$rollover,1);
        if($result["result"]=="success"){
        $budget_list = new BudgetList();
        $result["budget"]= $budget_list->get_budget($database,$budget_id,$_SESSION["user"]->get_ID(),$_SESSION["user"]->get_currency_id(),$_SESSION["user"]->get_NumberFormat());
    }
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);      
}

function edit_target($database,$budget_id,$target_id,$amount,$category_id,$currency_id,$period,$every,$rollover){
    $result = array();
    if (is_session_active()) {
        $target_list = new TargetList();
        $result = $target_list->edit_target($database, $budget_id,$target_id, $_SESSION["user"]->get_ID(), $category_id, $amount, $currency_id, $period, $every,$rollover,1);
        if($result["result"]=="success"){
        $budget_list = new BudgetList();
        $result["budget"]= $budget_list->get_budget($database,$budget_id,$_SESSION["user"]->get_ID(),$_SESSION["user"]->get_currency_id(),$_SESSION["user"]->get_NumberFormat());
    }
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);     
}

function delete_target($database, $budget_id,$target_id){
    $result = array();
    if (is_session_active()) {
        $target_list = new TargetList();
        $result = $target_list->delete_target($database,$target_id, $_SESSION["user"]->get_ID());
        if($result["result"]=="success"){
        $budget_list = new BudgetList();
        $result["budget"]= $budget_list->get_budget($database,$budget_id,$_SESSION["user"]->get_ID(),$_SESSION["user"]->get_currency_id(),$_SESSION["user"]->get_NumberFormat());
    }
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);      
}

function edit_budget($database, $id,$name, $start_month,$start_year,$end_month, $end_year, $target_ids,$target_category_ids,
            $target_amounts,$target_currency_ids,$target_periods,$target_everys,$target_rollovers,$indices){
     $result = array();
    if (is_session_active()) {
        $budget_list = new BudgetList();
        $user_id=$_SESSION["user"]->get_ID();
        /*$result=array("target_ids"=>$target_ids,"target_category_ids"=>$target_category_ids,
            "target_amounts"=>$target_amounts,"currency_ids"=>$target_currency_ids,"periods"=>$target_periods,"everys"=>$target_everys,"rollovers"=>$target_rollovers);*/
        $result = $budget_list->edit_budget($database, $id, $name, $start_month, $start_year, $end_month, $end_year, $user_id, $target_ids, $target_category_ids, $target_amounts, $target_currency_ids, $target_periods, $target_everys, $target_rollovers, $indices);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);      
}

function get_currencies() {
    $result = array();
    if (is_session_active()) {
        $result = Utility::load_currencies();
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function get_user_currencies($database) {
    $result = array();
    if (is_session_active()) {
        $budget_list = new BudgetList();
        $result = $budget_list->get_user_currencies($database,$_SESSION["user"]->get_ID(),$_SESSION["user"]->get_currency_id());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function get_user_accounts($database) {
    $result = array();
    if (is_session_active()) {
        $transaction_list = new TransactionList();
        $result = $transaction_list->get_user_accounts($database,$_SESSION["user"]->get_ID());
    }
    else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;    
    return json_encode($result);
}

function get_user_categories($database) {
    $result = array();
    if (is_session_active()) {
        $budget_list = new BudgetList();
        $result = $budget_list->get_user_categories($database,$_SESSION["user"]->get_ID());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function get_analytics_expense_categories($database,$start_month,$start_year,$period,$time_zone_offset){
    $result = array();
    if (is_session_active()) {
        $analytics = new Analytics();
        $result = $analytics->get_expense_categories($database,$_SESSION["user"]->get_ID(),$start_month,$start_year,$period,$time_zone_offset);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}


function get_income_categories($database) {
    $result = array();
    if (is_session_active()) {
        $budget_list = new BudgetList();
        $result = $budget_list->get_income_categories($database,$_SESSION["user"]->get_ID());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function get_average_category($database,$category_id,$start_month,$start_year,$time_zone_offset){
   
    $result = array();
   if (is_session_active()) {
        $user_id=$_SESSION["user"]->get_ID();  
        $user_currency_id=$_SESSION["user"]->get_currency_id();
        $number_format=$_SESSION["user"]->get_NumberFormat();
        
        $analytics=new Analytics();
        $result = $analytics->get_AVG_expenses_2($database, 1, $start_month, $start_year, $time_zone_offset, "Category.ID", "all_accounts", $user_id, $user_currency_id, $number_format, $category_id);     
   }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;    
   return json_encode($result);    
}


function get_budgets($database) {
    $result = array();
    if (is_session_active()) {
        $budget_list = new BudgetList();
        $result = $budget_list->get_budgets($database,$_SESSION["user"]->get_ID(),$_SESSION["user"]->get_currency_id(),$_SESSION["user"]->get_NumberFormat());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function delete_transaction_item($database,$transaction_item_id,$transaction_id){
     $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();        
        $result = $transaction_list->delete_transaction_item($database,$transaction_item_id,$transaction_id,$user_id);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function delete_transaction($database,$transaction_id){
     $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();        
        $result = $transaction_list->delete_transaction($database,$transaction_id,$user_id);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function edit_transaction($database,$transaction_id,$payee,$date_day,$date_month,$date_year,$time_hour,$time_minute,$time_zone_offset,$account_id,$transaction_item_ids,$item_names, $category_names, $item_amounts, $quantities,$category_type,$indices){
     $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();    
        $lang=$_SESSION["user"]->get_language();
        //$result=array("transaction_item_ids"=>$transaction_item_ids,"item_names"=>$item_names, "category_names"=>$category_names, "item_names"=>$item_amounts, "quantities"=>$quantities,"category_type"=>$category_type);
        $result = $transaction_list->edit_transaction($database,$transaction_id,$payee,$date_day,$date_month,$date_year,$time_hour,$time_minute,$time_zone_offset,$account_id,$user_id,$transaction_item_ids,$item_names, $category_names, $item_amounts, $quantities,$indices,$category_type, $lang);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function add_transaction($database, $payee, $date_day, $date_month,$date_year,$time_hour,$time_minute,$time_zone_offset,$account_id,$item_names, $category_names,$amounts,$quantities,$category_type,$indices){
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();
        $lang=$_SESSION["user"]->get_language();
        
        $result = $transaction_list->add_transaction($database, $payee, $date_day,$date_month,$date_year, $time_hour,$time_minute,$time_zone_offset,$account_id, $item_names,$category_names, $amounts,$quantities,$indices,$user_id,$category_type,$lang);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function add_transaction_item($database, $transaction_id,$item_name,$category_name,$amount,$quantity){
     $result = array();
    if (is_session_active()) {        
        $transaction_item_list = new TransactionItemList();
        $user_id=$_SESSION["user"]->get_ID();
        $lang=$_SESSION["user"]->get_language();
        $result = $transaction_item_list->add_transaction_item($database, $transaction_id, $user_id, $item_name, $category_name, $amount,$quantity, "e", $lang,1);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function edit_transaction_item($database, $transaction_item_id,$transaction_id,$item_name,$category_name,$amount,$quantity){
     $result = array();
    if (is_session_active()) {        
        $transaction_item_list = new TransactionItemList();
        $user_id=$_SESSION["user"]->get_ID();
        $lang=$_SESSION["user"]->get_language();
        $result = $transaction_item_list->edit_transaction_item($database, $transaction_item_id,$transaction_id, $user_id, $item_name, $category_name, $amount,$quantity, "e", $lang,1);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function edit_transfer($database, $transfer_id,$date_day, $date_month,$date_year,$time_hour,$time_minute,$time_zone_offset,$from_account_id,$to_account_id, $amount){
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();
        $result = $transaction_list-> edit_transfer($database, $transfer_id,$date_day, $date_month, $date_year, $time_hour, $time_minute, $time_zone_offset, $from_account_id, $to_account_id,$amount,$user_id);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}



function add_transfer($database, $date_day, $date_month,$date_year,$time_hour,$time_minute,$time_zone_offset,$from_account_id,$to_account_id, $amount){
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();
        $result = $transaction_list-> add_transfer($database, $date_day, $date_month, $date_year, $time_hour, $time_minute, $time_zone_offset, $from_account_id, $to_account_id,$amount,$user_id);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function delete_transfer($database,$transfer_id){
     $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();        
        $result = $transaction_list->delete_transfer($database,$transfer_id,$user_id);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function delete_income($database,$income_id){
     $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();        
        $result = $transaction_list->delete_income($database,$income_id,$user_id);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function edit_income($database, $income_id,$date_day, $date_month,$date_year,$time_hour,$time_minute,$time_zone_offset,$account_id,$item_name, $category_name,$amount,$category_type){
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();
        $lang=$_SESSION["user"]->get_language();
        $result = $transaction_list->edit_income($database,$income_id, $date_day,$date_month,$date_year, $time_hour,$time_minute,$time_zone_offset,$account_id, $category_name,$item_name, $amount,$category_type,$lang, $user_id);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}
                            
function add_income($database, $date_day, $date_month,$date_year,$time_hour,$time_minute,$time_zone_offset,$account_id,$item_name, $category_name,$amount,$category_type){
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
        $user_id=$_SESSION["user"]->get_ID();
        $lang=$_SESSION["user"]->get_language();
        $result = $transaction_list->add_income($database, $date_day,$date_month,$date_year, $time_hour,$time_minute,$time_zone_offset,$account_id, $category_name,$item_name, $amount,$category_type,$lang, $user_id);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function get_transactions($database,$month, $year,$transaction_type,$time_zone_offset,$transaction_account) {
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
         $result = $transaction_list->get_transactions($database,$month, $year,$_SESSION["user"]->get_ID(),$_SESSION["user"]->get_NumberFormat(),$_SESSION["user"]->get_TimeFormat(),$_SESSION["user"]->get_DateFormat(),$transaction_type,$time_zone_offset,$transaction_account);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function get_transaction($database,$month, $year,$transaction_type,$time_zone_offset,$id,$transaction_account) {
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();
         $result = $transaction_list->get_transaction($database,$month, $year,$_SESSION["user"]->get_ID(),$_SESSION["user"]->get_NumberFormat(),$_SESSION["user"]->get_TimeFormat(),$_SESSION["user"]->get_DateFormat(),$transaction_type,$time_zone_offset,$id,$transaction_account);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function get_transaction_item($database, $transaction_id, $id){
    $result = array();
    if (is_session_active()) {        
        $transaction_item_list = new TransactionItemList();
        $user_id=$_SESSION["user"]->get_ID();
        $number_format=$_SESSION["user"]->get_NumberFormat();       
         $result = $transaction_item_list->get_transaction_item($database, $transaction_id, $id, $user_id, $number_format);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);    
}

function get_payees($database) {
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();        
        $result = $transaction_list->get_payees($database,$_SESSION["user"]->get_ID(), $_SESSION["user"]->get_current_country_code());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function get_payee_suggestions($database,$payee) {
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();        
        $result = $transaction_list->get_payee_suggestions($database, $payee, $_SESSION["user"]->get_ID(), $_SESSION["user"]->get_current_country_code());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function get_item_suggestions_from_payee($database,$payee) {
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();        
        $result=$transaction_list->get_item_suggestions_from_payee($database, $payee,$_SESSION["user"]->get_ID()); 
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}


function get_item_suggestions($database,$text,$type) {
    $result = array();
    if (is_session_active()) {        
        $transaction_list = new TransactionList();        
        $result = $transaction_list->get_item_suggestions($database,trim($text),$_SESSION["user"]->get_ID(),$type);
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function get_budget($database,$id) {
    $result = array();
    if (is_session_active()) {
        $budget_list = new BudgetList();
        $result = $budget_list->get_budget($database,$id,$_SESSION["user"]->get_ID(),$_SESSION["user"]->get_currency_id(),$_SESSION["user"]->get_NumberFormat());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;    
    return json_encode($result);
}

function get_targets($database, $budget_id) {
    $result = array();
    if (is_session_active()) {
        $target_list = new TargetList();
        $result = $target_list->get_targets($database, $_SESSION["user"]->get_NumberFormat(), $budget_id, $_SESSION["user"]->get_currency_id());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;      
    return json_encode($result);
}

function cmp($target_a, $target_b) {
    if ($target_a["target_equivalent"] == $target_b["target_equivalent"]) {
        return 0;
    }
    return ($target_a["target_equivalent"] < $target_b["target_equivalent"]) ? -1 : 1;
}

function get_accounts($database) {
    $result = array();
    if (is_session_active()) {
        $account_list = new AccountList();
        $result = $account_list->get_accounts($database, $_SESSION["user"]->get_ID(), $_SESSION["user"]->get_NumberFormat(),$_SESSION["user"]->get_currency_id());
    }
     else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function edit_account($database, $name, $des, $currency_id, $money_type, $type, $initial_balance, $id) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $account_list = new AccountList();
        $array = $account_list->edit_account($database, trim($name), trim($des), $_SESSION["user"]->get_ID(), trim($currency_id), trim($money_type), trim($type), trim($initial_balance), $_SESSION["user"]->get_NumberFormat(), trim($id));
    }
     else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;   
    return json_encode($array);
}

function add_account($database, $name, $des, $currency_id, $money_type, $type, $initial_balance) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $account_list = new AccountList();
        $array = $account_list->add_account($database, trim($name), trim($des), $_SESSION["user"]->get_ID(), trim($currency_id), trim($money_type), trim($type), trim($initial_balance), $_SESSION["user"]->get_NumberFormat());
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($array);
}

function delete_account($database, $account_id) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $account_list = new AccountList();
        $array = $account_list->delete_account($database, $_SESSION["user"]->get_ID(), trim($account_id));
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($array);
}

function get_categories($database, $cat_type) {
    $result = array();
    $result[Utility::$RESULT] = Utility::$SUCCESS;
    if (is_session_active()) {
        $category_list = new CategoryList();
        $result = $category_list->get_categories($database, $_SESSION["user"]->get_ID(), $cat_type);
    }
    else
        $result[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($result);
}

function add_category($database, $name, $description, $color, $cat_type) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $category_list = new CategoryList();
        $array = $category_list->add_category($database, trim($name), trim($description), trim($color), $_SESSION["user"]->get_ID(), trim($cat_type));
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE; 
    return json_encode($array);
}

function edit_item($database, $name, $item_id, $category_id) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $item_list = new ItemList();
        $array = $item_list->edit_item($database, $name, $item_id, $category_id, $_SESSION["user"]->get_ID());
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;    
    return json_encode($array);
}

function delete_item($database, $item_id, $category_id) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $item_list = new ItemList();
        $array = $item_list->delete_item($database, $item_id, $category_id, $_SESSION["user"]->get_ID());
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;    
    return json_encode($array);
}

function change_item_category($database, $item_id, $category_id) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $item_list = new ItemList();
        $array = $item_list->change_item_category($database, $item_id, $category_id, $_SESSION["user"]->get_ID());
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;   
    return json_encode($array);
}

function add_item($database, $name, $category_id) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $item_list = new ItemList();
        $array = $item_list->add_item($database, $name, $category_id, $_SESSION["user"]->get_ID());
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;        
    return json_encode($array);
}

function edit_category($database, $name, $description, $color, $cat_type, $cat_id) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $category_list = new CategoryList();
        $array = $category_list->edit_category($database, trim($name), trim($description), trim($color), $_SESSION["user"]->get_ID(), trim($cat_type), trim($cat_id));
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($array);
}

function delete_category($database, $cat_id) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $category_list = new CategoryList();
        $array = $category_list->delete_category($database, $_SESSION["user"]->get_ID(), trim($cat_id));
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE; 
    return json_encode($array);
}

function get_transaction_years($database,$current_month,$current_year,$time_zone_offset){
    $array=array();
    $array[Utility::$RESULT] = Utility::$SUCCESS;    
    if (is_session_active()) {
        $analytics = new Analytics();
        $user_id=$_SESSION["user"]->get_ID();  
        $array = $analytics->get_transaction_years($database, $user_id, $current_month, $current_year, $time_zone_offset);
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE; 
    
    return json_encode($array);     
}

function get_transactions_monthly_overview($database,$start_month,$start_year,$account_id,$time_zone_offset){
    $array=array();
    $array[Utility::$RESULT] = Utility::$SUCCESS;  
    if (is_session_active()) {
        $analytics = new Analytics();
        $user_id=$_SESSION["user"]->get_ID();   
        $user_currency_id=$_SESSION["user"]->get_currency_id();
        $number_format=$_SESSION["user"]->get_NumberFormat();
        $date_format_id=$_SESSION["user"]->get_DateFormat();
        $array = $analytics->get_transactions_monthly_overview($database, $start_month, $start_year, $time_zone_offset,$account_id, $user_id,$number_format,$user_currency_id,$date_format_id);
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;      
    return json_encode($array);          
}

function get_analytics_budgets($database,$start_month,$start_year,$time_zone_offset){
    $array=array();
    $array[Utility::$RESULT] = Utility::$SUCCESS;   
    if (is_session_active()) {
        $analytics = new Analytics();
        $user_id=$_SESSION["user"]->get_ID();          
        $array = $analytics->get_budgets($database, $start_month, $start_year, $time_zone_offset, $user_id);
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;     
    return json_encode($array);          
}

function get_budget_analytics($database,$start_month,$start_year,$time_zone_offset,$account_id,$budget_id){
    $array=array();
    $array[Utility::$RESULT] = Utility::$SUCCESS;       
    if (is_session_active()) {
        $analytics = new Analytics();
        $user_id=$_SESSION["user"]->get_ID();
        $user_currency_id=$_SESSION["user"]->get_currency_id();
        $number_format=$_SESSION["user"]->get_NumberFormat();
        $date_format=$_SESSION["user"]->get_DateFormat();
        $time_format=$_SESSION["user"]->get_TimeFormat();        
        $array = $analytics->get_budget_analytics($database, $start_month, $start_year, $time_zone_offset, $account_id, $user_id, $user_currency_id, $number_format, $budget_id, $date_format, $time_format);
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;  
    
    return json_encode($array);          
}

function get_monthly_expenses_stats($database,$start_day,$start_month,$start_year,$time_zone_offset,$account_id,$period,$category){

     $array = array();
     $array[Utility::$RESULT] = Utility::$SUCCESS;     
    
    if (is_session_active()) {
        $analytics = new Analytics();
        $user_id=$_SESSION["user"]->get_ID();
        $user_currency_id=$_SESSION["user"]->get_currency_id();
        $number_format=$_SESSION["user"]->get_NumberFormat();
        $array = $analytics->get_monthly_expenses_stats($database, $start_day, $start_month, $start_year, $time_zone_offset, $account_id, $user_id, $user_currency_id, $number_format, $period, $category);
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;    
    return json_encode($array);    
}

function get_monthly_savings_stats($database,$start_day,$start_month,$start_year,$time_zone_offset,$account_id,$period){

     $array = array();
     $array[Utility::$RESULT] = Utility::$SUCCESS;
    
    if (is_session_active()) {
        $analytics = new Analytics();
        $user_id=$_SESSION["user"]->get_ID();
        $user_currency_id=$_SESSION["user"]->get_currency_id();
        $number_format=$_SESSION["user"]->get_NumberFormat();
        $array = $analytics->get_monthly_income_vs_expense_stats($database, $start_day, $start_month, $start_year, $time_zone_offset, $account_id, $user_id, $user_currency_id, $number_format, $period);
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;
    
    return json_encode($array);    
}

function get_monthly_income_vs_expenses_stats($database,$start_day,$start_month,$start_year,$time_zone_offset,$account_id,$period,$sort_by){

     $array = array();
     $array[Utility::$RESULT] = Utility::$SUCCESS;
     
    if (is_session_active()) {
        $analytics = new Analytics();
        $user_id=$_SESSION["user"]->get_ID();
        $user_currency_id=$_SESSION["user"]->get_currency_id();
        $number_format=$_SESSION["user"]->get_NumberFormat();
        $array = $analytics->get_monthly_income_vs_expense_stats($database, $start_day, $start_month, $start_year, $time_zone_offset, $account_id, $user_id, $user_currency_id, $number_format, $period, $sort_by);
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;
    
    return json_encode($array);    
}

function get_monthly_expense($database,$start_day,$start_month,$start_year,$end_day,$end_month,$end_year,$time_zone_offset,$filter_by,$account_id,$prev_month,$avg){

     $array = array();
     $array[Utility::$RESULT] = Utility::$SUCCESS;
    
    if (is_session_active()) {
        $analytics = new Analytics();
        $user_id=$_SESSION["user"]->get_ID();
        $user_currency_id=$_SESSION["user"]->get_currency_id();
        $number_format=$_SESSION["user"]->get_NumberFormat(); 
        $array = $analytics->get_prev_month_expenses($database, $start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $time_zone_offset, $filter_by, $account_id, $user_id, $user_currency_id, $number_format,$prev_month,$avg);
    }
    else
        $array[Utility::$RESULT]=Utility::$SESSION_INACTIVE;
    
    return json_encode($array);    
}


function user_exists($database, $email) {
    $user = new User();
    $result = $user->user_exists($database, $email);
    if (!$result)
        return "valid";
    return "User exists already";
}

function stats($database,$page) {
    $array[Utility::$RESULT] = Utility::$SUCCESS;
    $settings=new Settings();
    $settings->stats($database, $page);
    return json_encode($array);
}

function generate_token($database) {
    $array["t"]="";
    $settings=new Settings();
    $array["t"]=$settings->generate_signup_code($database);
    return json_encode($array);
}

function signup($database, $email, $password, $confirm_password,$token_id) {
    $user = new User();
    $result = $user->add_user($database, $email, $password, $confirm_password,$token_id);
    if($result["result"]=="success"){
        login($database,$email,$password);
    }
    return json_encode($result);
}

function login($database, $email, $password) {
    $user = new User();
    $user = $user->authenticate($database, $email, $password);
    if ($user != null) { //success
		session_start();
        //session_start(['cookie_lifetime' => 86400,'read_and_close'  => true]);
        //ini_set('session.gc_maxlifetime', 8*60*60);
       // $_SESSION["start"] = time(); //start time is now
        $_SESSION["user"] = $user; //store the user object in the session

        return "success";
    }
    return "fail";
}

function get_email() {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;
    $array["email"] = "";

    if (is_session_active()) {
        $array["email"] = $_SESSION["user"]->get_email();
        $array["result"] = Utility::$SUCCESS;
    }
    else
        $array["result"]=Utility::$SESSION_INACTIVE;
    return json_encode($array);
}

function settings() {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $settings = new Settings();
        $array=$settings->get_settings();
    }
    else
        $array[Utility::$RESULT] =Utility::$SESSION_INACTIVE;
    
    return json_encode($array);
}

function edit_orientation($database,$orientation){
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;
    if (is_session_active()) {
        $array=$_SESSION["user"]->edit_orientation($database,$orientation);
    }
    else
        $array["result"]=Utility::$SESSION_INACTIVE;    
    return json_encode($array);    
}

function edit_settings($database,$date_format, $number_format,$time_format, $currency_id) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $user_id=$_SESSION["user"]->get_ID();
        $settings = new Settings();
        $array=$settings->edit_settings($database, $date_format, $number_format, $time_format, $currency_id, $user_id);
    }
    else
        $array["result"]=Utility::$SESSION_INACTIVE;     
    return json_encode($array);
}

function delete_user_account($database) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $array=$_SESSION["user"]->delete_account($database);
    }
    else
        $array["result"]=Utility::$SESSION_INACTIVE;     
    return json_encode($array);
}

function change_password($database, $old_password,$password,$confirmed_password) {
    $array = array();
    $array[Utility::$RESULT] = Utility::$FAIL;

    if (is_session_active()) {
        $array=$_SESSION["user"]->change_password($database,$old_password, $password, $confirmed_password);
    }
    else
        $array["result"]=Utility::$SESSION_INACTIVE;     
    return json_encode($array);
}

function reset_password($database, $email,$password,$confirmed_password,$code) {
    $user=new User();
    $array=$user->reset_password($database, $email, $password, $confirmed_password, $code);
    return json_encode($array);
}

function recover_password($database, $email) {
    $settings = new Settings();
    $array=$settings->recover_password($database, $email);
    return json_encode($array);
}

function contact_us($from, $subject,$comments) {
    $settings = new Settings();
    $array=$settings->contact_us($from, $subject, $comments);
    return json_encode($array);
}

function is_session_active() {
   return isset($_SESSION) && count($_SESSION) > 0;
}

function log_out() {
    logout();
    return "success";
}

function logout() {
    // Unset all of the session variables.
    $_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }

// Finally, destroy the session.
    session_destroy();
}
?>



