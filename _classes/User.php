<?php

class User {

    private $email;
    private $id;
    private $numberFormat;
    private $timeZone; /* 1=American, 2=European */
    private $status; /*1=Active, 2=Deleted, 3=Suspended, 4=Archived */
    private $currency_id;
    private $dateFormat;
    private $timeFormat;
    private $language;
    private $currencies_in_dollar;
    private $current_country;
    private $info_added;
    private $orientation;

    function __construct() {
        $this->currencies_in_dollar = array();
        $this->current_country = Utility::get_country_code_of_user();
    }

    function populate_currencies() {
        $this->currencies_in_dollar = array();
        $currencies = CurrencyList::get_currencies();
        foreach ($currencies as $currency_array) {
            $currency = $currency_array["Abbreviation"];
            $value_in_dollar = Utility::convert_money_currency_symbol(1, $currency, "USD");
            $this->currencies_in_dollar[$currency] = $value_in_dollar;
        }
    }

    function populate_currency($abbreviation) {
        $this->currencies_in_dollar[$abbreviation] = Utility::convert_money_currency_symbol(1, $abbreviation, "USD");
    }

    function convert_money($amount, $from_currency_id, $to_currency_id) {
        $from_currency_array = CurrencyList::get_currency($from_currency_id . "");
        $from_currency = $from_currency_array["Abbreviation"];

        $to_currency_array = CurrencyList::get_currency($to_currency_id . "");
        $to_currency = $to_currency_array["Abbreviation"];

        if ($from_currency == $to_currency)
            return $amount;
        $from_currency_exist = array_key_exists($from_currency, $this->currencies_in_dollar);
        $to_currency_exist = array_key_exists($to_currency, $this->currencies_in_dollar);
        if (!$from_currency_exist)
            $this->populate_currency($from_currency);
        if (!$to_currency_exist)
            $this->populate_currency($to_currency);

        $from_currency_rate = $this->currencies_in_dollar[$from_currency];
        $to_currency_rate = $this->currencies_in_dollar[$to_currency];
        return $amount * ($from_currency_rate / $to_currency_rate);

        return $amount;
    }

    function get_TimeFormat() {
        return $this->timeFormat;
    }

    function set_TimeFormat($timeFormat) {
        $this->timeFormat = $timeFormat;
    }

    function get_currencies_in_dollar() {
        return $this->currencies_in_dollar;
    }

    function get_current_country_code() {
        return $this->current_country;
    }

    function get_DateFormat() {
        return $this->dateFormat;
    }

    function set_DateFormat($dateFormat) {
        $this->dateFormat = $dateFormat;
    }

    function get_ID() {
        return $this->id;
    }

    function get_email() {
        return $this->email;
    }

    function get_NumberFormat() {
        return $this->numberFormat;
    }

    function set_NumberFormat($numberFormat) {
        $this->numberFormat = $numberFormat;
    }

    function get_TimeZone() {
        return $this->timeZone;
    }

    function get_currency_id() {
        return $this->currency_id;
    }

    function set_currency_id($currency_id) {
        $this->currency_id = $currency_id;
    }

    function get_language() {
        return $this->language;
    }

    function get_info_added() {
        return $this->info_added;
    }

    function get_orientation() {
        return $this->orientation;
    }

    function set_info_added($info_added) {
        $this->info_added = $info_added;
    }

    function set_orientation($orientation) {
        $this->orientation = $orientation;
    }
    
    function validate_old_password($database, $user_id,$password) {
        $encrypted_password=  sha1($password);
        $user_count = $database->count("User", "ID='$user_id' AND Password='$encrypted_password'");
        return $user_count == 1;
    }    

    function user_exists($database, $email) {
        $user_count = $database->count("User", "Email='$email'");
        return $user_count == 1;
    }
    
    function validate_code($database, $email,$code) {
        $user_count = $database->count("User", "Email='$email' AND Code IS NOT NULL AND Code='$code'");
        return $user_count ==1;
    }    
    
    function edit_orientation($database,$orientation){
        $result=array("result"=>"fail","orientation",0);
        $values = array("Orientation" => $orientation);
        $final = $database->update("User", $values, "ID='$this->id'");
            if(!$final)
                return $result;
            $result["result"] = "success";
            $result["orientation"]=1;
            return $result;
    }

    function add_user($database, $email, $password, $confirm_password, $token_id) {
        $result = array();
        $email_valid = $this->user_exists($database, $email) ? "User exists already" : $this->validate_email($email);
        $password_valid = $this->validate_password($password);
        $confirm_password_valid = $this->validate_confirm_password($password, $confirm_password);
        //$token_valid = $token_id == "AAFDAFKJD27687286" ? "valid" : "Token invalid";
        $token_valid=$this->validate_token($database, $token_id)?"valid":"Invalid";
        $result["email"] = $email_valid;
        $result["password"] = $password_valid;
        $result["confirm_password"] = $confirm_password_valid;
        $result["token"] = $token_valid;
        $result["result"] = "fail";

        $user_valid = $email_valid == "valid" && $password_valid == "valid" && $confirm_password_valid == "valid" && $token_valid == "valid";

        if ($user_valid && $email_valid) {
            $country_code = Utility::get_country_code_of_user();
            $default_language = CurrencyList::get_default_language($country_code);
            $default_number_format = CurrencyList::get_default_number_format($country_code);
            $default_date_format = CurrencyList::get_default_date_format($country_code);
            $default_time_format = CurrencyList::get_default_time_format($country_code);
            $default_currency_id = CurrencyList::get_default_currency($country_code);

            $values = array("Email" => $email, "Password" => sha1($password), "NumberFormat" => $default_number_format, "CurrencyID" => $default_currency_id, "TimeFormat" => $default_time_format, "DateFormat" => $default_date_format, "Language" => $default_language,"Status"=>1);
            $db_result = $database->insert("User", $values);
            if(!$db_result)
                return $result;
            $database->delete_where("Code", "CodeNumber='$token_id'");
            $result["result"] = "success";
        }

        return $result;
    }

    function authenticate($database, $email, $password) {
        $password = sha1($password);
        $result = $database->select_fields_where("User", "*", "Email='" . $email . "' AND Password='" . $password . "' AND Status='1'");
        if (!$result)
            return null;
        $row_count = mysqli_num_rows($result);
        if ($row_count == 1) {
            $row = mysqli_fetch_array($result);
            $this->email = $row["Email"];
            $this->id = $row["ID"];
            $this->timeZone = $row["TimeZone"];
            $this->numberFormat = $row["NumberFormat"];
            $this->currency_id = $row["CurrencyID"] . "";
            $this->dateFormat = $row["DateFormat"];
            $this->timeFormat = $row["TimeFormat"];
            $this->language = $row["Language"];
            $this->info_added = $row["InfoAdded"];
            $this->orientation = $row["Orientation"];
            $this->status=$row["Status"];
            if($this->info_added=="0"){
                $this->populate_info($database,$this->id,$this->currency_id);
            }
                
            //$this->populate_currencies();
            return $this;
        }
        return null;
    }
    
    function delete_account($database) {
        $result = array();
        $result["result"]="success";
        $values = array("Status" => 2);
        $db_result = $database->update("User", $values, "ID='$this->id'"); 
        if(!$db_result)
            $result["result"]="fail";
        
        return $result;
    }    
    
    function change_password($database,$old_password, $password, $confirmed_password) {
        $result = array();
        $old_password_valid = $this->validate_old_password($database, $this->id, $old_password)?"valid":"old password isn't correct";
        $password_valid = $this->validate_password($password);
        $confirm_password_valid = $this->validate_confirm_password($password, $confirmed_password); 
        $result["old_password"]=$old_password_valid;
        $result["password"]=$password_valid;
        $result["confirmed_password"]=$confirm_password_valid;
        $result["result"]="fail";
        if($old_password_valid=="valid" && $password_valid=="valid" && $confirm_password_valid=="valid"){
            $values = array("Password" => sha1($password));
            $db_result = $database->update("User", $values, "ID='$this->id'");
            if (!$db_result)
                return $result;
            $result["result"] = "success";            
        }
        
        return $result;
    } 
    
    function reset_password($database, $email, $password, $confirmed_password,$code) {
        $result = array();
        $email_valid = $this->user_exists($database, $email) ? "valid" : "Email doesn't exist in our database";
        $password_valid = $this->validate_password($password);
        $confirm_password_valid = $this->validate_confirm_password($password, $confirmed_password); 
        $code_valid=$this->validate_code($database, $email, $code);
        $result["email"]=$email_valid;
        $result["password"]=$password_valid;
        $result["confirmed_password"]=$confirm_password_valid;
        $result["result"]="fail";
        if($email_valid=="valid" && $password_valid=="valid" && $confirm_password_valid=="valid" && $code_valid=="valid"){
            $values = array("Password" => sha1($password),"Code"=>null);
            $db_result = $database->update("User", $values, "Email='$email'");
            if (!$db_result)
                return $result;
            $result["result"] = "success";            
        }
        
        return $result;
    }    

    function populate_info($database, $user_id, $currency_id) {
        $final = array("result" => "fail");
        /** populate categories * */
        $SQL = "INSERT INTO Category (Name,Description,Color,UserID,Type)
              VALUES ('Transportation','Public Transit, Gasoline, Train Tickets, etc. ','#000000','$user_id','e'),
                     ('Groceries','Food, Kitchen items, snacks, nuts, drinks, water,etc.','#339967','$user_id','e'),
                     ('Personal Care','Hair, Gym, Makeup, Massage,etc.','#FE9800','$user_id','e'),
                     ('Utility','Electricity, water, gas, internet, phone calls, etc.','#33CBCB','$user_id','e'),
                     ('Housing','Rent, Mortgage, etc.','#FECB9A','$user_id','e'),
                     ('Leisure' ,'Eating out, Restaruants, Cafe, Party, etc.','#FF00FE','$user_id','e'),
                     ('Vacations' ,'Tickets, Hotel, Car rental,etc.','#FFCB00','$user_id','e'),
                     ('Education' ,'Tuition fees, books,conference fees, etc.','#00FF00','$user_id','e'),
                     ('Child care' ,'Day care, school fees, etc.','#CDFFCC','$user_id','e'),
                     ('Auto' ,'Car maintenance, oil changes, etc.','#C0C0C0','$user_id','e'),
                     ('Electronics' ,'Laptops, tablets, phones, etc.','999999','$user_id','e'),
                     ('Software' ,'Buyding software, software subscription, etc.','#FF6600','$user_id','e'),
                     ('Medical' ,'Health care insurance, co-pay, doctor visit, etc.','#808001','$user_id','e'),
                     ('Clothing' ,'Shoes, pants, shirts, dress, etc.','#01FFFF','$user_id','e'),
                     ('Insurance' ,'car insurance, home insurance, etc.','#008001','$user_id','e'),
                     ('Taxes' ,'Federal tax, state Tax, property Tax, etc.','#66669A','$user_id','e'),
                     ('Charity and Gifts' ,' ','#33CBCB','$user_id','e'),
                     ('Miscellaneous' ,' ','#FFFFFF','$user_id','e'),
                     ('Income' ,' ','#00FF00','$user_id','i');";

        $result = $database->send_SQL($SQL);

        /** populate categories items * */
        $SQL = "INSERT INTO Item (Name,CategoryID)
              VALUES ('Gasoline',(SELECT ID FROM Category WHERE Name='Transportation' AND UserID='$user_id')),    
                     ('Bus Ticket',(SELECT ID FROM Category WHERE Name='Transportation' AND UserID='$user_id')),
                     ('Train Ticket',(SELECT ID FROM Category WHERE Name='Transportation' AND UserID='$user_id')),
                     ('Car Rental',(SELECT ID FROM Category WHERE Name='Transportation' AND UserID='$user_id')),
                     ('Food',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Kitchen items',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Snacks',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Nuts',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Drinks',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Water',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Coffee',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Tea',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Toiletries',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Shaving cream',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Razors',(SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id')),
                     ('Hair',(SELECT ID FROM Category WHERE Name='Personal Care' AND UserID='$user_id')),
                     ('Gym',(SELECT ID FROM Category WHERE Name='Personal Care' AND UserID='$user_id')),
                     ('Makeup',(SELECT ID FROM Category WHERE Name='Personal Care' AND UserID='$user_id')),
                     ('Massage',(SELECT ID FROM Category WHERE Name='Personal Care' AND UserID='$user_id')),
                     ('Electricity',(SELECT ID FROM Category WHERE Name='Utility' AND UserID='$user_id')),
                     ('Water',(SELECT ID FROM Category WHERE Name='Utility' AND UserID='$user_id')),
                     ('Gas',(SELECT ID FROM Category WHERE Name='Utility' AND UserID='$user_id')),
                     ('Internet',(SELECT ID FROM Category WHERE Name='Utility' AND UserID='$user_id')),
                     ('Phone Calls',(SELECT ID FROM Category WHERE Name='Utility' AND UserID='$user_id')),
                     ('Rent',(SELECT ID FROM Category WHERE Name='Housing' AND UserID='$user_id')),
                     ('Mortgage',(SELECT ID FROM Category WHERE Name='Housing' AND UserID='$user_id')),
                     ('Eating out',(SELECT ID FROM Category WHERE Name='Leisure' AND UserID='$user_id')),
                     ('Restaruants',(SELECT ID FROM Category WHERE Name='Leisure' AND UserID='$user_id')),
                     ('Cafe',(SELECT ID FROM Category WHERE Name='Leisure' AND UserID='$user_id')),
                     ('Party',(SELECT ID FROM Category WHERE Name='Leisure' AND UserID='$user_id')),
                     ('Invitations',(SELECT ID FROM Category WHERE Name='Leisure' AND UserID='$user_id')),
                     ('Movies',(SELECT ID FROM Category WHERE Name='Leisure' AND UserID='$user_id')),
                     ('Sports',(SELECT ID FROM Category WHERE Name='Leisure' AND UserID='$user_id')),
                     ('Tickets',(SELECT ID FROM Category WHERE Name='Vacations' AND UserID='$user_id')),
                     ('Hotel',(SELECT ID FROM Category WHERE Name='Vacations' AND UserID='$user_id')),
                     ('Car rental',(SELECT ID FROM Category WHERE Name='Vacations' AND UserID='$user_id')),
                     ('Eating out',(SELECT ID FROM Category WHERE Name='Vacations' AND UserID='$user_id')),
                     ('Party',(SELECT ID FROM Category WHERE Name='Vacations' AND UserID='$user_id')),
                     ('Tuition fees',(SELECT ID FROM Category WHERE Name='Education' AND UserID='$user_id')),
                     ('Books',(SELECT ID FROM Category WHERE Name='Education' AND UserID='$user_id')),
                     ('Conference fees',(SELECT ID FROM Category WHERE Name='Education' AND UserID='$user_id')),
                     ('Student loan',(SELECT ID FROM Category WHERE Name='Education' AND UserID='$user_id')),
                     ('Day care',(SELECT ID FROM Category WHERE Name='Child care' AND UserID='$user_id')),
                     ('School fees',(SELECT ID FROM Category WHERE Name='Child care' AND UserID='$user_id')),
                     ('Toys',(SELECT ID FROM Category WHERE Name='Child care' AND UserID='$user_id')),
                     ('Allowance',(SELECT ID FROM Category WHERE Name='Child care' AND UserID='$user_id')),
                     ('Activities',(SELECT ID FROM Category WHERE Name='Child care' AND UserID='$user_id')),
                     ('Car maintenance',(SELECT ID FROM Category WHERE Name='Auto' AND UserID='$user_id')),
                     ('Oil changes',(SELECT ID FROM Category WHERE Name='Auto' AND UserID='$user_id')),
                     ('Car payment',(SELECT ID FROM Category WHERE Name='Auto' AND UserID='$user_id')),
                     ('Laptops',(SELECT ID FROM Category WHERE Name='Electronics' AND UserID='$user_id')),
                     ('Tablets',(SELECT ID FROM Category WHERE Name='Electronics' AND UserID='$user_id')),
                     ('Phones',(SELECT ID FROM Category WHERE Name='Electronics' AND UserID='$user_id')),
                     ('TVs',(SELECT ID FROM Category WHERE Name='Electronics' AND UserID='$user_id')),
                     ('Maintenance',(SELECT ID FROM Category WHERE Name='Electronics' AND UserID='$user_id')),
                     ('Buying software',(SELECT ID FROM Category WHERE Name='Software' AND UserID='$user_id')),
                     ('Software subscription',(SELECT ID FROM Category WHERE Name='Software' AND UserID='$user_id')),
                     ('Health care insurance',(SELECT ID FROM Category WHERE Name='Medical' AND UserID='$user_id')),
                     ('Co-pay',(SELECT ID FROM Category WHERE Name='Medical' AND UserID='$user_id')),
                     ('Doctor visit',(SELECT ID FROM Category WHERE Name='Medical' AND UserID='$user_id')),
                     ('Surgery',(SELECT ID FROM Category WHERE Name='Medical' AND UserID='$user_id')),
                     ('Therapy',(SELECT ID FROM Category WHERE Name='Medical' AND UserID='$user_id')),
                     ('Dental',(SELECT ID FROM Category WHERE Name='Medical' AND UserID='$user_id')),
                     ('Car insurance',(SELECT ID FROM Category WHERE Name='Insurance' AND UserID='$user_id')),
                     ('Home insurance',(SELECT ID FROM Category WHERE Name='Insurance' AND UserID='$user_id')),
                     ('Federal Tax',(SELECT ID FROM Category WHERE Name='Taxes' AND UserID='$user_id')),
                     ('State Tax',(SELECT ID FROM Category WHERE Name='Taxes' AND UserID='$user_id')),
                     ('Property Tax',(SELECT ID FROM Category WHERE Name='Taxes' AND UserID='$user_id')),
                     ('Gifts',(SELECT ID FROM Category WHERE Name='Charity and Gifts' AND UserID='$user_id')),
                     ('Charity',(SELECT ID FROM Category WHERE Name='Charity and Gifts' AND UserID='$user_id')),
                     ('Miscellaneous',(SELECT ID FROM Category WHERE Name='Miscellaneous' AND UserID='$user_id')),
                     ('Paycheck',(SELECT ID FROM Category WHERE Name='Income' AND UserID='$user_id')),
                     ('Bonus',(SELECT ID FROM Category WHERE Name='Income' AND UserID='$user_id')),
                     ('Investment',(SELECT ID FROM Category WHERE Name='Income' AND UserID='$user_id')),
                     ('Tax refund',(SELECT ID FROM Category WHERE Name='Income' AND UserID='$user_id')),
                     ('Cash rewards',(SELECT ID FROM Category WHERE Name='Income' AND UserID='$user_id'));";

        $result = $database->send_SQL($SQL);

        /** populate bank accounts * */
        $SQL = "INSERT INTO Account (Name, Description, UserID, CurrencyID, MoneyType, Type, InitialBalance)
              VALUES ('Checking','','$user_id','$currency_id','1','1',0.00),
                     ('Savings','','$user_id','$currency_id','1','1',0.00),
                     ('Cash','','$user_id','$currency_id','1','1',0.00);";

        $result = $database->send_SQL($SQL);

        /** populate payees for US * */
        $SQL = "INSERT INTO Payee (Name, Country, UserID)
              VALUES ('Walmart','US','$user_id'),
                     ('Target','US','$user_id'),
                     ('ALDI','US','$user_id'),
                     ('Panera Bread','US','$user_id'),
                     ('Amazon','US','$user_id'),
                     ('Payee','US','$user_id'),
                     ('ATT','US','$user_id');";

        $result = $database->send_SQL($SQL);

        /** populate one sample transaction * */
        $SQL = "INSERT INTO Transaction (UserID, Time, AccountID, PayeeID, Payee)
              VALUES ('$user_id',CURRENT_DATE(),(SELECT ID FROM Account WHERE Name='Checking' AND UserID='$user_id'), (SELECT ID FROM Payee WHERE Name='Payee' AND UserID='$user_id'),'Payee');";

        $result = $database->send_SQL($SQL);
        
        $SQL = " INSERT INTO TransactionItem (CategoryID, ItemID, Amount, TransactionID, Quantity)
              VALUES ((SELECT ID FROM Category WHERE Name='Miscellaneous' AND UserID='$user_id'), (SELECT ID FROM Item WHERE Name='Miscellaneous' AND CategoryID=(SELECT ID FROM Category WHERE Name='Miscellaneous' AND UserID='$user_id')),0.01, (SELECT ID FROM Transaction WHERE UserID='$user_id'),1);";

        $result = $database->send_SQL($SQL); 
        
        /** populate budget and targets **/
        
        $SQL="INSERT INTO Budget (Name, Description, UserID, Start, End)
              VALUES ('My Budget','','$user_id',CONCAT(YEAR(CURRENT_DATE()),'-',MONTH(CURRENT_DATE()),'-1'),DATE_ADD(CONCAT(YEAR(CURRENT_DATE()),'-',MONTH(CURRENT_DATE()),'-1'),INTERVAL 365 DAY));";
        
        $result = $database->send_SQL($SQL); 
        
        $SQL="INSERT INTO Target (CategoryID, BudgetID, Amount, Every, Period, CurrencyID, Rollover)
              VALUES ((SELECT ID FROM Category WHERE Name='Groceries' AND UserID='$user_id'),(SELECT ID FROM Budget WHERE Name='My Budget' AND UserID='$user_id'),600,1,'M','$currency_id',1),
                     ((SELECT ID FROM Category WHERE Name='Transportation' AND UserID='$user_id'),(SELECT ID FROM Budget WHERE Name='My Budget' AND UserID='$user_id'),120,1,'M','$currency_id',0),
                     ((SELECT ID FROM Category WHERE Name='Utility' AND UserID='$user_id'),(SELECT ID FROM Budget WHERE Name='My Budget' AND UserID='$user_id'),1000,1,'Y','$currency_id',0),
                     ((SELECT ID FROM Category WHERE Name='Vacations' AND UserID='$user_id'),(SELECT ID FROM Budget WHERE Name='My Budget' AND UserID='$user_id'),2000,1,'Y','$currency_id',0),
                     ((SELECT ID FROM Category WHERE Name='Education' AND UserID='$user_id'),(SELECT ID FROM Budget WHERE Name='My Budget' AND UserID='$user_id'),400,6,'M','$currency_id',0),
                     ((SELECT ID FROM Category WHERE Name='Housing' AND UserID='$user_id'),(SELECT ID FROM Budget WHERE Name='My Budget' AND UserID='$user_id'),1000,1,'M','$currency_id',0),
                     ((SELECT ID FROM Category WHERE Name='Clothing' AND UserID='$user_id'),(SELECT ID FROM Budget WHERE Name='My Budget' AND UserID='$user_id'),300,6,'M','$currency_id',1);";
        
        $result = $database->send_SQL($SQL); 
        
        $values = array("InfoAdded" => "1");
        $result = $database->update("User", $values, "ID='$user_id'");
        if (!$result)
            return $final;
        $final["result"] = "success";
        
        $this->set_info_added("1");
        return $final;
    }

    function validate_email($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return "Invalid email format";
        return "valid";
    }

    function validate_password($password) {
        if (strlen($password) < 6 || strlen($password) > 10)
            return "Password must be 6-10 characters";
        else if (!preg_match("/\d/", $password))
            return "Password must contain digits";
        else if (!preg_match("/[a-z]+/", $password))
            return "Password must contain lowercase letters";
        else if (!preg_match("/[A-Z]+/", $password))
            return "Password must contain uppercase letters";
        else
            return "valid";
    }

    function validate_confirm_password($password, $confirm_password) {
        if ($password == $confirm_password)
            return "valid";
        return "The two passwords don't match";
    }
    
    function validate_token($database, $token) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $token_count = $database->count("Code", "CodeNumber='$token' AND IPAddress='$ip'");
        return $token_count>0;
    }    

}
?>

