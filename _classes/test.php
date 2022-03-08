<?php

include "Utility.php";
include "User.php";

$month="6";
$year="2016";
$hour="1";
$minute="05";

$time_zone_offset=300;
        $start_date="$year-$month-1 00:00:00";
        $start_date_UTC=Utility::to_UTC_date_time($start_date, $time_zone_offset);
        $next_month_date=Utility::get_next_month($month, $year);
        $end_date=$next_month_date["year"]."-".$next_month_date["month"]."-1 $hour:$minute:00";
        $end_date_UTC=Utility::to_UTC_date_time($end_date, $time_zone_offset);
        
        echo $start_date_UTC;
        
        echo $end_date_UTC;

        $user=new User();
        //$user->populate_currencies();
        $amount=$user->convert_money(100, "6", "1");
        
        echo "amount: $amount";
        
        foreach ($user->get_currencies_in_dollar() as $key => $value) {
            echo $key.": ".$value."<br>";
        }
        
        echo "<br> now using the Utility one";
        
        echo "100 dollars in EURO: <br>";
       // echo Utility::convert_money_currency_symbol(100, "1", "6");
        
