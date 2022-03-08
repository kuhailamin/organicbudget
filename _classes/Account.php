<?php

class Account {

    private $ID;
    private $name;
    private $description;
    private $userID;
    private $currencyID;
    private $moneyType; //1=DEBIT, 2=CREDIT
    private $type; //1=BANK ACCOUNT, 2=CASH
    public static $MONEY_TYPE_ARRAY = array(1 => "Debit", 2 => "Credit");
    public static $ACCOUNT_TYPE_ARRAY = array(1 => "Bank Account", 2 => "Cash");
    private $initialBalance;

    function __construct($ID = "", $name = "", $description = "", $userID = "", $currencyID = "", $moneyType = "", $type = "", $initialBalance = 0) {
        $this->ID = $ID;
        $this->name = $name;
        $this->description = $description;
        $this->userID = $userID;
        $this->currencyID = $currencyID;
        $this->moneyType = $moneyType;
        $this->type = $type;
        $this->initialBalance = $initialBalance;
    }

    function get_name() {
        return $this->name;
    }

    function get_description() {
        return $this->description;
    }

    function get_ID() {
        return $this->ID;
    }

    function get_currency_ID() {
        return $this->currencyID;
    }

    function get_userID() {
        return $this->userID;
    }

    function get_moneyType() {
        return $this->moneyType;
    }
    
    static function get_total_balance($database,$initial_balance,$currency_id,$number_format,$account_id,$user_id,$money_type){
        $current_balance = 0;
        $SQL = " SELECT SUM(TransactionItem.Amount * TransactionItem.Quantity) AS 'Amount' From Transaction INNER JOIN TransactionItem ON Transaction.ID=TransactionItem.TransactionID WHERE Transaction.AccountID='$account_id' AND Transaction.UserID='$user_id'";
        $result = $database->send_SQL($SQL);
        $current_balance=floatval($initial_balance);
        if(!$result){
            //do nothing
        }
        else{
           $row = mysqli_fetch_assoc($result);
           $current_balance=$current_balance-floatval($row["Amount"]);   
        }
        //subtract money transfered from this account
        $result = $database->select_fields_where("Transfer","SUM(Transfer.Amount) AS 'TransferAmount'","FromAccount='$account_id' AND UserID='$user_id'");
        if(!$result){
            //do nothing
        }
        else{
           $row = mysqli_fetch_assoc($result);
           $current_balance=$current_balance-floatval($row["TransferAmount"]);   
        }
        //add money transfered to this account
        $result = $database->select_fields_where("Transfer","SUM(Transfer.Amount) AS 'TransferAmount'","ToAccount='$account_id' AND UserID='$user_id'");
        if(!$result){
            //do nothing
        }
        else{
           $row = mysqli_fetch_assoc($result);
           $current_balance=$current_balance+floatval($row["TransferAmount"]);   
        }
        //add income money to this account
        $result = $database->select_fields_where("Income","SUM(Income.Amount) AS 'IncomeAmount'","AccountID='$account_id' AND UserID='$user_id'");
        if(!$result){
            //do nothing
        }
        else{
           $row = mysqli_fetch_assoc($result);
           $current_balance=$current_balance+floatval($row["IncomeAmount"]);   
        }        
        if($money_type==2) //if CREDIT, positive amount is seen as negative, negative amount is seen as positive
          $current_balance=$current_balance*-1;
        return $current_balance;
    }
    
    static function delete_all_transactions($database,$account_id,$user_ID){
        $SQL = "DELETE FROM TransactionItem
               WHERE TransactionID IN (
                                        SELECT ID FROM Transaction
                                        WHERE AccountID='$account_id'
                                       );";
        
        $result = $database->send_SQL($SQL);
        if(!$result)
            return false;
        
        $result = $database->delete_where("Transaction","AccountID='$account_id' AND UserID='$user_ID'");
        if(!$result)
            return false;        
        return true;
    }


    function to_array($database,$number_Format) {
        $type = Account::$ACCOUNT_TYPE_ARRAY[$this->type];
        $money_type = Account::$MONEY_TYPE_ARRAY[$this->moneyType];
        /*$current_balance = 0;
        $final_balance = "";
        $SQL = " SELECT SUM(TransactionItem.Amount) AS 'Amount' From Transaction INNER JOIN TransactionItem WHERE Transaction.AccountID='$this->ID' AND Transaction.UserID='$this->userID'";
        $result = $database->send_SQL($SQL);
        $current_balance=floatval($this->initialBalance);
        if(!$result){
            //do nothing
        }
        else{
           $row = mysqli_fetch_assoc($result);
           $current_balance+=floatval($row["Amount"]);   
        }
        
        $current_balance=Utility::formatNumber($current_balance, $number_Format);
        $currency_result = $database->select_fields_where("Currency", "Symbol, AfterNumber", "ID='$this->currencyID'");
        if (!$currency_result) {
            //do nothing
        } else {
            $row = mysqli_fetch_assoc($currency_result);
            $final_balance=  Utility::formatMoney($current_balance, $row["Symbol"], $row["AfterNumber"]);
        }*/
        $final_balance=Account::get_total_balance($database, $this->initialBalance, $this->currencyID, $number_Format, $this->ID, $this->userID, $this->moneyType);
        $final_balance_formatted=Utility::get_formatted_money($this->currencyID, $final_balance, $number_Format); 
        $initial_balance_formatted=Utility::get_formatted_money($this->currencyID, $this->initialBalance, $number_Format);
        $name=  ucfirst($this->name);

        return array("id" => $this->ID, "account_type" => $type, "money_type" => $money_type, "account_description" => $this->description, "userID" => $this->userID,"account_balance"=>$final_balance_formatted,"account_balance_as_number"=>$final_balance,
                     "account_name"=>$name,"account_initial_balance"=>$this->initialBalance,"currency_id"=>$this->currencyID,"money_type_as_number"=>$this->moneyType,
                     "type_as_number"=>$this->type,"initial_balance_formatted"=>$initial_balance_formatted);
    }

}
?> 
