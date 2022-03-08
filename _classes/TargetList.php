<?php

include "Target.php";

class TargetList {
    public static $MAX_NUMBER_TARGETS_PER_BUDGET = 40;
    
    function __construct() {
        
    }
    
    function edit_targets($database, $budget_id, $userID, $target_ids, $target_category_ids, $target_amounts, $target_currency_ids, $target_periods, $target_everys, $target_rollovers, $indices) {
        $results = array();
        for ($i = 0; $i < count($target_category_ids); $i++) {
            if ($i < count($target_currency_ids) && $i < count($target_everys) && $i < count($target_periods) && $i < count($target_amounts)) {
                if(trim($target_ids[$i])=="null" || trim($target_ids[$i])=="")
                    $results[] = $this->add_target($database, $budget_id, $userID, $target_category_ids[$i], $target_amounts[$i], $target_currency_ids[$i], $target_periods[$i], $target_everys[$i], $target_rollovers[$i],$indices[$i]);
                else    
                    $results[] = $this->edit_target($database, $budget_id, $target_ids[$i], $userID, $target_category_ids[$i], $target_amounts[$i], $target_currency_ids[$i], $target_periods[$i], $target_everys[$i], $target_rollovers[$i],$indices[$i]);
            }
        }
        return $results;
    }
    
    function add_targets($database, $budget_id, $userID, $target_category_ids, $target_amounts, $target_currency_ids, $target_periods, $target_everys, $target_rollovers, $indices) {
        $results = array();
        for ($i = 0; $i < count($target_category_ids); $i++) {
            if ($i < count($target_currency_ids) && $i < count($target_everys) && $i < count($target_periods) && $i < count($target_amounts)) {
                $results[] = $this->add_target($database, $budget_id, $userID, $target_category_ids[$i], $target_amounts[$i], $target_currency_ids[$i], $target_periods[$i], $target_everys[$i], $target_rollovers[$i],$indices[$i]);
            }
        }
        return $results;
    }

    function edit_target($database, $budget_id, $target_id, $userID, $category_id, $amount, $currency_id, $period, $every, $rollover,$index) {
        $result = array();
        $target_id_valid = $this->validate_target_id($database, $target_id, $userID);
        $budget_valid = $this->validate_target_budget_id($database, $budget_id, $userID);
        $category_valid = $this->validate_target_category_id($database, $category_id, $userID);
        $currency_valid = $this->validate_target_currency_id($database, $currency_id, $userID);
        $amount_valid = $this->validate_amount($amount);
        $period = Target::fix_period($period);
        $every = Target::fix_every($every);
        $rollover = Target::fix_rollover($rollover);
        $frequency_valid=$this->validate_frequency($database,$budget_id, $every, $period);

        $result["target-budget-id"] = $budget_valid;
        $result["target-category-id"] = $category_valid;
        $result["target-currency-id"] = $currency_valid;
        $result["target-amount-$index"] = $amount_valid;
        $result["target-frequency-$index"] = $frequency_valid;$result["target-rollover-$index"] = "valid";$result["target-category-$index"] = "valid";
        $result["target-id"] = $target_id_valid;
        $result["result"] = "fail";
        if ($frequency_valid=="valid" && $target_id_valid == "valid" && $budget_valid == "valid" && $category_valid == "valid" && $currency_valid == "valid" && $amount_valid == "valid") {
            $values = array("CategoryID" => $category_id, "Amount" => $amount,
                "Every" => $every, "Period" => $period, "CurrencyID" => $currency_id, "Rollover" => $rollover);
            $final = $database->update("Target", $values, "ID='$target_id'");
            if (!$final)
                return $result;
            $result["result"] = "success";
        }
        return $result;
    }

    function delete_target($database, $target_id, $userID) {
        $result = array();
        $result["result"] = "fail";
        $target_id_valid = $this->validate_target_id($database, $target_id, $userID);
        if ($target_id_valid == "valid") {
            $final = $database->delete_where("Target", "ID='$target_id'");
            if (!$final)
                return $result;
            $result["result"] = "success";
        }
        return $result;
    }

    function add_target($database, $budget_id, $userID, $category_id, $amount, $currency_id, $period, $every, $rollover,$index) {
        $result = array();
        $budget_valid = $this->validate_target_budget_id($database, $budget_id, $userID);
        $category_valid = $this->validate_target_category_id($database, $category_id, $userID);
        $currency_valid = $this->validate_target_currency_id($database, $currency_id, $userID);
        $amount_valid = $this->validate_amount($amount);
        $period = Target::fix_period($period);
        $every = Target::fix_every($every);
        $rollover = Target::fix_rollover($rollover);
        $frequency_valid=$this->validate_frequency($database,$budget_id, $every, $period);
        $max_num_targets_valid=$this->validate_max_num_targets_per_budget($database, $budget_id);

        $result["target-budget-id"] = $budget_valid;
        $result["target-category-id"] = $category_valid;
        $result["target-currency-id"] = $currency_valid;
        $result["target-amount-$index"] = $amount_valid;
        $result["target-frequency-$index"] = $frequency_valid;$result["target-rollover-$index"] = "valid";$result["target-category-$index"] = "valid";
        $result["result"] = "fail";
        $result["error_message"]=$max_num_targets_valid=="valid"?"":$max_num_targets_valid;
        if ($frequency_valid=="valid" && $budget_valid == "valid" && $category_valid == "valid" && $currency_valid == "valid" && $amount_valid == "valid" && $max_num_targets_valid=="valid") {
            $values = array("CategoryID" => $category_id, "BudgetID" => $budget_id, "Amount" => $amount,
                "Every" => $every, "Period" => $period, "CurrencyID" => $currency_id, "Rollover" => $rollover);
            $final = $database->insert("Target", $values);
            if (!$final)
                return $result;
            $result["result"] = "success";
            $result["id"] = $database->last_insert_id();
        }
        return $result;
    }

    //adding a target requires a valid budget id, cat id, every, period 

    function get_targets($database, $number_format, $budget_id, $user_currency_id) {
        //$result = $database->select_fields_where("Target", "*", "BudgetID='$budget_id'");
        $result = $database->left_join_SQL("Target", "Category", "Target.ID AS 'TargetID', Target.CurrencyID, Target.Amount, Target.Every, Target.Period, Target.CategoryID, Target.Rollover, Category.Name AS 'CategoryName', Category.Color AS 'CategoryColor'", "Category.ID=Target.CategoryID", "Target.BudgetID='$budget_id'");
        
        $array = array();
        $total = 0;
        $total_target = "";
        $max=0;
        if (!$result)
            return $array;
        $num_rows = mysqli_num_rows($result);
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $target = new Target($row["TargetID"], $row["CurrencyID"], $row["Amount"], $row["Every"], $row["Period"], $row["CategoryID"], $row["Rollover"]);
            $target->set_category_name_color($row["CategoryName"], $row["CategoryColor"]);
            $target->set_equivalent($user_currency_id, $number_format);
            $target->set_formatted_amount($database, $number_format);
            if($target->get_equivalent()>$max)
                $max=$target->get_equivalent ();
            $total+=$target->get_equivalent();
            $target_data = $target->to_array();
            $target_data["budget_id"] = $budget_id;
            $array[] = $target_data;
        }
        $total_target = Utility::get_formatted_money($user_currency_id, $total, $number_format);

        if ($num_rows > 0) {
            $array[0]["target_total_formatted"] = $total_target;
            $array[0]["target_total"] = $total;
            $array[0]["max"] = $max;
        }

        return $array;
    }
    
    function validate_max_num_targets_per_budget($database,$budget_id) {
        $target_count = $database->count("Target", "BudgetID='$budget_id'");
        if($target_count>= TargetList::$MAX_NUMBER_TARGETS_PER_BUDGET){
            return "You can't add more than ".TargetList::$MAX_NUMBER_TARGETS_PER_BUDGET." targets";
        }
        return "valid";
    }    

    function validate_amount($amount) {
        if(trim($amount)=="")
            return "Amount is required";
        if (!is_numeric($amount))
            return "Amount has to be a number";
        if (floatval($amount) < Target::$MIN_AMOUNT)
            return "Amount is too small";
        if (floatval($amount) > Target::$MAX_AMOUNT)
            return "Amount is too large";
        return "valid";
    }
    
    function validate_frequency($database,$budget_id,$every,$period) {
        $result = $database->select_fields_where("Budget", "Start, End", "ID='$budget_id'");
        
        if (!$result)
            return "invalid";
        $num_rows = mysqli_num_rows($result);
        if ($num_rows == 1) {
            $row = mysqli_fetch_assoc($result);
            $start = $row["Start"];
            $end=$row["End"];
            $start_date=Utility::to_date_object($start,"Y-m-d");
            $end_date=Utility::to_date_object($end,"Y-m-d");
            
            $year_difference=0;
            $month_difference=0;

            $start_year=Utility::get_year_component($start_date);
            $start_month=Utility::get_month_component($start_date);
            
            $end_month=Utility::get_month_component($end_date);
            $end_year=Utility::get_year_component($end_date);            
            
            $year_difference=$end_year-$start_year;
            $month_difference=($end_month-$start_month)+$year_difference*12;
            if($month_difference==0)
                $month_difference++;
            
            $target_min_period=$every;
            if($period=="Y")
                $target_min_period*=12;
            
            if($month_difference<$target_min_period)
                return "Frequency is greater than budget duration";
            
            if($month_difference%$target_min_period>0)
                return "Budget duration isn't a multiple of frequency";
        }
        return "valid";  
    }    

    function validate_target_id($database, $target_id, $userID) {
        $result = $database->select_fields_where("Target", "BudgetID", "ID='$target_id'");
        if (!$result)
            return "invalid";
        $num_rows = mysqli_num_rows($result);
        if ($num_rows == 1) {
            $row = mysqli_fetch_assoc($result);
            $budget_id = $row["BudgetID"];
            return $this->validate_target_budget_id($database, $budget_id, $userID);
        }
        return "invalid";
    }

    function validate_target_category_id($database, $category_id, $userID) {
        $category_count = $database->count("Category", "UserID='$userID' AND ID='$category_id'");
        if ($category_count > 0)
            return "valid";
        return "invalid";
    }

    function validate_target_budget_id($database, $budget_id, $userID) {
        $budget_count = $database->count("Budget", "UserID='$userID' AND ID='$budget_id'");
        if ($budget_count > 0)
            return "valid";
        return "invalid";
    }

    function validate_target_currency_id($database, $currency_id, $userID) {
        $currency_count = $database->count("Currency", "ID='$currency_id'");
        if ($currency_count > 0)
            return "valid";
        return "invalid";
    }

}
