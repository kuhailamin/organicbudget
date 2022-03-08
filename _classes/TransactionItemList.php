<?php

include "Transaction.php";

class TransactionItemList {

    public static $MIN_AMOUNT = 0.01;
    public static $MAX_AMOUNT = 99999;
    public static $MAX_NUMBER_TRANSACTION_ITEMS_PER_TRANSACTION = 100;

    function __construct() {
        
    }

    function add_transaction_items($database, $transaction_id, $user_id, $item_names, $category_names, $item_amounts, $quantities, $indices, $category_type, $lang) {
        $results = array();
        for ($i = 0; $i < count($item_names); $i++) {
            // if ($i < count($item_names) && $i < count($category_names)) {
                $results[] = $this->add_transaction_item($database, $transaction_id, $user_id, $item_names[$i], $category_names[$i], $item_amounts[$i], $quantities[$i], $category_type, $lang, $indices[$i]);
            //  }
        }
        return $results;
    }

    function edit_transaction_items($database, $transaction_id, $user_id, $transaction_item_ids, $item_names, $category_names, $item_amounts, $quantities, $indices, $category_type, $lang) {
        $results = array();
        for ($i = 0; $i < count($item_names); $i++) {
            if (trim($transaction_item_ids[$i]) == "null" || trim($transaction_item_ids[$i]) == ""){
                $results[] = $this->add_transaction_item($database, $transaction_id, $user_id, $item_names[$i], $category_names[$i], $item_amounts[$i], $quantities[$i], $category_type, $lang, $indices[$i]);
            }
            else{
                $results[] = $this->edit_transaction_item($database, $transaction_item_ids[$i], $transaction_id, $user_id, $item_names[$i], $category_names[$i], $item_amounts[$i], $quantities[$i], $category_type, $lang, $indices[$i]);
            }
        }
        return $results;
    }

    function add_transaction_item($database, $transaction_id, $user_id, $item_name, $category_name, $amount, $quantity, $category_type, $lang, $index) {
        $result = array();
        $category_type = TransactionItemList::fix_category_type($category_type);
        $lang = Utility::fix_language($lang);
        $quantity = $this->fix_quantity($quantity);
        $amount_valid = TransactionItemList::validate_amount($amount);
        $max_num_items_per_transaction_valid = $this->validate_max_num_transaction_items($database, $transaction_id);



        $transaction_valid = $this->validate_transaction_id($database, $transaction_id, $user_id);
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
        
        

        $result["transaction-id"] = $transaction_valid;
        $result["transaction_item_$index"] = $item_valid;
        $result["transaction-item-amount-$index"] = $amount_valid;
        $result["transaction-item-quantity-$index"] = "valid";
        $result["item_id"] = $item_id;
        $result["category_id"] = $category_id;
        $result["error_message"] = $max_num_items_per_transaction_valid == "valid" ? "" : $max_num_items_per_transaction_valid;
        $result["result"] = "fail";
        if ($item_valid == "valid" && $amount_valid == "valid" && $transaction_valid == "valid" && $max_num_items_per_transaction_valid == "valid") {
            $values = array("CategoryID" => $category_id, "ItemID" => $item_id, "Amount" => abs($amount),
                "TransactionID" => $transaction_id, "Quantity" => $quantity);
            $final = $database->insert("TransactionItem", $values);
            if (!$final)
                return $result;
            $result["result"] = "success";
            $result["id"] = $database->last_insert_id();
        }
        return $result;
    }
    
    function edit_transaction_item($database, $transaction_item_id, $transaction_id, $user_id, $item_name, $category_name, $amount, $quantity, $category_type, $lang, $index) {
        $result = array();
        $category_type = TransactionItemList::fix_category_type($category_type);
        $lang = Utility::fix_language($lang);
        $quantity = $this->fix_quantity($quantity);
        $amount_valid = TransactionItemList::validate_amount($amount);

        $transaction_valid = $this->validate_transaction_id($database, $transaction_id, $user_id);
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

        $result["transaction-id"] = $transaction_valid;
        $result["transaction_item_$index"] = $item_valid;
        $result["transaction-item-amount-$index"] = $amount_valid;
        $result["transaction-item-quantity-$index"] = "valid";
        $result["item_id"] = $item_id;
        $result["category_id"] = $category_id;
        $result["result"] = "fail";
        if ($item_valid == "valid" && $amount_valid == "valid" && $transaction_valid == "valid") {
            $values = array("CategoryID" => $category_id, "ItemID" => $item_id, "Amount" => abs($amount), "Quantity" => $quantity);
            $final = $database->update("TransactionItem", $values, "ID='$transaction_item_id' AND TransactionID='$transaction_id'");
            if (!$final)
                return $result;
            $result["result"] = "success";
        }
        return $result;
    }

    function get_transaction_total_amount($database, $transaction_id, $user_id, $number_format) {
        $SQL = "SELECT * FROM (SELECT TransactionItem.ID, TransactionItem.CategoryID, TransactionItem.ItemID, Item.Name, Category.Name AS 'CatName', Category.Color, Amount FROM";
        $SQL.=" TransactionItem";
        $SQL.=" INNER JOIN Item";
        $SQL.=" ON Item.ID = TransactionItem.ItemID";
        $SQL.=" INNER JOIN Category";
        $SQL.=" ON Item.CategoryID = Category.ID";
        $SQL.=" WHERE TransactionItem.TransactionID='$transaction_id' AND TransactionItem.ID='$id' AND Category.UserID='$user_id') AS Transactions Order By CatName, Name";
        $result = $database->send_SQL($SQL);
        $array = array();
        if (!$result)
            return $array;
        $row_count = mysqli_num_rows($result);
        if ($row_count == 1) {
            $currency_id = Transaction::get_currency_id($database, $transaction_id, $user_id);
            $total_amount = Transaction::get_total_balance($database, $transaction_id, $currency_id, $number_format);
            $formatted_total_amount = Utility::get_formatted_money($currency_id, $total_amount, $number_format);
            if ($total_amount > 0)
                $formatted_total_amount = "-$formatted_total_amount";
            $row = mysqli_fetch_array($result);
            $id = $row["ID"];
            $item_name = ucfirst(htmlspecialchars($row["Name"], ENT_QUOTES));
            $cat_name = ucfirst(htmlspecialchars($row["CatName"], ENT_QUOTES));
            $color = $row["Color"];
            $amount = floatval($row["Amount"]);
            $formatted_amount = Utility::get_formatted_money($currency_id, $amount, $number_format);
            if ($amount > 0)
                $formatted_amount = "-$formatted_amount";
            $array[] = array("id" => $id, "category_name" => $cat_name, "category_color" => $color,
                "amount" => $amount, "item_name" => $item_name, "category_id" => $row["CategoryID"],
                "formatted_amount" => $formatted_amount,
                "total_amount" => $total_amount, "formatted_total_amount" => $formatted_total_amount
            );
        }

        return $array;
    }

    function get_transaction_item($database, $transaction_id, $id, $user_id, $number_format) {
        $SQL = "SELECT * FROM (SELECT TransactionItem.ID, TransactionItem.CategoryID, TransactionItem.ItemID, Item.Name, TransactionItem.Quantity, Category.Name AS 'CatName', Category.Color, Amount FROM";
        $SQL.=" TransactionItem";
        $SQL.=" INNER JOIN Item";
        $SQL.=" ON Item.ID = TransactionItem.ItemID";
        $SQL.=" INNER JOIN Category";
        $SQL.=" ON Item.CategoryID = Category.ID";
        $SQL.=" WHERE TransactionItem.TransactionID='$transaction_id' AND TransactionItem.ID='$id' AND Category.UserID='$user_id') AS Transactions Order By CatName, Name";
        $result = $database->send_SQL($SQL);
        $array = array();
        if (!$result)
            return $array;
        $row_count = mysqli_num_rows($result);
        if ($row_count == 1) {
            $currency_id = Transaction::get_currency_id($database, $transaction_id, $user_id);
            $total_amount = Transaction::get_total_balance($database, $transaction_id, $currency_id, $number_format);
            $formatted_total_amount = Utility::get_formatted_money($currency_id, $total_amount, $number_format);
            if ($total_amount > 0)
                $formatted_total_amount = "-$formatted_total_amount";
            $row = mysqli_fetch_array($result);
            $id = $row["ID"];
            $item_name = ucfirst(htmlspecialchars($row["Name"], ENT_QUOTES));
            $cat_name = ucfirst(htmlspecialchars($row["CatName"], ENT_QUOTES));
            $color = $row["Color"];
            $amount = floatval($row["Amount"]);
            $total_amount = $amount * floatval($row["Quantity"]);
            $formatted_amount = Utility::get_formatted_money($currency_id, $total_amount, $number_format);
            if ($amount > 0)
                $formatted_amount = "-$formatted_amount";
            $array[] = array("id" => $id, "category_name" => $cat_name, "category_color" => $color,
                "amount" => $amount, "item_name" => $item_name, "category_id" => $row["CategoryID"],
                "formatted_amount" => $formatted_amount,
                "total_amount" => $total_amount, "formatted_total_amount" => $formatted_total_amount, "quantity" => $row["Quantity"]
            );
        }

        return $array;
    }

    public function fix_quantity($quantity) {
        if (is_numeric($quantity) && intval($quantity) >= 1 && intval($quantity) <= 9)
            return intval($quantity);
        return 1; //default quantity
    }

    public static function fix_category_type($category_type) {
        $category_type = trim($category_type);
        if (strlen($category_type) == 0 || ($category_type != "e" && $category_type != "i"))
            return "e";
        return $category_type;
    }

    public static function validate_amount($amount) {
        if (trim($amount) == "")
            return "Amount is required";
        if (!is_numeric($amount))
            return "Amount isn't a number";
        if (floatval(abs($amount)) < TransactionItemList::$MIN_AMOUNT)
            return "Amount is too small";
        if (floatval(abs($amount)) > TransactionItemList::$MAX_AMOUNT)
            return "Amount is too large";
        return "valid";
    }

    /* function validate_transaction_item_id($database, $transaction_item_id, $transaction_id, $user_id) {
      $SQL = "SELECT * FROM Transaction ";
      $SQL.=" INNER JOIN TransactionItem ON";
      $SQL.=" Transaction.ID=TransactionItem.TransactionID ";
      $SQL.=" WHERE TransactionItem.TransactionID='$transaction_id' AND TransactionItem.ID='$transaction_item_id' AND Transaction.UserID='$user_id'";
      $sql_result = $database->send_SQL($SQL);

      if (!$sql_result || mysqli_num_rows($sql_result)!=1)
      return "invalid";
      return "valid";
      } */

    function validate_transaction_id($database, $transaction_id, $user_id) {
        $transaction_count = $database->count("Transaction", "UserID='$user_id' AND ID='$transaction_id'");
        if ($transaction_count > 0)
            return "valid";
        return "invalid";
    }

    function validate_max_num_transaction_items($database, $transaction_id) {

        $transaction_item_count = $database->count("TransactionItem", "TransactionID='$transaction_id'");

        if ($transaction_item_count >= TransactionItemList::$MAX_NUMBER_TRANSACTION_ITEMS_PER_TRANSACTION) {
            return "You can't add more than " . TransactionItemList::$MAX_NUMBER_TRANSACTION_ITEMS_PER_TRANSACTION . " items per transaction";
        }
        return "valid";
    }

    public static function get_item_info($database, $item_name, $category_name, $category_type, $user_id) {
        $item_name = trim($item_name);
        $category_name = trim($category_name);
        $result = array();
        $result["item_id"] = -2; //item is not given
        $result["category_id"] = -2; //category is not given

        if (strlen($item_name) == 0 && strlen($category_name) == 0)
            return $result;

        if (strlen($item_name) > 0)
            $result["item_id"] = -1; //item is given, but not found so far
        if (strlen($category_name) > 0)
            $result["category_id"] = -1; //category is given, but not found so far        



            
//  /** check if the given item exists **/
        if ($result["item_id"] == -1) {

            $SQL = "SELECT Item.ID AS 'ItemID', Category.ID AS 'CategoryID' ";
            $SQL.=" FROM Item ";
            $SQL.=" INNER JOIN Category ";
            $SQL.=" ON Item.CategoryID=Category.ID ";
            $SQL.=" WHERE Category.UserID='$user_id' AND LOWER(Item.Name)=LOWER('$item_name') AND Category.Type='$category_type'";
            if (strlen($category_name) > 0)
                $SQL.=" AND LOWER(Category.Name)=LOWER('$category_name')";

            $sql_result = $database->send_SQL($SQL);

            if (!$sql_result)
                return $result;

            $row_count = mysqli_num_rows($sql_result);
            if ($row_count > 0) {
                $row = mysqli_fetch_array($sql_result);
                $result["item_id"] = $row["ItemID"];
                $result["category_id"] = $row["CategoryID"];
            }
        }

        /** check if the given category exists * */
        if ($result["category_id"] == -1) {
            $result["category_id"] = TransactionItemList::get_category_id($database, $category_name, $user_id, $category_type);
        }


        return $result;
    }

    public static function get_category_id($database, $category_name, $user_id, $type) {
        $result = $database->select_fields_where("Category", "ID", "UserID='$user_id' AND LOWER(Name)=LOWER('$category_name') AND Type='$type'");
        if (!$result)
            return -1; //it doesn't exist

        $row_count = mysqli_num_rows($result);
        if ($row_count == 0)
            return -1; //it doesn't exist
        if ($row_count > 0) {
            $row = mysqli_fetch_array($result);
            return $row["ID"];
        }

        return -1;
    }

    public static function create_category($database, $name, $user_id, $type) {
        $category_count = $database->count("Category", "UserID='$user_id'");
        $max_categories=$type==CategoryList::$EXPENSE_TYPE?CategoryList::$MAX_NUMBER_EXPENSE_CATEGORIES:CategoryList::$MAX_NUMBER_INCOME_CATEGORIES;
        if ($category_count < $max_categories) {
            $values = array("UserID" => $user_id, "Name" => $name, "Color" => "#FFFFFF", "Type" => $type, "Description" => "");
            $final = $database->insert("Category", $values);
            if (!$final)
                return -1; //the item has not been inserted
            return $database->last_insert_id(); //the id of the item has been inserted 
        }
        return -1;
    }

    public static function create_item($database, $name, $category_id) {
        $item_count = $database->count("Item", "CategoryID='$category_id'");
        if ($item_count < ItemList::$MAX_NUMBER_ITEMS_PER_CATEGORY) {
            $values = array("Name" => $name, "CategoryID" => $category_id);
            $final = $database->insert("Item", $values);
            if (!$final)
                return -1; //the item has not been inserted
            return $database->last_insert_id(); //the id of the item has been inserted
        }
        return -1;
    }

}
