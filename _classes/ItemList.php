<?php

class ItemList {
    
    public static $MAX_NUMBER_ITEMS_PER_CATEGORY = 100;
    
    function __construct() {
        
    }

    function change_item_category($database,$item_id, $cat_ID, $userID) {
        $result=array();
        $result["result"]="fail";
        $result["category"]=$this->validate_category($database,$cat_ID, $userID);
        $result["item_id"]=$this->validate_item_id($database,$item_id,$userID);
        $result["item_exists"]=$this->validate_item_exists($database,$item_id,$cat_ID);
        $max_num_items_per_category_valid=$this->validate_max_num_items_per_category($database, $cat_ID);
        $result["error_message"]=$max_num_items_per_category_valid=="valid"?"":$max_num_items_per_category_valid;


        if ($result["item_id"] == "valid" && $result["category"] == "valid" && $result["item_exists"]=="valid" && $max_num_items_per_category_valid=="valid") {
                $values = array("CategoryID" => $cat_ID);
                $final = $database->update("Item",$values,"ID='$item_id'");
                $final = $database->update("Income",$values,"ItemID='$item_id'");
                $final = $database->update("TransactionItem",$values,"ItemID='$item_id'");
                $result["result"]="success";
            if (!$final)
                $result["result"]="fail";
        }
        return $result;
    }    

    function delete_item($database,$item_id, $cat_ID, $userID) {
        $result=array();
        $result["result"]="fail";
        $result["category"]=$this->validate_category($database,$cat_ID, $userID);
        $result["name"]=$this->validate_delete_item_name($database,$item_id,$cat_ID);

        if ($result["name"] == "valid" && $result["category"] == "valid") {
            $final = $database->delete_where("Item", "ID='$item_id'");
                $result["result"]="success";
            if (!$final)
                $result["result"]="fail";
        }
        return $result;
    }  
    
    function edit_item($database,$name,$item_id, $cat_ID, $userID) {
        $result=array();
        $name=trim(strtolower($name));
        $result["result"]="fail";
        $result["category"]=$this->validate_category($database,$cat_ID, $userID);
        $result["name"]=$this->validate_edit_item_name($database,$name,$item_id,$cat_ID);

        if ($result["name"] == "valid" && $result["category"] == "valid") {
                $values = array("Name" => $name);
                $final = $database->update("Item",$values,"ID='$item_id' AND CategoryID='$cat_ID'");
                $result["result"]="success";
            if (!$final)
                $result["result"]="fail";
        }
        return $result;
    }    

    function validate_max_num_items_per_category($database,$category_id) {
        $item_count = $database->count("Item", "CategoryID='$category_id'");
        if($item_count>=ItemList::$MAX_NUMBER_ITEMS_PER_CATEGORY){
            return "You can't add more than ".ItemList::$MAX_NUMBER_ITEMS_PER_CATEGORY." items per category";
        }
        return "valid";
    } 
    
    function add_item($database,$name, $cat_ID, $userID) {
        $name=trim(strtolower($name));
        $result=array();
        $result["result"]="fail";
        $result["category"]=$this->validate_category($database,$cat_ID, $userID);
        $max_num_items_per_category_valid=$this->validate_max_num_items_per_category($database, $cat_ID);
        $result["name"]=$this->validate_item_name($database,$name, $cat_ID);
        $result["error_message"]=$max_num_items_per_category_valid=="valid"?"":$max_num_items_per_category_valid;

        if ($result["name"] == "valid" && $result["category"] == "valid" && $max_num_items_per_category_valid=="valid") {
                $values = array("Name" => $name, "CategoryID" => $cat_ID);
                $final = $database->insert("Item", $values);
                if (!$final)
                    $result["result"]="fail";
                $result["result"]="success";
                $result["id"]=$database->last_insert_id();
        }
        return $result;
    }
    
    function validate_item_name($database,$name,$cat_ID){
        $name=trim(strtolower($name));
        if($name=="")
            return "Item name is required";
         $item_count=$database->count("Item", "LOWER(Name)=LOWER('$name') AND CategoryID='$cat_ID'");
         if($item_count>0)
            return "Item exists within the same category";
         return "valid";
    }
    function validate_delete_item_name($database,$item_ID,$cat_ID){
        $item_count=$database->count("Item", "CategoryID='$cat_ID' AND ID='$item_ID'");
         if($item_count==1)
            return "valid";
         return "Item doesn't exist";
    }       
    
    function validate_edit_item_name($database,$name,$item_ID,$cat_ID){
        $name=trim(strtolower($name));
        $item_count=$database->count("Item", "LOWER(Name)=LOWER('$name') AND ID='$item_ID'");
         if($item_count==1)
            return "valid";
         return $this->validate_item_name($database, $name, $cat_ID);
    }    
    
    function validate_category($database,$cat_ID,$userID){
        $cat_count = $database->count("Category", "ID='$cat_ID' AND UserID='$userID'");
        if($cat_count==1)
            return "valid";
        return "Category doesn't exist";
    }
    
    function validate_item_id($database, $item_ID, $user_ID) {
        $result = $database->inner_join_SQL("Item", "Category", "Item.Name, Item.ID", "Category.ID=Item.CategoryID", "Item.ID='$item_ID' AND Category.UserID='$user_ID'");
        $error_message="Item doesn't exist";
        if (!$result)
            return $error_message;
        
        $row_count=mysqli_num_rows($result);
        
        if($row_count==1)
            return "valid";
        return $error_message;
    }
    
    function validate_item_exists($database, $item_id, $cat_id) {
        $error_message="item exists within the category";
        
        $item_result=$database->select_fields_where("Item", "Name, CategoryID", "ID='$item_id'");
        if(!$item_result)
            return $error_message;
        
        $num_rows=mysqli_num_rows($item_result);
        
        if($num_rows==0)
            return $error_message;
        
        if($num_rows==1){
            $row = mysqli_fetch_assoc($item_result);
            $item_name=$row["Name"];
            $item_result_2=$database->select_fields_where("Item", "Name, CategoryID", "LOWER(Name)=LOWER('$item_name') AND CategoryID='$cat_id'");
            if(!$item_result_2)
                return $error_message;
            
            $num_rows_2=mysqli_num_rows($item_result_2);
            if($num_rows_2==0)
                return "valid";
            if($num_rows_2>0)
                return $error_message;            
        }
        
        return $error_message;
    }    

    
    function get_items($database, $cat_ID, $user_ID) {
        $result = $database->inner_join_SQL("Item", "Category", "Item.Name, Item.ID", "Category.ID=Item.CategoryID", "CategoryID='$cat_ID' AND UserID='$user_ID'", "ORDER BY Item.Name");
        $array=array();
        if (!$result)
            return $array;
        
        $row_count = mysqli_num_rows($result);

        for ($j = 0; $j < $row_count; ++$j) {
            $row = mysqli_fetch_array($result); //fetch the next row
            $id = $row["ID"];
            $name= ucfirst(htmlspecialchars($row["Name"], ENT_QUOTES));
            $array[]=array("item_id"=>$id,"item_name"=>$name);
        }
        
        return $array;
    }

}

?>