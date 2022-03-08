<?php


class Analytics {

    function __construct() {
        
    }
    
    function get_transaction_years($database,$user_id,$current_month, $current_year,$time_zone_offset){
        $final_array=array();
        $min_date=Utility::to_date_object("$current_year-$current_month-1 00:00:00");
        $max_date=$min_date;
        $start_year=Utility::get_year_component($min_date);
        $end_year=$start_year;
        $init_year=$start_year;
        
        
        $SQL = " SELECT MIN(Transaction.Time) AS 'min_time', MAX(Transaction.Time) AS 'max_time'";
        $SQL.=" FROM Transaction";    
        $SQL.=" WHERE Transaction.UserID='$user_id'";  
        $result = $database->send_SQL($SQL);
        
        if (!$result){
            $year_item=array("year"=>$start_year);
            $final_array[]=$year_item;
            return $final_array;
        }
        while($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $min_date=Utility::get_local_date_object($row["min_time"], $time_zone_offset);
            $max_date=Utility::get_local_date_object($row["max_time"], $time_zone_offset);
            $start_year=Utility::get_year_component($min_date);
            $end_year=Utility::get_year_component($max_date);
        }
        if($end_year<$init_year)
            $end_year=$init_year;
        
        $final_array=Utility::get_years($start_year,$end_year);
        return $final_array;
    }
    
    function get_expense_categories($database,$user_id,$end_month,$end_year,$period,$time_zone_offset){
        $final_array = array();
        $period_fixed = $this->fix_period($period);
        $end_month++;
        while($end_month >12) {
            $end_month= 1;
            $end_year++;
        }
                
        $start_month = $end_month - $period_fixed;
        $start_year = $end_year;
        while($start_month < 1) {
            $start_month+= 12;
            $start_year--;
        }

        $end_date = "$end_year-$end_month-1 00:00:00";
        $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);
        
        $start_date = "$start_year-$start_month-1 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);        
        
        $SQL = " SELECT Category.ID, Category.Name FROM TransactionItem";
        $SQL.=" INNER JOIN Transaction";
        $SQL.=" ON Transaction.ID=TransactionItem.TransactionID ";
        $SQL.=" INNER JOIN Category";
        $SQL.=" ON TransactionItem.CategoryID=Category.ID"; 
        $SQL.=" WHERE Category.UserID='$user_id' AND Transaction.Time>='$start_date_UTC' AND Transaction.Time<='$end_date_UTC'";
        $SQL.=" Group By Category.ID";   
        $SQL.=" Order By Category.Name";
        
        $result = $database->send_SQL($SQL);
        
        if (!$result)
            return $array;
        while($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $item=array("id"=>$row["ID"],"name"=>ucfirst($row["Name"]));
            $array[]=$item;           
        }
        return $array;          
    }
    

    function get_transactions_monthly_overview($database, $start_month, $start_year, $time_zone_offset, $account_id, $user_id, $number_format, $user_currency_id,$date_format_id) {
        $final_array = array();
        $start_month = intval($start_month);
        $start_year = intval($start_year);
        $max_amount = 0;
        $total_converted_spending = 0;
        $total_converted_income = 0;
        $converted_savings = 0;
        $converted_overspending = 0;

        $converted_formatted_overspending = "";
        $converted_formatted_savings = "";
        $total_formatted_converted_spending = "";
        $total_formatted_converted_income = "";


        $start_date = "$start_year-$start_month-1 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);
        $end_month = $start_month + 1;
        $end_year = $start_year;
        if ($end_month > 12) {
            $end_month = 1;
            $end_year++;
        }
        $end_date = "$end_year-$end_month-1 00:00:00";
        $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);
        $acccount_SQL = $account_id == "all" ? "" : " AND Transaction.AccountID='$account_id'";
        $time_constraint = "AND Transaction.Time>='$start_date_UTC' AND Transaction.Time<'$end_date_UTC'";

        $SQL = " SELECT Payee.Name as 'payee', Currency.ID AS 'currencyID', SUM(Amount*Quantity) AS 'expense_sum', Transaction.Time as 'date' FROM TransactionItem";
        $SQL.=" INNER JOIN Transaction";
        $SQL.=" ON Transaction.ID=TransactionItem.TransactionID ";
        $SQL.=" LEFT JOIN Payee";
        $SQL.=" ON Payee.ID=Transaction.PayeeID ";        
        $SQL.=" INNER JOIN Account";
        $SQL.=" ON Account.ID=Transaction.AccountID";
        $SQL.=" INNER JOIN";
        $SQL.=" Currency ON";
        $SQL.=" Account.CurrencyID=Currency.ID";  
        $SQL.=" WHERE Transaction.UserID='$user_id' $acccount_SQL $time_constraint ";
        $SQL.=" Group By Transaction.ID";    
        //return $SQL;

        $result = $database->send_SQL($SQL);


        $num_days = cal_days_in_month(CAL_GREGORIAN, $start_month, $start_year);
        $formatted_converted_amount = $formatted_converted_amount = Utility::get_formatted_money($user_currency_id, 0, $number_format);
        for ($i = 1; $i <= $num_days;  ++$i) {
            $date_object = DateTime::createFromFormat("Y-m-d", "$start_year-$start_month-$i");
            $formatted_date=Utility::get_formatted_date($date_format_id, $date_object);
            $item = array("day" => $i, "month" => $start_month,"year"=>$start_year, "converted_amount" => 0, "formatted_converted_amount" => $formatted_converted_amount,"formatted_date"=>$formatted_date,"transaction_items"=>array());
            $final_array[$i - 1] = $item;
        }

        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $currency_id = $row["currencyID"];
            $amount = floatval($row["expense_sum"]);
            $date = $row["date"];
            $payee = ucfirst($row["payee"]);
            $local_date_object = Utility::get_local_date_object($date, $time_zone_offset);
            $day = intval(date_format($local_date_object, "j"));
            $formatted_amount=Utility::get_formatted_money($currency_id, $amount, $number_format);
            $transaction_item_array=array("payee"=>$payee,"formatted_amount"=>$formatted_amount);
            $converted_amount = Utility::convert_money($amount, $currency_id, $user_currency_id);
            $total_converted_spending+=$converted_amount;
            $final_array[$day - 1]["converted_amount"]+=$converted_amount;
            $converted_amount = $final_array[$day - 1]["converted_amount"];
            $formatted_converted_amount = Utility::get_formatted_money($user_currency_id, $final_array[$day - 1]["converted_amount"], $number_format);
            $final_array[$day - 1]["formatted_converted_amount"] = $formatted_converted_amount;
            $final_array[$day - 1]["transaction_items"][] = $transaction_item_array;

            if ($converted_amount > $max_amount)
                $max_amount = $converted_amount;
        }

        $total_formatted_converted_spending = Utility::get_formatted_money($user_currency_id, $total_converted_spending, $number_format);
        $acccount_sql = $account_id == "all" ? "" : " AND Income.AccountID='$account_id'";
        
        $SQL = " SELECT Currency.ID AS 'currencyID', SUM(Amount) AS 'income_sum' FROM Income";
        $SQL.=" INNER JOIN";
        $SQL.=" Account ON";
        $SQL.=" Account.ID=Income.AccountID";    
        $SQL.=" INNER JOIN";
        $SQL.=" Currency ON";
        $SQL.=" Account.CurrencyID=Currency.ID";
        $SQL.=" WHERE Income.UserID='$user_id' $acccount_sql AND Income.Time>='$start_date_UTC' AND Income.Time<'$end_date_UTC' ";
        $SQL.=" Group by Currency.ID";

        $result = $database->send_SQL($SQL);

        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $currency_id = $row["currencyID"];
            $amount = floatval($row["income_sum"]);
            $total_converted_income += Utility::convert_money($amount, $currency_id, $user_currency_id);
        }
        $total_formatted_converted_income = Utility::get_formatted_money($user_currency_id, $total_converted_income, $number_format);

        $converted_savings = $total_converted_income - $total_converted_spending;
        $converted_overspending = $total_converted_spending - $total_converted_income;

        $converted_formatted_savings = Utility::get_formatted_money($user_currency_id, abs($converted_savings), $number_format);
        $converted_formatted_overspending = Utility::get_formatted_money($user_currency_id, $converted_overspending, $number_format);
        if($converted_savings<0)
            $converted_formatted_savings="-$converted_formatted_savings";

        $final_array[0]["max_amount"] = $max_amount;
        $final_array[0]["formatted_income"] = $total_formatted_converted_income;
        $final_array[0]["formatted_spending"]=$total_converted_spending>0?"-".$total_formatted_converted_spending:$total_formatted_converted_spending;
        $final_array[0]["formatted_income"]=$total_formatted_converted_income;
        $final_array[0]["savings"]=$converted_savings;
        $final_array[0]["formatted_savings"]=$converted_formatted_savings;
        $final_array[0]["overspending"]=$converted_overspending;
        $final_array[0]["formatted_overspending"]=$converted_overspending>0?"-".$converted_formatted_overspending:$converted_formatted_overspending;

        return $final_array;
    }

    function get_budgets($database, $start_month, $start_year, $time_zone_offset, $user_id) {
        $final_array = array();
        $start_date = "$start_year-$start_month-1 00:00:00";
        //$start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);

        $end_month = $start_month + 1;
        $end_year = $start_year;
        if ($end_month > 12) {
            $end_month = 1;
            $end_year++;
        }
        $end_date = "$end_year-$end_month-1 00:00:00";
        //$end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);

        $result = $database->select_fields_where("Budget", "Name, ID ", "Start<='$start_date' AND End>='$end_date' AND UserID='$user_id'");
        if (!$result || mysqli_num_rows($result) == 0)
            return $final_array;

        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $item = array("budget_id" => $row["ID"], "budget_name" => ucfirst($row["Name"]));
            $final_array[] = $item;
        }
        return $final_array;
    }

    function get_budget_analytics($database, $init_start_month, $init_start_year, $time_zone_offset, $account_id, $user_id, $user_currency_id, $number_format, $budget_id, $date_format, $time_format) {

        $final_result = array();
        $until_year = $init_start_year;
        $until_month = $init_start_month;


        $start_date = "$init_start_year-$init_start_month-1 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);
        $start_date_object_UTC = Utility::to_UTC_date_object($start_date, $time_zone_offset);
        $num_months_since_budget_start = 0;
        $budget_num_months = 0;

        $init_end_month = $init_start_month + 1;
        $init_end_year = $init_start_year;
        if ($init_end_month > 12) {
            $init_end_month = 1;
            $init_end_year++;
        }

        $until_date = "$init_end_year-$init_end_month-1 00:00:00";
        $end_date = "$init_end_year-$init_end_month-1 00:00:00";
        $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);
        $acccount_SQL = $account_id == "all_accounts" ? "" : " AND Transaction.AccountID='$account_id'";

        $result = $database->select_fields_where("Budget", "Start, End", "ID='$budget_id'");
        if (!$result || mysqli_num_rows($result) == 0)
            return $final_result;

        $row = mysqli_fetch_assoc($result); //fetch the next row

        $budget_start = $row["Start"];
        $budget_end = $row["End"];

        $budget_start_date = Utility::to_UTC_date_object($row["Start"],0,"Y-m-d");
        $budget_end_date = Utility::to_UTC_date_object($row["End"], 0,"Y-m-d");
        $num_months_since_budget_start = Utility::month_difference($start_date_object_UTC, $budget_start_date);
        $budget_num_months = Utility::month_difference($budget_end_date, $budget_start_date);
        $months_back = 0;



        $target_list = new TargetList();
        $targets = $target_list->get_targets($database, $number_format, $budget_id, $user_currency_id);
        $max_percentage = 0;
        $max_amount = 0;

        for ($i = 0; $i < count($targets); $i++) {
            $start_month = $init_start_month;
            $start_year = $init_start_year;
            $until_month = $init_start_month;
            $until_year = $init_start_year;

            $rollover = $targets[$i]["target_rollover"];
            $rollover_text = "";
            $category_id = $targets[$i]["category_id"];
            $every_as_number = intval($targets[$i]["target_every_as_number"]);
            $target_amount = floatval($targets[$i]["target_amount_as_number"]);
            $target_currency_id = $targets[$i]["target_currency_id"];
            $category_name = $targets[$i]["target_category_name"];
            $category_color = $targets[$i]["target_category_color"];
            $formatted_period = $targets[$i]["target_formatted_period"];
            $budget_multiplier = 1;
            $target_converted_amount = Utility::convert_money($target_amount, $target_currency_id, $user_currency_id);
            $target_formatted_amount = Utility::get_formatted_money($user_currency_id, $target_converted_amount, $number_format);

            $target_text = "$target_formatted_amount";


            $period = $targets[$i]["target_period"];
            $total_budget_amount = 0;
            $total_budget_converted_amount = 0;
            $target_text_plural = false;

            if ($every_as_number > 1) {
                $target_text.=" / $every_as_number ";
                $target_text_plural = true;
            } else
                $target_text.=" /";


            if ($period == "Y") {
                $every_as_number*=12;
                $target_text.="year";
            } else
                $target_text.="month";
            if ($target_text_plural)
                $target_text.="s";

            if ($num_months_since_budget_start == 0 || $every_as_number == 1)
                $months_back = 0;
            else if ($num_months_since_budget_start > 0) {
                $months_back = $num_months_since_budget_start % $every_as_number;
            }

            $months_forward = $every_as_number - $months_back;
            $years_forward = intval($months_forward / 12);
            $months_forward = $months_forward - $years_forward * 12;

            $until_month+=$months_forward;
            if ($until_month > 12) {
                $until_month-=12;
                $until_year++;
            }
            $until_year+=$years_forward;

            $until_date = "$until_year-$until_month-1 00:00:00";
            $until_date_UTC = Utility::to_UTC_date_time_object($until_date, $time_zone_offset);
            $formatted_until_date = Utility::get_formatted_date($date_format, $until_date_UTC);
            $underspent_amount = 0;
            $formatted_underspent_amount = "";
            $overspent_amount = 0;
            $formatted_overspent_amount = "";
            $overspent = false;
            $years_back = intval($months_back / 12);
            $months_back = $months_back - $years_back * 12;
            if (!$rollover) { //if not rollover, we want to know how far back in time we want to calculate the spending
                $start_year-=$years_back;
                $start_month-=$months_back;
                if ($start_month < 1) {
                    $start_month+=12;
                    $start_year--;
                }
                $start_date = "$start_year-$start_month-1 00:00:00";
                $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);
                $start_date_object_UTC = Utility::to_UTC_date_time_object($start_date, $time_zone_offset);
                $total_budget_amount = $every_as_number * $target_amount;
            } else { //if rollover, we want to calculate all the spending since the begining of the budget
                $rollover_text = "(rollover)";
                $start_date = "$budget_start 00:00:00";
                $start_date_object_UTC = $budget_start_date;
                $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);
                $budget_multiplier = ceil(($num_months_since_budget_start + 1) / $every_as_number);
                $total_budget_amount = $budget_multiplier * $target_amount;
                // $until_date = "$budget_end 00:00:00";
                // $until_date_UTC=Utility::to_UTC_date_time_object($until_date, $time_zone_offset);
            }


            $formatted_until_date = Utility::get_formatted_date($date_format, $until_date_UTC);
            $formatted_start_date = Utility::get_formatted_date($date_format, $start_date_object_UTC);

            $total_budget_converted_amount = Utility::convert_money($total_budget_amount, $target_currency_id, $user_currency_id);

            $monthly_expenses = $this->get_expenses_for_category($database, $start_date_UTC, $end_date_UTC, $category_id, $account_id, $user_id, $user_currency_id, $number_format);

            $formatted_monthly_expenses = Utility::get_formatted_money($user_currency_id, $monthly_expenses["converted_amount"], $number_format);
            $expense_percentage = $monthly_expenses["converted_amount"] / ($total_budget_converted_amount/$every_as_number) * 100;

            if ($max_percentage < $expense_percentage)
                $max_percentage = $expense_percentage;

            $expense_percentage_formatted = Utility::formatNumber($expense_percentage, $number_format);
            if ($expense_percentage > 100) {
                $overspent = true;
                $overspent_amount = $monthly_expenses["converted_amount"] - $total_budget_converted_amount/$every_as_number;
                $formatted_overspent_amount = Utility::get_formatted_money($user_currency_id, $overspent_amount, $number_format);
            } else {
                $overspent = false;
                $underspent_amount = $total_budget_converted_amount/$every_as_number - $monthly_expenses["converted_amount"];
            }
            $formatted_underspent_amount = Utility::get_formatted_money($user_currency_id, $underspent_amount, $number_format);
            $spent_amount = $monthly_expenses["converted_amount"];
            if ($spent_amount > $max_amount)
                $max_amount = $spent_amount;
            if ($total_budget_converted_amount/$every_as_number > $max_amount)
                $max_amount = $total_budget_converted_amount/$every_as_number;
            $formatted_spent_amount = Utility::get_formatted_money($user_currency_id, $spent_amount, $number_format);

            $item = array("category_name" => ucfirst($category_name), "category_id" => $category_id, "category_color" => $category_color
                , "formatted_start_date" => $formatted_start_date, "formatted_period" => $formatted_period
                , "formatted_spending" => $formatted_monthly_expenses, "formatted_percentage" => $expense_percentage_formatted
                , "percentage" => $expense_percentage, "formatted_until_date" => $formatted_until_date
                , "overspent" => $overspent ? "yes" : "no", "formatted_overspent_amount" => $formatted_overspent_amount
                , "formatted_remaining_amount" => $formatted_underspent_amount, "num_months_since_budget_started" => $num_months_since_budget_start, "start_date" => $start_date_UTC, "end_date" => $end_date_UTC, "months_back" => $months_back,
                "budget_multiplier" => $budget_multiplier, "target_amount" => $target_amount, "budget_start" => $budget_start,
                "spent_amount" => $spent_amount, "formatted_spent_amount" => $formatted_spent_amount,
                "overspent_amount" => $overspent_amount, "planned_amount" => $total_budget_converted_amount/$every_as_number,
                "target_text" => $target_text, "remaining_amount" => $underspent_amount, "rollover_text" => $rollover_text
            );


            $final_result[] = $item;
        }

        if (count($final_result) > 0) {
            $final_result[0]["max_percentage"] = $max_percentage;
            $final_result[0]["max_amount"] = $max_amount;
        }

        return $final_result;
    }

    function get_expenses_for_category($database, $start_date_UTC, $end_date_UTC, $category_id, $account_id, $user_id, $user_currency_id, $number_format) {

        $final_array = array();
        $acccount_SQL = $account_id == "all_accounts" ? "" : " AND Transaction.AccountID='$account_id'";
        $time_constraint = "AND Transaction.Time>='$start_date_UTC' AND Transaction.Time<'$end_date_UTC'";

        $SQL = " SELECT Currency.ID AS 'currencyID', SUM(Amount*Quantity) AS 'expense_sum' FROM TransactionItem";
        $SQL.=" INNER JOIN";
        $SQL.=" Category ON Category.ID=TransactionItem.CategoryID";
        $SQL.=" INNER JOIN Transaction";
        $SQL.=" ON Transaction.ID=TransactionItem.TransactionID ";
        $SQL.=" INNER JOIN Account";
        $SQL.=" ON Account.ID=Transaction.AccountID";
        $SQL.=" INNER JOIN";
        $SQL.=" Currency ON";
        $SQL.=" Account.CurrencyID=Currency.ID";
        $SQL.=" WHERE Category.ID='$category_id' AND Transaction.UserID='$user_id' $acccount_SQL $time_constraint ";
        $SQL.=" Group by Currency.ID";

        $result = $database->send_SQL($SQL);

        $index = 0;
        $monthly_total = 0;
        $formatted_amount = "";

        if (!$result) {
            
        } else {
            while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
                $currency_id = $row["currencyID"];
                $amount = floatval($row["expense_sum"]);
                if ($index > 0)
                    $formatted_amount.=" + ";
                $formatted_amount.=Utility::get_formatted_money($currency_id, $amount, $number_format);
                $converted_amount = Utility::convert_money($amount, $currency_id, $user_currency_id);
                $monthly_total+=$converted_amount;
                $index++;
            }

            $formatted_converted_amount = Utility::get_formatted_money($user_currency_id, $monthly_total, $number_format);

            $item = array("formatted_converted_amount" => $formatted_converted_amount, "converted_amount" => $monthly_total, "formatted_amount" => $formatted_amount);
            $final_array = $item;
        }
        return $final_array;
    }

    function get_monthly_expenses_stats($database, $start_day, $start_month, $start_year, $time_zone_offset, $account_id, $user_id, $user_currency_id, $number_format, $period, $category) {
        $final_array = array();
        $max_amount = 0;
        $num_months = $this->get_num_expenses_months($database, $start_day, $start_month, $start_year, $time_zone_offset, $account_id, $user_id);
        $avg_start_month=$start_month+1;
        $avg_start_year=$start_year;
        if($avg_start_month>12){
            $avg_start_month=1;
            $avg_start_year++;
        }
        
        $avg_start_date = "$avg_start_year-$avg_start_month-$start_day 00:00:00";
        $avg_start_date_UTC = Utility::to_UTC_date_time($avg_start_date, $time_zone_offset);
                
        $category_SQL=trim($category)=="all"?"":"AND TransactionItem.CategoryID='$category'";

        $acccount_SQL = $account_id == "all_accounts" ? "" : " AND Transaction.AccountID='$account_id'";

        /** get the average * */
        $total_average_amount = 0;
        $total_average = 0;
        $total_amount=0;

        $SQL = " SELECT Currency.ID AS 'currencyID', SUM(Amount*Quantity) AS 'expense_sum' FROM TransactionItem";
        $SQL.=" INNER JOIN";
        $SQL.=" Transaction";
        $SQL.=" ON Transaction.ID=TransactionItem.TransactionID";
        $SQL.=" INNER JOIN";
        $SQL.=" Account ON";
        $SQL.=" Account.ID=Transaction.AccountID";
        $SQL.=" INNER JOIN";
        $SQL.=" Currency ON";
        $SQL.=" Account.CurrencyID=Currency.ID";
        $SQL.=" WHERE Transaction.UserID='$user_id' $acccount_SQL AND Transaction.Time<'$avg_start_date_UTC' $category_SQL";
        $SQL.=" Group by Currency.ID";

        $result = $database->send_SQL($SQL);
        if (!$result) {
            
        } else {
            while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
                $currency_id = $row["currencyID"];
                $amount = floatval($row["expense_sum"]);
                $converted_amount = Utility::convert_money($amount, $currency_id, $user_currency_id);
                $total_average_amount+=$converted_amount;
            }
        }

        if (($num_months+1) > 0) {
            $total_average = $total_average_amount / ($num_months+1);
        }

        $total_average_formatted = Utility::get_formatted_money($user_currency_id, $total_average, $number_format);

        $months_array = $this->get_total_expenses_monthly_periods($database, $start_month, $start_year, $period, $time_zone_offset, $acccount_SQL, $user_id, $user_currency_id, $number_format,$category_SQL);
            for ($i = 0; $i < count($months_array); ++$i) {
                $above_average=$months_array[$i]["converted_amount"]-$total_average;
                $months_array[$i]["above_average"]=$above_average;
                $formatted_above_average=Utility::get_formatted_money($user_currency_id, $above_average, $number_format);
                $months_array[$i]["formatted_above_average_amount"]=$formatted_above_average;
                $below_average=$total_average-$months_array[$i]["converted_amount"];
                $months_array[$i]["below_average"]=$below_average;
                $formatted_below_average=Utility::get_formatted_money($user_currency_id, $below_average, $number_format);
                $months_array[$i]["formatted_below_average_amount"]=$formatted_below_average;
                $above_percentage=$total_average==0?0:$above_average/$total_average*100;
                $formatted_above_percentage=Utility::formatNumber(floatval($above_percentage), $number_format);
                $months_array[$i]["formatted_above_average_percentage"]=$formatted_above_percentage;
                $below_percentage=$total_average==0?0:$below_average/$total_average*100;
                $formatted_below_percentage=Utility::formatNumber(floatval($below_percentage), $number_format);
                $months_array[$i]["formatted_below_average_percentage"]=$formatted_below_percentage;
                $total_amount+=$months_array[$i]["converted_amount"];
                
                $final_array[] = $months_array[$i];
                if ($max_amount < $months_array[$i]["converted_amount"])
                    $max_amount = $months_array[$i]["converted_amount"];
            }
            
            $total_amount_formatted = Utility::get_formatted_money($user_currency_id, $total_amount, $number_format);

        

        if ($max_amount < $total_average)
            $max_amount = $total_average;

        if (count($final_array) > 0){
            $final_array[0]["max_amount"] = $max_amount;
            $final_array[0]["formatted_total_amount"]=$total_amount_formatted;
        }

        $average_item = array("expense_title" => "", "expense_title_class" => "total_avg_monthly_expense_bar", "expense_title_label_class" => "total_avg_monthly_expense_bar_label", "formatted_converted_amount" => $total_average_formatted, "converted_amount" => $total_average);
        $final_array[] = $average_item;

        return $final_array;
    }

    function get_monthly_income_vs_expense_stats($database, $start_day, $start_month, $start_year, $time_zone_offset, $account_id, $user_id, $user_currency_id, $number_format, $period, $sort_by="present_first") {
        $final_array = array();
        $max_amount = 0;
        $max_saving=0;
        $min_saving=0;
        $total_income=0;
        $total_expense=0;
        $total_savings=0;
        $total_overspending=0;
        $formatted_total_income="";
        $formatted_total_expense="";
        $formatted_total_overspending="";
        
        //$num_months = $this->get_num_expenses_months($database, $start_day, $start_month, $start_year, $time_zone_offset, $account_id, $user_id);
        $start_date = "$start_year-$start_month-$start_day 00:00:00";
        //$start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);

        $acccount_SQL = $account_id == "all_accounts" ? "" : " AND Transaction.AccountID='$account_id'";
        $income_account_SQL = $account_id == "all_accounts" ? "" : " AND Income.AccountID='$account_id'";

        $expense_array = $this->get_total_expenses_monthly_periods($database, $start_month, $start_year, $period, $time_zone_offset, $acccount_SQL, $user_id, $user_currency_id, $number_format,"");
        $income_array = $this->get_total_income_monthly_periods($database, $start_month, $start_year, $period, $time_zone_offset, $income_account_SQL, $user_id, $user_currency_id, $number_format);

        $past_first = $sort_by == "past_first" ? true : false;
        $i = $past_first ? count($expense_array) - 1 : 0;

        for ($j = 0; $j < count($expense_array); ++$j) {
            $income=floatval($income_array[$i]["converted_amount"]);
            $total_income+=$income;
            $expense=floatval($expense_array[$i]["converted_amount"]);
            $total_expense+=$expense;
            $saving = $income - $expense;
            if($j==0){
                $max_saving=$saving;
                $min_saving=$saving;
            }
            if($saving>$max_saving)
                $max_saving=$saving;
            if($saving<$min_saving)
                $min_saving=$saving;
            $formatted_saving = Utility::get_formatted_money($user_currency_id, abs($saving), $number_format);
            if($saving<0)
                $formatted_saving="-$formatted_saving";
            $formatted_converted_expense=Utility::get_formatted_money($user_currency_id, $expense, $number_format);
            if($expense > 0 )
                $formatted_converted_expense="-$formatted_converted_expense";
            $overspending = $expense - $income;
            $formatted_overspending = Utility::get_formatted_money($user_currency_id, $overspending, $number_format);
            if ($overspending > 0)
                $formatted_overspending = "-" . $formatted_overspending;

            if ($max_amount < $expense_array[$i]["converted_amount"])
                $max_amount = $expense_array[$i]["converted_amount"];

            if ($max_amount < $income_array[$i]["converted_amount"])
                $max_amount = $income_array[$i]["converted_amount"];

            $item = array("month_label"=>$expense_array[$i]["month_label"],"month_string" => $expense_array[$i]["expense_title"], "expense_formatted_converted_amount" => $formatted_converted_expense,
                "income_formatted_converted_amount" => $income_array[$i]["formatted_converted_amount"],
                "income_converted_amount" => $income_array[$i]["converted_amount"], "expense_converted_amount" => $expense_array[$i]["converted_amount"],
                "income_formatted_amount" => $income_array[$i]["formatted_amount"], "expense_formatted_amount" => $expense_array[$i]["formatted_amount"],
                "formatted_overspending" => $formatted_overspending, "formatted_savings" => $formatted_saving, "saving"=>$saving
            );

            $final_array[] = $item;
            $i = $past_first ? --$i : ++$i;
        }
        $formatted_total_expense=Utility::get_formatted_money($user_currency_id, $total_expense, $number_format);
        if($total_expense>0)
            $formatted_total_expense="-$formatted_total_expense";
        $formatted_total_income=Utility::get_formatted_money($user_currency_id, $total_income, $number_format);
       
        $total_savings=$total_income-$total_expense;
        $formatted_total_savings=Utility::get_formatted_money($user_currency_id, abs($total_savings), $number_format);
        if($total_savings<0)
            $formatted_total_savings="-$formatted_total_savings";
        $total_overspending=$total_expense-$total_income;
        $formatted_total_overspending=Utility::get_formatted_money($user_currency_id, $total_overspending, $number_format);
        if($total_overspending>0)
            $formatted_total_overspending="-$formatted_total_overspending";
        if (count($final_array) > 0) {
            $final_array[0]["max_amount"] = $max_amount;
            $final_array[0]["formatted_total_overspending"] = $formatted_total_overspending;
            $final_array[0]["formatted_total_savings"] = $formatted_total_savings;
            $final_array[0]["total_savings"] = $total_savings;
            $final_array[0]["formatted_total_income"] = $formatted_total_income;
            $final_array[0]["formatted_total_expense"] = $formatted_total_expense;
            $final_array[0]["max_saving"] = $max_saving;
            $final_array[0]["min_saving"] = $min_saving;
        }
        return $final_array;
    }

    function get_total_expenses_monthly_periods($database, $start_month, $start_year, $period, $time_zone_offset, $acccount_SQL, $user_id, $user_currency_id, $number_format,$category_SQL) {
        $final_array = array();
        $period_fixed = $this->fix_period($period);
        $start_date = "$start_year-$start_month-1 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);
        $end_month = $start_month + 1;
        $end_year = $start_year;
        $formatted_amount = "";
        if ($end_month > 12) {
            $end_month = 1;
            $end_year++;
        }

        $end_date = "$end_year-$end_month-1 00:00:00";
        $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);

        for ($i = 1; $i <= $period_fixed; $i++) {
            $formatted_amount = "";
            $monthly_total = 0;
            if ($i > 1) {
                $end_month = $start_month;
                $end_year = $start_year;
                --$start_month;
                if ($start_month < 1) {
                    $start_month = 12;
                    --$start_year;
                }
                $start_date = "$start_year-$start_month-1 00:00:00";
                $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);

                $end_date = "$end_year-$end_month-1 00:00:00";
                $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);
            }
            $SQL = " SELECT Currency.ID AS 'currencyID', SUM(Amount*Quantity) AS 'expense_sum' FROM TransactionItem";
            $SQL.=" INNER JOIN";
            $SQL.=" Transaction";
            $SQL.=" ON Transaction.ID=TransactionItem.TransactionID";
            $SQL.=" INNER JOIN";
            $SQL.=" Account ON";
            $SQL.=" Account.ID=Transaction.AccountID";
            $SQL.=" INNER JOIN";
            $SQL.=" Currency ON";
            $SQL.=" Account.CurrencyID=Currency.ID";
            $SQL.=" WHERE Transaction.UserID='$user_id' $acccount_SQL AND Transaction.Time>='$start_date_UTC' AND Transaction.Time<'$end_date_UTC' $category_SQL";
            $SQL.=" Group by Currency.ID";

            $result = $database->send_SQL($SQL);

            $index = 0;

            if (!$result) {
                
            } else {
                while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
                    $currency_id = $row["currencyID"];
                    $amount = floatval($row["expense_sum"]);
                    if ($index > 0)
                        $formatted_amount.=" + ";
                    $formatted_amount.=Utility::get_formatted_money($currency_id, $amount, $number_format);
                    $converted_amount = Utility::convert_money($amount, $currency_id, $user_currency_id);
                    $monthly_total+=$converted_amount;
                    $index++;
                }

                $month_string = Utility::get_month_name($start_month) . " " . $start_year;
                $month_label=$start_month."/".$start_year;
                $month_class_name = $i == 1 ? "monthly_expense_bar" : "prev_monthly_expense_bar";
                $month_label_class_name = $i == 1 ? "monthly_expense_bar_label" : "prev_monthly_expense_bar_label";
                $formatted_converted_amount = Utility::get_formatted_money($user_currency_id, $monthly_total, $number_format);

                $item = array("month_label"=>$month_label,"expense_title" => $month_string, "expense_title_class" => $month_class_name, "expense_title_label_class" => $month_label_class_name, "formatted_converted_amount" => $formatted_converted_amount, "converted_amount" => $monthly_total, "formatted_amount" => $formatted_amount);
                $final_array[] = $item;
            }
        }

        return $final_array;
    }

    function get_total_income_monthly_periods($database, $start_month, $start_year, $period, $time_zone_offset, $acccount_SQL, $user_id, $user_currency_id, $number_format) {
        $final_array = array();
        $period_fixed = $this->fix_period($period);
        $start_date = "$start_year-$start_month-1 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);
        $end_month = $start_month + 1;
        $end_year = $start_year;
        $formatted_amount = "";
        if ($end_month > 12) {
            $end_month = 1;
            $end_year++;
        }

        $end_date = "$end_year-$end_month-1 00:00:00";
        $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);

        for ($i = 1; $i <= $period_fixed; $i++) {
            $formatted_amount = "";
            $monthly_total = 0;
            if ($i > 1) {
                $end_month = $start_month;
                $end_year = $start_year;
                --$start_month;
                if ($start_month < 1) {
                    $start_month = 12;
                    --$start_year;
                }
                $start_date = "$start_year-$start_month-1 00:00:00";
                $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);

                $end_date = "$end_year-$end_month-1 00:00:00";
                $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);
            }
            $SQL = " SELECT Currency.ID AS 'currencyID', SUM(Amount) AS 'income_sum' FROM Income";
            $SQL.=" INNER JOIN";
            $SQL.=" Account ON";
            $SQL.=" Account.ID=Income.AccountID";
            $SQL.=" INNER JOIN";
            $SQL.=" Currency ON";
            $SQL.=" Account.CurrencyID=Currency.ID";
            $SQL.=" WHERE Income.UserID='$user_id' $acccount_SQL AND Income.Time>='$start_date_UTC' AND Income.Time<'$end_date_UTC' ";
            $SQL.=" Group by Currency.ID";

            $result = $database->send_SQL($SQL);

            $index = 0;

            if (!$result) {
                
            } else {
                while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
                    $currency_id = $row["currencyID"];
                    $amount = floatval($row["income_sum"]);
                    if ($index > 0)
                        $formatted_amount.=" + ";
                    $formatted_amount.=Utility::get_formatted_money($currency_id, $amount, $number_format);
                    $converted_amount = Utility::convert_money($amount, $currency_id, $user_currency_id);
                    $monthly_total+=$converted_amount;
                    $index++;
                }

                $month_string = Utility::get_month_name($start_month) . " " . $start_year;
                $month_class_name = $i == 1 ? "monthly_income_bar" : "prev_monthly_income_bar";
                $month_label_class_name = $i == 1 ? "monthly_income_bar_label" : "prev_income_expense_bar_label";
                $formatted_converted_amount = Utility::get_formatted_money($user_currency_id, $monthly_total, $number_format);

                $item = array("income_title" => $month_string, "income_title_class" => $month_class_name, "income_title_label_class" => $month_label_class_name, "formatted_converted_amount" => $formatted_converted_amount, "converted_amount" => $monthly_total, "formatted_amount" => $formatted_amount);
                $final_array[] = $item;
            }
        }

        return $final_array;
    }

    function fix_period($period) {
        if (intval($period) != 6 && intval($period) != 3 && intval($period) != 12)
            return 6;
        return intval($period);
    }

    function get_num_expenses_months($database, $start_day, $start_month, $start_year, $time_zone_offset, $account_id, $user_id) {
        $num_months = 0;
        $start_date = "$start_year-$start_month-$start_day 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);

        $acccount_SQL = $account_id == "all_accounts" ? "" : " AND Transaction.AccountID='$account_id'";

        $SQL = " SELECT DISTINCT DATE_FORMAT(Transaction.Time,'%Y-%m') AS 'monthcomponent' FROM TransactionItem";
        $SQL.=" INNER JOIN";
        $SQL.=" Transaction";
        $SQL.=" ON Transaction.ID=TransactionItem.TransactionID";
        $SQL.=" WHERE Transaction.UserID='$user_id' $acccount_SQL AND Transaction.Time<'$start_date_UTC'";

        $result = $database->send_SQL($SQL);
        if (!$result)
            return $num_months;
        $num_months = mysqli_num_rows($result);
        return $num_months;
    }

    function get_AVG_expenses_2($database, $start_day, $start_month, $start_year, $time_zone_offset, $filter_by, $account_id, $user_id, $user_currency_id, $number_format,$category_id="") {
        $array = array();
        $final_array = array();
        
        $max=0;

        $num_months = $this->get_num_expenses_months($database, $start_day, $start_month, $start_year, $time_zone_offset, $account_id, $user_id);
        $category_where=$this->fix_category_id($database, $category_id);

        $start_date = "$start_year-$start_month-$start_day 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);

        $filter_by_fixed = $this->fix_filter_by($filter_by);
        $acccount_SQL = $account_id == "all_accounts" ? "" : " AND Transaction.AccountID='$account_id'";

        $SQL = " SELECT Payee.ID AS 'payeeid', Payee.Name AS 'payee', DATE_FORMAT(Transaction.Time,'%Y-%m') AS 'monthcomponent',Item.ID AS 'itemid', Category.ID AS 'categoryid', Currency.ID AS 'currencyID',Item.Name AS 'itemname', Category.Color AS 'catcolor', Category.Name AS 'catname', SUM(Amount*Quantity) AS 'expense_sum' FROM TransactionItem";
        $SQL.=" INNER JOIN";
        $SQL.=" Transaction";
        $SQL.=" ON Transaction.ID=TransactionItem.TransactionID";
        $SQL.=" INNER JOIN";
        $SQL.=" Payee";
        $SQL.=" ON Transaction.PayeeID=Payee.ID";        
        $SQL.=" INNER JOIN";
        $SQL.=" Category  ON";
        $SQL.=" TransactionItem.CategoryID=Category.ID";
        $SQL.=" INNER JOIN";
        $SQL.=" Item ON";
        $SQL.=" TransactionItem.ItemID=Item.ID";
        $SQL.=" INNER JOIN";
        $SQL.=" Account ON";
        $SQL.=" Account.ID=Transaction.AccountID";
        $SQL.=" INNER JOIN";
        $SQL.=" Currency ON";
        $SQL.=" Account.CurrencyID=Currency.ID";
        $SQL.=" WHERE Transaction.UserID='$user_id' $acccount_SQL AND Transaction.Time<'$start_date_UTC' $category_where";
        $SQL.=" Group by DATE_FORMAT(Transaction.Time,'%Y-%m'),Currency.ID,$filter_by_fixed ";
        $result = $database->send_SQL($SQL);

        if (!$result) {/* do nothing */
        }

        $to_add = true;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $to_add = true;
            $currency_id = $row["currencyID"];
            $category_id = $row["categoryid"];
            $item_id = $row["itemid"];
            $payee_id = $row["payeeid"];
            $amount = floatval($row["expense_sum"]);
            $count = 1;
            $converted_amount = Utility::convert_money($amount, $currency_id, $user_currency_id);
            $month_component = $row["monthcomponent"];

            for ($i = 0; $i < count($array); $i++) {
                if (($filter_by_fixed == "Category.ID" && $array[$i]["category_id"] == $category_id && $month_component == $array[$i]["monthcomponent"]) || ($filter_by_fixed == "Item.ID" && $array[$i]["item_id"] == $item_id && $month_component == $array[$i]["monthcomponent"]) || ($filter_by_fixed == "Payee.ID" && $array[$i]["payee_id"] == $payee_id && $month_component == $array[$i]["monthcomponent"])) {
                    $prev_converted_amount = floatval($array[$i]["converted_amount"]);
                    $accumulative_amount = $prev_converted_amount + $converted_amount;
                    $array[$i]["converted_amount"] = $accumulative_amount;
                    $to_add = false;
                }
            }
            if ($to_add) {

                $item = array("monthcomponent" => $month_component, "item_id" => $row["itemid"],"payee_id" => $row["payeeid"], "category_id" => $row["categoryid"], "currency_id" => $row["currencyID"], "category_name" => ucfirst($row["catname"]),"payee" => ucfirst($row["payee"]), "category_color" => $row["catcolor"], "item_name" => ucfirst($row["itemname"]),
                    "converted_amount" => $converted_amount, "total_count" => $count
                );
                $array[] = $item;
            }
        }

        for ($j = 0; $j < count($array); $j++) {
            $to_add = true;
            $cat_name = $array[$j]["category_name"];
            $cat_color = $array[$j]["category_color"];
            $item_name = $array[$j]["item_name"];
            $payee_name = $array[$j]["payee"];
            $item_id = $array[$j]["item_id"];
            $payee_id = $array[$j]["payee_id"];
            $currency_id = $array[$j]["currency_id"];
            $category_id = $array[$j]["category_id"];
            $converted_amount = $array[$j]["converted_amount"];
            $avg_amount = $converted_amount / $num_months;
            $avg_formatted_converted_amount = Utility::get_formatted_money($user_currency_id, $avg_amount, $number_format);

            for ($k = 0; $k < count($final_array); $k++) {
                if (($filter_by_fixed == "Category.ID" && $final_array[$k]["category_id"] == $category_id) || ($filter_by_fixed == "Item.ID" && $final_array[$k]["item_id"] == $item_id)|| ($filter_by_fixed == "Payee.ID" && $final_array[$k]["payee_id"] == $payee_id)) {
                    $prev_converted_amount = floatval($final_array[$k]["converted_amount"]);
                    $prev_total_count = intval($final_array[$k]["total_count"]);
                    $prev_total_count++;
                    $accumulative_amount = $prev_converted_amount + $converted_amount;
                    $final_array[$k]["converted_amount"] = $accumulative_amount;
                    $final_array[$k]["total_count"] = $prev_total_count;
                    //$avg_amount = $accumulative_amount / $prev_total_count;
                    $avg_amount = $accumulative_amount / $num_months;
                    if($max<$avg_amount)
                        $max=$avg_amount;
                    $avg_formatted_converted_amount = Utility::get_formatted_money($user_currency_id, $avg_amount, $number_format);
                    $final_array[$k]["formatted_avg_amount"] = $avg_formatted_converted_amount;
                    $final_array[$k]["avg_converted_amount"] = $avg_amount;
                    $to_add = false;
                }
            }
            if ($to_add) {
                $item = array("avg_converted_amount" => $avg_amount, "formatted_avg_amount" => $avg_formatted_converted_amount, "item_id" => $item_id, "category_id" => $category_id, "currency_id" => $currency_id, "category_name" => ucfirst($cat_name), "category_color" => $cat_color, "item_name" => ucfirst($item_name),"payee" => ucfirst($payee_name),
                    "converted_amount" => $converted_amount, "total_count" => 1,"payee_id"=>$payee_id
                );
                if($max<$avg_amount)
                        $max=$avg_amount;
                $final_array[] = $item;
            }
        }

        if (count($final_array) > 0) {
            $final_array[0]["max_amount"] = $max;
        }
        return $final_array;
    }

    function get_AVG_expenses($database, $start_day, $start_month, $start_year, $time_zone_offset, $filter_by, $account_id, $user_id, $user_currency_id, $number_format) {
        $array = array();
        $max_amount = 0;
        $total_converted_amount = 0;
        $total_count = 0;
        $start_date = "$start_year-$start_month-$start_day 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);

        $filter_by_fixed = $this->fix_filter_by($filter_by);
        $filter_by_2 = $filter_by_fixed == "Item.ID" ? "itemid" : "categoryid";
        $acccount_SQL = $account_id == "all_accounts" ? "" : " AND Transaction.AccountID='$account_id'";

        $SQL = " SELECT *, SUM(b.expense_sum) AS 'total_expense', COUNT(b.expense_sum) AS 'total_count' FROM ( ";
        $SQL.=" SELECT DATE_FORMAT(Transaction.Time,'%Y-%m') AS 'Time',Item.ID AS 'itemid', Category.ID AS 'categoryid', Currency.ID AS 'currencyID',Item.Name AS 'itemname', Category.Color AS 'catcolor', Category.Name AS 'catname', SUM(Amount*Quantity) AS 'expense_sum' FROM TransactionItem";
        $SQL.=" INNER JOIN";
        $SQL.=" Transaction";
        $SQL.=" ON Transaction.ID=TransactionItem.TransactionID";
        $SQL.=" INNER JOIN";
        $SQL.=" Category  ON";
        $SQL.=" TransactionItem.CategoryID=Category.ID";
        $SQL.=" INNER JOIN";
        $SQL.=" Item ON";
        $SQL.=" TransactionItem.ItemID=Item.ID";
        $SQL.=" INNER JOIN";
        $SQL.=" Account ON";
        $SQL.=" Account.ID=Transaction.AccountID";
        $SQL.=" INNER JOIN";
        $SQL.=" Currency ON";
        $SQL.=" Account.CurrencyID=Currency.ID";
        $SQL.=" WHERE Transaction.UserID='$user_id' $acccount_SQL AND Transaction.Time<'$start_date_UTC'";
        $SQL.=" Group by DATE_FORMAT(Transaction.Time,'%Y-%m'),Currency.ID,$filter_by_fixed ";
        $SQL.=" ) b ";
        $SQL.=" Group by b.currencyID, b.$filter_by_2 ";
        $SQL.=" Order by total_count DESC, total_expense DESC";
        $result = $database->send_SQL($SQL);

        if (!$result) {/* do nothing */
        }

        $to_add = true;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $to_add = true;
            $currency_id = $row["currencyID"];
            $category_id = $row["categoryid"];
            $item_id = $row["itemid"];
            $amount = floatval($row["total_expense"]);
            $count = floatval($row["total_count"]);
            $formatted_amount = Utility::get_formatted_money($currency_id, $amount, $number_format);
            $converted_amount = Utility::convert_money($amount, $currency_id, $user_currency_id);
            $avg_amount = $converted_amount / $count;
            $formatted_converted_amount = Utility::get_formatted_money($user_currency_id, $converted_amount, $number_format);
            $formatted_avg_amount = Utility::get_formatted_money($user_currency_id, $avg_amount, $number_format);

            $total_converted_amount+=$converted_amount;
            $total_count+=$count;

            if ($max_amount < $converted_amount)
                $max_amount = $converted_amount;
            for ($i = 0; $i < count($array); $i++) {
                if (($filter_by_fixed == "Category.ID" && $array[$i]["category_id"] == $category_id) || ($filter_by_fixed == "Item.ID" && $array[$i]["item_id"] == $item_id)) {
                    $prev_converted_amount = floatval($array[$i]["converted_amount"]);
                    $prev_count = floatval($array[$i]["total_count"]);
                    $prev_converted_amount+=$converted_amount;
                    /* if($prev_count<$count)
                      $prev_count=$count; */
                    $avg_amount = $prev_converted_amount / $prev_count;
                    $array[$i]["avg_converted_amount"] = $avg_amount;
                    $array[$i]["converted_amount"] = $prev_converted_amount;
                    $array[$i]["total_count"] = $prev_count;
                    $array[$i]["formatted_converted_amount"] = Utility::get_formatted_money($user_currency_id, $prev_converted_amount, $number_format);
                    $array[$i]["formatted_avg_amount"] = Utility::get_formatted_money($user_currency_id, $avg_amount, $number_format);
                    $array[$i]["formatted_amount"].=" + " . $formatted_amount;
                    if ($max_amount < $prev_converted_amount)
                        $max_amount = $prev_converted_amount;
                    $to_add = false;
                }
            }
            if ($to_add) {

                $item = array("avg_converted_amount" => $avg_amount, "formatted_avg_amount" => $formatted_avg_amount, "item_id" => $row["itemid"], "category_id" => $row["categoryid"], "currency_id" => $row["currencyID"], "category_name" => ucfirst($row["catname"]), "category_color" => $row["catcolor"], "item_name" => ucfirst($row["itemname"]),
                    "amount" => $amount, "converted_amount" => $converted_amount, "total_count" => $count, "formatted_converted_amount" => $formatted_converted_amount, "formatted_amount" => $formatted_amount
                );
                $array[] = $item;
            }
        }
        return $array;
    }
    
    function get_expenses_details($database,$filter_by, $start_date_UTC, $end_date_UTC, $account_SQL, $user_id, $user_currency_id, $number_format, $time_zone_offset, $date_format_id,$group_by="transaction") {
        $array = array();
        $group_by_sql="Transaction.ID";
        if($group_by=="name")
            $group_by_sql="Item.ID";
        else if($group_by=="payee")
            $group_by_sql="Payee.ID";
        
        $order_by_sql=$group_by=="transaction"?"Transaction.Time":"expense";
        $SQL = "SELECT Item.Name AS 'name', Currency.ID AS 'currencyID',Payee.Name AS 'payee_name', SUM(Amount*Quantity) AS 'expense',Transaction.Time as 'date' FROM TransactionItem";
        $SQL.=" INNER JOIN";
        $SQL.=" Transaction";
        $SQL.=" ON Transaction.ID=TransactionItem.TransactionID";
        $SQL.=" LEFT JOIN";
        $SQL.=" Payee  ON";
        $SQL.=" Transaction.PayeeID=Payee.ID";        
        $SQL.=" LEFT JOIN";
        $SQL.=" Category  ON";
        $SQL.=" TransactionItem.CategoryID=Category.ID";
        $SQL.=" LEFT JOIN";
        $SQL.=" Item ON";
        $SQL.=" TransactionItem.ItemID=Item.ID";
        $SQL.=" INNER JOIN";
        $SQL.=" Account ON";
        $SQL.=" Account.ID=Transaction.AccountID";
        $SQL.=" INNER JOIN";
        $SQL.=" Currency ON";
        $SQL.=" Account.CurrencyID=Currency.ID";
        $SQL.=" WHERE Transaction.Time>='$start_date_UTC' AND Transaction.Time<'$end_date_UTC' AND Transaction.UserID='$user_id' $account_SQL $filter_by";
        $SQL.=" Group by $group_by_sql";
        $SQL.=" Order by $order_by_sql";
        
        $result = $database->send_SQL($SQL);
        $sum=0;
        $max=0;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $currency_id = $row["currencyID"];
            $amount = floatval($row["expense"]);            
            $converted_amount = Utility::convert_money($amount, $currency_id, $user_currency_id);
            $sum+=$converted_amount;
            if($converted_amount>$max)
                $max=$converted_amount;
            $formatted_converted_amount = Utility::get_formatted_money($user_currency_id, $converted_amount, $number_format);
            $payee=$row["payee_name"];  
            $date=$row["date"];
            $local_date_object = Utility::get_local_date_object($date, $time_zone_offset);
            $formatted_date=Utility::get_formatted_date_NO_YEAR($date_format_id, $local_date_object);
         
            $array[]=array("payee_name"=>ucfirst($payee),"name"=>ucfirst($row["name"]),"formatted_converted_transaction_amount"=>$formatted_converted_amount,"formatted_date"=>$formatted_date,"converted_amount"=>$converted_amount,"percentage"=>0,"bar_percentage"=>0);

            }
            for($i=0;$i<sizeof($array);++$i){
                $percentage=0;
                $max_percentage=0;
                if($sum>0)
                    $percentage=$array[$i]["converted_amount"]/$sum*100;
                if($max>0)
                    $max_percentage=$array[$i]["converted_amount"]/$max*100;
                $array[$i]["percentage"]=Utility::formatNumber($percentage, $number_format);
                $array[$i]["bar_percentage"]=Utility::formatNumber($max_percentage, $number_format);
            }            
        
        return array_reverse($array);
    }
    
    function get_expenses($database, $start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $time_zone_offset, $filter_by, $account_id, $user_id, $user_currency_id, $number_format, $avg,$get_transactions) {
        $array = array();
        $max_amount = 0;
        $total_amount = 0;
        $start_date = "$start_year-$start_month-$start_day 00:00:00";
        $start_date_UTC = Utility::to_UTC_date_time($start_date, $time_zone_offset);
        $end_date = "$end_year-$end_month-$end_day 00:00:00";
        $end_date_UTC = Utility::to_UTC_date_time($end_date, $time_zone_offset);
        $filter_by_fixed = $this->fix_filter_by($filter_by);
        $acccount_SQL = $account_id == "all_accounts" ? "" : " AND Transaction.AccountID='$account_id'";

        $SQL = "SELECT Payee.ID AS 'payeeid', Payee.Name AS 'payee', Item.ID AS 'itemid', Category.ID AS 'categoryid', Currency.ID AS 'currencyID',Item.Name AS 'itemname', Category.Color AS 'catcolor', Category.Name AS 'catname', SUM(Amount*Quantity) AS 'expense' FROM TransactionItem";
        $SQL.=" INNER JOIN";
        $SQL.=" Transaction";
        $SQL.=" ON Transaction.ID=TransactionItem.TransactionID";
        $SQL.=" LEFT JOIN";
        $SQL.=" Payee";
        $SQL.=" ON Transaction.PayeeID=Payee.ID";        
        $SQL.=" LEFT JOIN";
        $SQL.=" Category  ON";
        $SQL.=" TransactionItem.CategoryID=Category.ID";
        $SQL.=" LEFT JOIN";
        $SQL.=" Item ON";
        $SQL.=" TransactionItem.ItemID=Item.ID";
        $SQL.=" INNER JOIN";
        $SQL.=" Account ON";
        $SQL.=" Account.ID=Transaction.AccountID";
        $SQL.=" INNER JOIN";
        $SQL.=" Currency ON";
        $SQL.=" Account.CurrencyID=Currency.ID";
        $SQL.=" WHERE Transaction.Time>='$start_date_UTC' AND Transaction.Time<'$end_date_UTC' AND Transaction.UserID='$user_id' $acccount_SQL";
        $SQL.=" Group by Currency.ID, $filter_by_fixed ";
        $SQL.=" Order by Expense DESC;";
        $result = $database->send_SQL($SQL);

        if (!$result) {/* do nothing */
        }

        $to_add = true;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $to_add = true;
            $item_name = is_null($row["itemname"]) ? "Unknown" : htmlspecialchars($row["itemname"], ENT_QUOTES);
            $cat_name = is_null($row["catname"]) ? "Unknown" : htmlspecialchars($row["catname"], ENT_QUOTES);
            $cat_color=is_null($row["catcolor"]) ? "white" : $row["catcolor"];
            $currency_id = $row["currencyID"];
            $category_id = $row["categoryid"];
            $item_id = $row["itemid"];
            $payee_id=$row["payeeid"];
            $amount = floatval($row["expense"]);
            $formatted_amount = Utility::get_formatted_money($currency_id, $amount, $number_format);
            $converted_amount = Utility::convert_money($amount, $currency_id, $user_currency_id);
            $formatted_converted_amount = Utility::get_formatted_money($user_currency_id, $converted_amount, $number_format);
            $total_amount+=$converted_amount;

            if ($max_amount < $converted_amount)
                $max_amount = $converted_amount;
            for ($i = 0; $i < count($array); $i++) {
                if (($filter_by_fixed == "Category.ID" && $array[$i]["category_id"] == $category_id) || ($filter_by_fixed == "Item.ID" && $array[$i]["item_id"] == $item_id) || ($filter_by_fixed=="Payee.ID" && $array[$i]["payee_id"] == $payee_id)) {
                    $prev_converted_amount = floatval($array[$i]["converted_amount"]);
                    $prev_converted_amount+=$converted_amount;
                    $array[$i]["converted_amount"] = $prev_converted_amount;
                    $array[$i]["formatted_converted_amount"] = Utility::get_formatted_money($user_currency_id, $prev_converted_amount, $number_format);
                    $array[$i]["formatted_amount"].=" + " . $formatted_amount;
                    if ($max_amount < $prev_converted_amount)
                        $max_amount = $prev_converted_amount;
                    $to_add = false;
                    /** to add payee**/
                }
            }
            if ($to_add) {

                $category_item = array("prev_display" => "none", "avg_display" => "none", "item_id" => $row["itemid"],"payee_id" => $row["payeeid"], "category_id" => $row["categoryid"], "currency_id" => $row["currencyID"], "category_name" => ucfirst($cat_name), "category_color" => $cat_color, "item_name" => ucfirst($item_name),"payee"=>$row["payee"],
                    "amount" => $amount, "converted_amount" => $converted_amount, "formatted_converted_amount" => $formatted_converted_amount, "formatted_amount" => $formatted_amount,"transactions"=>array(),"by_name"=>array(),"by_payee"=>array()
                );
                $filter_by_SQL=$this->fix_filter_by_SQL($filter_by, $category_id, $item_id);
                if($get_transactions && $filter_by!="by_payee"){
                    $category_item["transactions"]=$this->get_expenses_details($database, $filter_by_SQL, $start_date_UTC, $end_date_UTC, $acccount_SQL, $user_id, $user_currency_id, $number_format, $time_zone_offset, $_SESSION["user"]->get_DateFormat());                    
                }
                if($filter_by=="by_category"){
                    $category_item["by_name"]=$this->get_expenses_details($database, $filter_by_SQL, $start_date_UTC, $end_date_UTC, $acccount_SQL, $user_id, $user_currency_id, $number_format, $time_zone_offset, $_SESSION["user"]->get_DateFormat(),"name");
                    $category_item["by_payee"]=$this->get_expenses_details($database, $filter_by_SQL, $start_date_UTC, $end_date_UTC, $acccount_SQL, $user_id, $user_currency_id, $number_format, $time_zone_offset, $_SESSION["user"]->get_DateFormat(),"payee");   
                }
                $array[] = $category_item;
            }
        }
        $index = 0;
        for ($i = 0; $i < count($array); $i++) {
            if ($index == 0) {
                $array[$i]["max_amount"] = $max_amount;
                $array[$i]["total"] = $total_amount;
                $array[$i]["total_formatted"] = Utility::get_formatted_money($user_currency_id, $total_amount, $number_format);
            }
            $array[$i]["expense_percentage_unformatted"] = floatval($array[$i]["converted_amount"]) / $total_amount;
            $array[$i]["expense_percentage"] = Utility::formatNumber(floatval($array[$i]["converted_amount"]) / $total_amount * 100, $number_format);
            $index++;
        }

        $final_array = $array;
        if (trim($avg) == "yes") {
            $avg_expense_result = $this->get_AVG_expenses_2($database, $start_day, $start_month, $start_year, $time_zone_offset, $filter_by, $account_id, $user_id, $user_currency_id, $number_format);
            $final_array = $this->combine_expenses_AVG_expenese($array, $avg_expense_result, $filter_by,$user_currency_id, $number_format);
        }
        return $final_array;
    }

    function combine_expenses_AVG_expenese($this_month_result, $avg_expense_result, $filter_by,$user_currency_id,$number_format) {
        $max_amount = 0;


        for ($i = 0; $i < count($this_month_result); $i++) {
            $category_id = $this_month_result[$i]["category_id"];
            $item_id = $this_month_result[$i]["item_id"];
            $payee_id = $this_month_result[$i]["payee_id"];
            $this_month_result[$i]["avg_display"] = "none";
            $converted_amount = floatval($this_month_result[$i]["converted_amount"]);
            $prev_converted_amount = floatval($this_month_result[$i]["prev_month_converted_amount"]);
            if ($max_amount < $prev_converted_amount)
                $max_amount = $prev_converted_amount;
            if ($max_amount < $converted_amount)
                $max_amount = $converted_amount;
            //$max_amount=$converted_amount>$prev_month_converted_amount?$converted_amount:$prev_month_converted_amount;
            $this_month_result[$i]["avg_converted_amount"] = 0;
            $this_month_result[$i]["avg_formatted_converted_amount"] = Utility::get_formatted_money($user_currency_id, 0, $number_format);
            

            for ($j = 0; $j < count($avg_expense_result); $j++) {
                if (($filter_by == "by_category" && $avg_expense_result[$j]["category_id"] == $category_id) || ($filter_by == "by_item" && $avg_expense_result[$j]["item_id"] == $item_id) || ($filter_by == "by_payee" && $avg_expense_result[$j]["payee_id"] == $payee_id)) {
                    $avg_expense_count = floatval($avg_expense_result[$j]["total_count"]);
                    $avg_amount = floatval($avg_expense_result[$j]["avg_converted_amount"]);
                    // $max_amount=$avg_amount>$max_amount?$avg_amount:$max_amount;   
                    if ($max_amount < $avg_amount)
                        $max_amount = $avg_amount;
                    $this_month_result[$i]["avg_formatted_converted_amount"] = $avg_expense_result[$j]["formatted_avg_amount"];
                    $this_month_result[$i]["avg_expense_count"] = $avg_expense_count;
                    $this_month_result[$i]["avg_converted_amount"] = $avg_amount;
                    $this_month_result[$i]["avg_display"] = "block";
                    break;
                }
            }
        }

        if (count($this_month_result) > 0) {
            $this_month_result[0]["max_amount"] = $max_amount;
        }

        return $this_month_result;
    }

    function get_prev_month_expenses($database, $start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $time_zone_offset, $filter_by, $account_id, $user_id, $user_currency_id, $number_format,$prev_month_show, $avg) {
        $this_month_result = $this->get_expenses($database, $start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $time_zone_offset, $filter_by, $account_id, $user_id, $user_currency_id, $number_format, "no",true);
        $prev_day = intval($start_day);
        $prev_month = intval($start_month) - 1;
        $prev_year = intval($start_year);
        if ($prev_month == 0) {
            $prev_month = 12;
            $prev_year--;
        }
        $prev_month_array = $this->get_expenses($database, $prev_day, $prev_month, $prev_year, $start_day, $start_month, $start_year, $time_zone_offset, $filter_by, $account_id, $user_id, $user_currency_id, $number_format, "no",false);

        $max_amount_1 = count($this_month_result) > 0 ? floatval($this_month_result[0]["max_amount"]) : 0;
        $max_amount_2 = count($prev_month_array) > 0 ? floatval($prev_month_array[0]["max_amount"]) : 0;
        $max_amount_3=0;
        $max_amount = $max_amount_1 > $max_amount_2 ? $max_amount_1 : $max_amount_2;
        

        for ($i = 0; $i < count($this_month_result); $i++) {
            $category_id = $this_month_result[$i]["category_id"];
            $item_id = $this_month_result[$i]["item_id"];
            $payee_id = $this_month_result[$i]["payee_id"];
            $prev_display = "none";
            $converted_amount = floatval($this_month_result[$i]["converted_amount"]);
            $this_month_result[$i]["label_visible"] = "visible";
            $this_month_result[$i]["this_month_class"] = "alone_monthly_expense_bar_wrapper";
            $this_month_result[$i]["prev_month_converted_amount"] = 0;
            $this_month_result[$i]["prev_month_formatted_converted_amount"] = Utility::get_formatted_money($user_currency_id, 0, $number_format);

            foreach ($prev_month_array as $prev_key=>$prev_value) {
                if (($filter_by == "by_category" && trim($prev_value["category_id"]) == trim($category_id)) || ($filter_by == "by_item" && $prev_value["item_id"] == $item_id)|| ($filter_by == "by_payee" && $prev_value["payee_id"] == $payee_id)) {
                    $prev_month_converted_amount = floatval($prev_value["converted_amount"]);
                    $prev_month_amount = $prev_value["formatted_amount"];
                    $this_month_label_visible = $converted_amount >= $prev_month_converted_amount ? "visible" : "hidden";
                    $prev_month_label_visible = $prev_month_converted_amount > $converted_amount ? "visible" : "hidden";

                    $this_month_result[$i]["prev_month_formatted_converted_amount"] = $prev_value["formatted_converted_amount"];
                    $this_month_result[$i]["prev_month_expense_percentage"] = $prev_month_converted_amount / $max_amount * 100;
                    $this_month_result[$i]["prev_month_converted_amount"] = $prev_value["converted_amount"];
                    $this_month_result[$i]["prev_month_label_visible"] = $prev_month_label_visible;
                    $this_month_result[$i]["prev_month_class"] = "";
                    $this_month_result[$i]["this_month_class"] = "";
                    $this_month_result[$i]["label_visible"] = $this_month_label_visible;
                    $this_month_result[$i]["prev_month_formatted_amount"] = $prev_month_amount;
                    unset($prev_month_array[$prev_key]);
                    $prev_display = "block";
                    break;
                }
            }
            $this_month_result[$i]["display"] = "block";
            $this_month_result[$i]["prev_display"] = $prev_display;
        }
        
if($prev_month_show=="yes")
        foreach ($prev_month_array as $value) {
            $prev_formatted_amount = $value["formatted_amount"];
            $prev_formatted_converted_amount = $value["formatted_converted_amount"];
            $prev_month_expense_percentage = floatval($value["converted_amount"]) / $max_amount * 100;
            $category_id = $value["category_id"];
            $item_id = $value["item_id"];
            $payee_id = $value["payee_id"];

            $item = array("avg_display" => "none", "prev_month_formatted_amount" => $prev_formatted_amount, "category_id" => $category_id, "item_id" => $item_id,"payee_id"=>$payee_id,  "prev_month_label_visible" => "visible", "prev_display" => "block", "display" => "none", "category_name" => $value["category_name"], "category_color" => $value["category_color"], "item_name" => $value["item_name"],"payee"=>$value["payee"],
                "prev_month_formatted_converted_amount" => $prev_formatted_converted_amount, "prev_month_expense_percentage" => $prev_month_expense_percentage,
                "prev_month_converted_amount" => $value["converted_amount"], "prev_month_class" => "alone_prev_monthly_expense_bar_wrapper", "converted_amount" => 0
            );
            $this_month_result[] = $item;
        }

        if (count($this_month_result) > 0) {
            $this_month_result[0]["max_amount"] = $max_amount;
        }

        $final_array = $this_month_result;
        $avg_expense_result = $this->get_AVG_expenses_2($database, $start_day, $start_month, $start_year, $time_zone_offset, $filter_by, $account_id, $user_id, $user_currency_id, $number_format);
        $final_array = $this->combine_expenses_AVG_expenese($this_month_result, $avg_expense_result, $filter_by,$user_currency_id, $number_format);
       $max_amount_3 = count($avg_expense_result) > 0 ? floatval($avg_expense_result[0]["max_amount"]) : 0;

        if (count($final_array) > 0) {
            $final_array[0]["this_month_max_amount"] = $max_amount_1;
            $final_array[0]["prev_month_max_amount"] = $max_amount_2;
            $final_array[0]["avg_max_amount"] = $max_amount_3;
            $final_array[0]["this_month_text"]="$start_month/$start_year";
            $final_array[0]["prev_month_text"]="$prev_month/$prev_year";
        }        
        return $final_array;
    }
    
    function fix_filter_by_SQL($filter_by,$category_id,$item_id) {
        $filter_result = "AND Category.ID='$category_id'";
        if (trim($filter_by) == "by_item")
            $filter_result = "AND Item.ID='$item_id'";
        return $filter_result;
    }    

    function fix_filter_by($filter_by) {
        $filter_result = "Category.ID";
        if (trim($filter_by) == "by_item")
            $filter_result = "Item.ID";
        if(trim($filter_by)=="by_payee")
            $filter_result="Payee.ID";
        return $filter_result;
    }
    
  function fix_category_id($database,$category_id) {
      $cat_id= trim($category_id);
      if($cat_id==null || $cat_id=="")
              return "";
      $category_count = $database->count("Category", "ID='$cat_id'");
      if($category_count ==1)
          return " AND Category.ID='$category_id'";
      return "";
    }

}
