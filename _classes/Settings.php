<?php

class Settings {

    function __construct() {
        
    }

    function get_settings() {

        $array["username"] = $_SESSION["user"]->get_email();
        $array["dateformat"] = $_SESSION["user"]->get_DateFormat();
        $array["timeformat"] = $_SESSION["user"]->get_TimeFormat();
        $array["numberformat"] = $_SESSION["user"]->get_NumberFormat();
        $array["currency"] = $_SESSION["user"]->get_currency_id();
        $array["orientation"] = $_SESSION["user"]->get_orientation();
        $array["result"] = "success";
        return $array;
    }
    
    function stats($database,$page){
        $ip = $_SERVER['REMOTE_ADDR'];
        $country=Utility::get_country_code_of_user();
        $values = array("IP_ADDRESS" => $ip, "Country" => $country,"Page" => $page);
        $db_result = $database->insert("Stats", $values);
        if(!$db_result)
            return false;
        return true;
    }    

    function generate_signup_code($database){
        $ip = $_SERVER['REMOTE_ADDR'];
        $random_code = sha1( rand(10000000, 1000000000));
        $values = array("IPAddress" => $ip, "CodeNumber" => $random_code);
        $database->delete_where("Code", "IPAddress='$ip'");
        $db_result = $database->insert("Code", $values);
        if(!$db_result)
            return null;
        return $random_code;
    }

    function recover_password($database, $email) {
        $result = array();
        $result["email"] = "valid";
        $result["result"] = "fail";
        $user_count = $database->count("User", "Email='$email'");
        if ($user_count == 0) {
            $result["email"] = "Email doesn't exist in our database";
            return $result;
        } else if ($user_count == 1) {
            $random_code = sha1( rand(10000000, 1000000000));
            $values = array("Code" => $random_code);
            $final = $database->update("User", $values, "Email='$email'");
            if (!$final)
                return $result;
            $result["email"] = "valid";
            $message = "<html><body><div class='text-align:center;'><img style='margin-right:auto;margin-left:auto;width:140px;' src='https://organicbudget.us/images/about_us.png'></div>";
            $message.="<h1>Reset Password</h1>";
            $message.="<div>Click on the following button to reset your password</div><br>";
            $message.="<a style='background-color:rgb(0,176,238);color:white;padding:8px;border-radius:5px;text-align:center; text-decoration:none;font-weight:bold;' href='https://organicbudget.us/reset_password/?code=$random_code'>Reset Password</a>";
            $message.="<br><br></body></html>";
            $mail_result = $this->send_email($email, null, "Organic Budget <helpdesk@organicbudget.us>", $message, "Organic Budget Login Assistance");
             if (!$mail_result)
                $result["result"] = "fail";
             else
               $result["result"] = "success";
        }
        return $result;
    }
   

    function contact_us($from, $subject, $comments) {
        $result = array();
        $user_country = Utility::get_country_code_of_user();
        $result["result"] = "fail";
        $from_valid = $this->validate_email($from);
        $comments_valid = $this->validate_comments($comments);
        $result["email"] = $from_valid;
        $result["comments"] = $comments_valid;

        if ($comments_valid != "valid" || $from_valid != "valid")
            return $result;

        $comments = "User Country: $user_country \n $comments";
        $cc = 'mohammadkuhail@hotmail.com';
        $to = "kuhailamin@gmail.com";

        $mail_result = $this->send_email($to, $cc, $from, $comments, $subject);

        //$mail_result = mail($to, $subject, $comments, "From: $from" . "\r\n" . "CC: $cc");
        if (!$mail_result)
            $result["result"] = "fail";
        else
            $result["result"] = "success";
        return $result;
    }

    function send_email($to, $cc, $from, $message, $subject) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = "To: $to";
        $headers[] = "From: $from";
        if ($cc != null) {
            $headers[] = "Cc: $cc";
        }
        $mail_result = mail($to, $subject, $message, implode("\r\n", $headers));
        return $mail_result;
    }

    function send_email_elastic($to, $cc, $from, $message, $subject) {
        $url = 'https://api.elasticemail.com/v2/email/send';
        $to_string=$cc==null?$to:"$to;$cc";

        try {
            $post = array('from' => 'youremail@yourdomain.com',
                'fromName' => $from,
                'apikey' => '0723ade5-615b-4be3-8eb0-ac3f6e893ce8',
                'subject' => $subject,
                'to' => $to_string,
                'bodyHtml' => $message,
                'bodyText' => $message,
                'isTransactional' => false);

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $post,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_SSL_VERIFYPEER => false
            ));

            $result = curl_exec($ch);
            curl_close($ch);
            
            return $result;

            return $result["success"];
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return false;
            //echo $ex->getMessage();
        }
        return false;
    }

    function edit_settings($database, $date_format, $number_format, $time_format, $currency_id, $user_id) {
        $result = array();
        $date_format = $this->fix_date_format($date_format);
        $number_format = $this->fix_number_format($number_format);
        $time_format = $this->fix_time_format($time_format);
        $currency_id = $this->fix_currency_ID($database, $currency_id);

        $result["result"] = "fail";
        $values = array("NumberFormat" => $number_format, "DateFormat" => $date_format, "CurrencyID" => $currency_id, "TimeFormat" => $time_format);
        $final = $database->update("User", $values, "ID='$user_id'");
        if (!$final)
            return $result;
        $result["result"] = "success";

        $_SESSION["user"]->set_DateFormat($date_format);
        $_SESSION["user"]->set_TimeFormat($time_format);
        $_SESSION["user"]->set_NumberFormat($number_format);
        $_SESSION["user"]->set_currency_id($currency_id);

        return $result;
    }

    function fix_currency_ID($database, $currency_ID) {
        $account_count = $database->count("Currency", "ID='$currency_ID'");
        if ($account_count == 0) { //currency ID is not valid, just get the top most ID
            $result = $database->select_fields_TOP("Currency", "ID");
            $row = mysqli_fetch_assoc($result);
            $currency_ID = $row["ID"];
        }
        return $currency_ID;
    }

    function fix_number_format($number_format) {
        if (!is_numeric($number_format) || intval($number_format) < Utility::$MIN_NUMBER_FORMAT || intval($number_format) > Utility::$MAX_NUMBER_FORMAT)
            return Utility::$default_number_format;
        else
            return intval($number_format);
    }

    function validate_email($email) {
        if (strlen(trim($email)) == 0)
            return "email is required";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return "Invalid email format";
        else
            return "valid";
    }

    function validate_comments($comments) {
        if (strlen(trim($comments)) == 0)
            return "comments are required";
        if (strlen(trim($comments)) < 30)
            return "min character length is 30 characters";
        return "valid";
    }

    function fix_date_format($date_format) {
        if (!is_numeric($date_format) || intval($date_format) < Utility::$MIN_DATE_FORMAT || intval($date_format) > Utility::$MAX_DATE_FORMAT)
            return Utility::$default_date_format;
        else
            return intval($date_format);
    }

    function fix_time_format($time_format) {
        if (!is_numeric($time_format) || intval($time_format) < Utility::$MIN_TIME_FORMAT || intval($time_format) > Utility::$MAX_TIME_FORMAT)
            return Utility::$default_time_format;
        else
            return intval($time_format);
    }

}
?> 
