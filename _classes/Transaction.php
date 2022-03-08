<?php

class Transaction {

    private $id;
    private $payee;
    private $date;
    private $account_id;

    function __construct($id = "", $payee = "", $date = "",$account_id="") {
        $this->id = $id;
        $this->payee = $payee;
        $this->date = $date;
        $this->account_id=$account_id;
    }


    function edit_transaction_item($database, $id, $userID, $amount) {
        $SQL = " SELECT * FROM TransactionItem INNER JOIN Transaction ON Transaction.ID=TransactionItem.TransactionID";
        $SQL.=" WHERE Transaction.UserID='$userID' AND TransactionItem.ID='$id'";
        $result = $database->send_SQL($SQL);
        $valid = $result != false && mysqli_num_rows($result) == 1;
        if ($valid) {
            $values = array("Amount" => $amount);
            return $database->update("TransactionItem", $values, "ID='$id'");
        }
        return false;
    }

    function add_transaction_item($database, $trans_id, $userID, $text, $amount) {
        $t_count = $database->count("Transaction", "ID='$trans_id' AND UserID='$userID'");
        $SQL = "SELECT Item.ID, Item.Name FROM Item INNER JOIN Category ON Category.ID=Item.CategoryID ";
        $SQL.="WHERE Category.UserID='$userID' AND Item.Name LIKE '%$text%' ORDER BY Item.Name LIMIT 1";
        $result = $database->send_SQL($SQL);
        if (!($result == false)) {
            $row_count = mysqli_num_rows($result);
            if ($row_count > 0) {
                $row = mysqli_fetch_array($result);
                $item_id = $row["ID"];
                $values = array("ItemID" => $item_id, "Amount" => $amount, "TransactionID" => $trans_id);
                $result = $database->insert("TransactionItem", $values);
                return $result;
            }
        }
        $SQL = " SELECT Category.ID FROM Category WHERE Category.Name LIKE '%$text%' AND Category.UserID='$userID'";
        $result = $database->send_SQL($SQL);
        if (!($result == false)) {
            $row_count = mysqli_num_rows($result);
            if ($row_count > 0) {
                $row = mysqli_fetch_array($result);
                $cat_id = $row["ID"];
                $values = array("CategoryID" => $cat_id, "Amount" => $amount, "TransactionID" => $trans_id);
                $result = $database->insert("TransactionItem", $values);
                return $result;
            }
        }
        return false;
    }
    

    function get_transaction_items($database,$currency_id,$number_format,$user_id) {
        $SQL = "SELECT * FROM (SELECT TransactionItem.ID, TransactionItem.CategoryID, TransactionItem.ItemID, TransactionItem.TransactionID, TransactionItem.Quantity, Item.Name, Category.Name AS 'CatName', Category.Color, Amount FROM";
        $SQL.=" TransactionItem";
        $SQL.=" LEFT JOIN Category";
        $SQL.=" ON TransactionItem.CategoryID = Category.ID";
        $SQL.=" LEFT JOIN Item";
        $SQL.=" ON Item.ID = TransactionItem.ItemID";
        $SQL.=" WHERE TransactionItem.TransactionID='$this->id') AS Transactions Order By CatName, Name";
        $result = $database->send_SQL($SQL);
        $array = array();
        if (!$result)
            return "";
        $row_count = mysqli_num_rows($result);
        $sum = 0;

        for ($j = 0; $j < $row_count; ++$j) {
            $row = mysqli_fetch_array($result);
            $id = $row["ID"];
            $item_name = is_null($row["Name"])?"Unknown":htmlspecialchars($row["Name"], ENT_QUOTES);
            $cat_name = is_null($row["CatName"])?"Unknown":htmlspecialchars($row["CatName"],ENT_QUOTES);
            $color = is_null($row["Color"])?"#FFFFFF":$row["Color"];
            $amount = floatval($row["Amount"]);
            $total_amount=$amount*floatval($row["Quantity"]);
            $formatted_amount=Utility::get_formatted_money($currency_id, $total_amount, $number_format);
            if($amount>0)
                $formatted_amount="-$formatted_amount";
            $array[] = array("id"=>$id,"item_id"=>$row["ItemID"],"category_name"=>ucfirst($cat_name),"category_color"=>$color,
                "amount"=>$amount,"item_name"=>ucfirst($item_name),"category_id"=>$row["CategoryID"],
                "formatted_amount"=>$formatted_amount,"transaction_id"=>$row["TransactionID"], "quantity"=>$row["Quantity"]); 
        }
        
        return $array;
    }
    
    function get_item_name($database,$item_id,$category_id){
        $result = $database->select_fields_where("Item", "Name", "ID='$item_id' AND CategoryID='$category_id'");
        if (!$result || mysqli_num_rows($result)==0)
                    return "Unknown";
        $row = mysqli_fetch_array($result);
        return $row["Name"]; 
    }
    
    function get_category_info($database,$category_id,$user_id){
        $info=array("Color"=>"#FFFFFF","Name"=>"Unknown");
        $result = $database->select_fields_where("Category", "Name, Color", "ID='$category_id' AND UserID='$user_id'");
        if (!$result || mysqli_num_rows($result)==0)
                    return $info;
        $row = mysqli_fetch_array($result);
        $info["Color"]=$row["Color"];
        $info["Name"]=$row["Name"];
        return $info;
    }    

    function to_array($database,$number_format,$time_format,$date_format,$user_id,$time_zone_offset) {
        $currency_id="";
        $account_name="";
        $result = $database->select_fields_where("Account", "CurrencyID, Name", "ID='$this->account_id' AND UserID='$user_id'");
        if(!$result){
            //do nothing
        }
        else{
            $row=mysqli_fetch_array($result);
            $currency_id=$row["CurrencyID"];
            $account_name=$row["Name"];
        }

        $raw_amount=Transaction::get_total_balance($database, $this->id, $currency_id, $number_format);
        $formatted_amount=Utility::get_formatted_money($currency_id, $raw_amount, $number_format);
        if($raw_amount>0)
            $formatted_amount="-$formatted_amount";
        //$raw_amount=$amount_array["amount"];
        $formatted_date=Utility::get_formatted_date_time($date_format,$time_format, $this->date,$time_zone_offset);
        $local_date_object=Utility::get_local_date_object($this->date, $time_zone_offset);
        $array = array("type"=>"transaction","account_name"=>$account_name,"payee"=>$this->payee,
            "formatted_date"=>$formatted_date,"id"=>$this->id,"formatted_amount"=>$formatted_amount,
            "account_id"=>$this->account_id,"date"=>$this->date,"amount"=>$raw_amount,
            "year"=>date_format($local_date_object,"Y"),"month"=>date_format($local_date_object,"m"), "day"=>date_format($local_date_object,"j"),
            "hour"=>date_format($local_date_object,"G"),"minute"=>date_format($local_date_object,"i"),
            "items"=>$this->get_transaction_items($database, $currency_id, $number_format,$user_id)
            );
        return $array;        
    }
    
    static function get_total_balance($database,$transaction_id,$currency_id,$number_format){
        $SQL = " SELECT SUM(TransactionItem.Amount * TransactionItem.Quantity) AS 'Amount' FROM TransactionItem";
        $SQL.=" WHERE TransactionItem.TransactionID='$transaction_id'";
        $result = $database->send_SQL($SQL);
        $total_value=0;
        if(!$result){
            //do nothing
        }
        else if(mysqli_num_rows($result)>0){
           $row = mysqli_fetch_assoc($result);
           $total_value+=floatval($row["Amount"]);   
        }
        
        return $total_value;
    } 
    
    public static function get_currency_id($database,$transaction_id,$user_id){
        $SQL = "SELECT Account.CurrencyID FROM Account ";
        $SQL.=" INNER JOIN Transaction ON ";
        $SQL.=" Transaction.AccountID=Account.ID ";
        $SQL.=" WHERE Transaction.ID='$transaction_id' AND Transaction.UserID='$user_id'";
        $result = $database->send_SQL($SQL); 
        if(!$result)
            return "";
        
        if(mysqli_num_rows($result)>0){
           $row = mysqli_fetch_assoc($result);
           return $row["CurrencyID"];   
        }
        
        return "";
    }

}
