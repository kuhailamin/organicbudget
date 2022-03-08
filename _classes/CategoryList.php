<?php

include "Category.php";

class CategoryList {
    public static $MIN_NUMBER_EXPENSE_CATEGORIES = 3;
    public static $MIN_NUMBER_INCOME_CATEGORIES = 2;
    public static $MAX_NUMBER_EXPENSE_CATEGORIES = 100;
    public static $MAX_NUMBER_INCOME_CATEGORIES = 50;
    public static $EXPENSE_TYPE="e";
    public static $INCOME_TYPE="i";
    public static $CATEGORY_TYPES=array("e"=>"spending","i"=>"income");
    
    function __construct() {
        
    }

    function add_category($database, $name, $des, $color, $userID, $cat_type) {
        $result = array();
        $name=trim($name);
        $cat_type = $this->fix_type($cat_type);
        $name_valid = $this->validate_category_name($database, $name, $userID,$cat_type);
        $description_valid = $this->validate_category_description($des);
        $color = $this->fix_color($color);
        $max_num_categories_valid=$this->validate_max_num_categories($database, $userID,$cat_type);
        $result["category-name"] = $name_valid;
        $result["category-description"] = $description_valid;
        $result["category-type"] = "valid";
        $result["error_message"]=$max_num_categories_valid=="valid"?"":$max_num_categories_valid;
        $result["result"] = "fail";
        if ($name_valid == "valid" && $description_valid == "valid" && $max_num_categories_valid=="valid") {
            $values = array("Name" => $name, "Description" => $des, "Color" => $color, "UserID" => $userID, "Type" => $cat_type);
            $final = $database->insert("Category", $values);
            if(!$final)
                return $result;
            $result["result"] = "success";
            $result["id"]=$database->last_insert_id();
        }
        return $result;
    }
    
    function delete_category($database, $userID,$cat_id) {
        $result = array();
        $result["result"] = "fail";
        $id_valid = $this->validate_category_id($database, $cat_id);
        $category_type=$this->get_category_type($database, $cat_id, $userID);
        $min_num_categories_valid=$this->validate_min_num_categories($database, $userID, $category_type);
        $result["error_message"]=$min_num_categories_valid=="valid"?"":$min_num_categories_valid;
        if($id_valid=="valid" && $min_num_categories_valid=="valid"){
            $final = $database->delete_where("Category","ID='$cat_id' AND UserID='$userID'");
            if(!$final)
                return $result;
            
            $result["result"] = "success";
        }
        return $result;
    }   
    
    function edit_category($database, $name, $des, $color, $userID, $cat_type,$cat_id) {
        $result = array();
        $name=trim($name);
        $cat_type = $this->fix_type($cat_type);
        $id_valid = $this->validate_category_id($database, $cat_id);
        $name_valid = $this->validate_category_edit_name($database, $name, $userID,$cat_id,$cat_type);
        $description_valid = $this->validate_category_description($des);
        $color = $this->fix_color($color);
        
        $result["category-id"] = $cat_id;
        $result["category-name"] = $name_valid;
        $result["category-description"] = $description_valid;
        $result["category-type"] = "valid";
        $result["result"] = "fail";
        if ($id_valid=="valid" && $name_valid == "valid" && $description_valid == "valid") {
            $values = array("Name" => $name, "Description" => $des, "Color" => $color, "Type" => $cat_type);
            $final = $database->update("Category", $values, "ID='$cat_id'");
            if(!$final)
                return $result;
            $result["result"] = "success";
        }
        return $result;
    }
    
    function validate_category_id($database, $cat_id) {
        if ($this->category_id_exists($database, $cat_id))
            return "valid";        
        return "Category doesn't exist";
    }
    function validate_max_num_categories($database,$user_id,$cat_type) {
        $category_count = $database->count("Category", "UserID='$user_id' AND Type='$cat_type'");
        $max_category_count=$cat_type==CategoryList::$EXPENSE_TYPE?CategoryList::$MAX_NUMBER_EXPENSE_CATEGORIES:CategoryList::$MAX_NUMBER_INCOME_CATEGORIES;
        
        if($category_count>=$max_category_count){
            return "You can't add more than ".$max_category_count." ".CategoryList::$CATEGORY_TYPES[$cat_type]." categories";
        }
        return "valid";
    } 
    
    function validate_min_num_categories($database,$user_id,$cat_type) {
        $category_count = $database->count("Category", "UserID='$user_id' AND Type='$cat_type'");
        $min_category_count=$cat_type==CategoryList::$EXPENSE_TYPE?CategoryList::$MIN_NUMBER_EXPENSE_CATEGORIES:CategoryList::$MIN_NUMBER_INCOME_CATEGORIES;
        
        if($category_count<=$min_category_count){
            return "You have to have at least ".$min_category_count." ".CategoryList::$CATEGORY_TYPES[$cat_type]." categories";
        }
        return "valid";
    }    
    
    function validate_category_edit_name($database, $name, $userID,$cat_id,$cat_type) {
        $name= trim(strtolower($name));
        $category_count = $database->count("Category", "ID='$cat_id' AND LOWER(Name)=LOWER('$name') AND UserID='$userID'");
        if($category_count==1)//same category as before
          return "valid";
        return $this->validate_category_name($database, $name, $userID,$cat_type);
    }    

    function validate_category_name($database, $name, $userID,$cat_type) {
        if ($name == "")
            return "Name is required";
        if ($this->category_exists($database, $name, $userID,$cat_type))
            return "Category name exists already";
        if (strlen($name) > 30)
            return "Name can't be longer than 30 characters";
        return "valid";
    }

    function validate_category_description($description) {
        if (strlen($description) > 200)
            return "Description can't be longer than 200 characters";
        return "valid";
    }

    function fix_color($color) {
        if (!preg_match('/^#[a-f0-9]{6}$/i', $color))
            return "#FFFFFF";
        return $color;
    }

    function fix_type($type) {
        if ($type == CategoryList::$EXPENSE_TYPE || $type == CategoryList::$INCOME_TYPE)
            return $type;
        return CategoryList::$EXPENSE_TYPE;
    }
    
    function category_id_exists($database, $cat_id) {
        $category_count = $database->count("Category", "ID='$cat_id'");
        return $category_count ==1;
    }

    function category_exists($database, $name, $userID,$cat_type) {
        $name=strtolower(trim($name));
        $category_count = $database->count("Category", "UserID='$userID' AND LOWER(Name)=LOWER('$name') AND Type='$cat_type'");
        return $category_count > 0;
    }
    
    function get_category_type($database, $category_id,$user_id) {
        $result = $database->select_fields_where("Category", "Type", "ID='$category_id' AND UserID='$user_id'");
        if (!$result)
            return CategoryList::$EXPENSE_TYPE;
        $row_count = mysqli_num_rows($result);
        if($row_count!=1)
            return CategoryList::$EXPENSE_TYPE;
        $row = mysqli_fetch_array($result);
        return $row["Type"];
    }

    function get_categories($database, $user_ID, $cat_type) {
        $cat_type = $this->fix_type($cat_type);
        $result = $database->select_fields_where("Category", "*", "UserID='$user_ID' AND TYPE='$cat_type'", "ORDER BY Name");
        $array = array();
        if (!$result)
            return $array;

        $row_count = mysqli_num_rows($result);

        for ($j = 0; $j < $row_count; ++$j) {
            $row = mysqli_fetch_array($result); //fetch the next row
            $category = new Category($row["Name"], $row["Description"], $row["Color"], $row["Type"]);
            $cat_array = $category->to_array($row["ID"]);
            $item_list = new ItemList();
            $cat_array["items"] = $item_list->get_items($database, $row["ID"], $user_ID);
            $array[] = $cat_array;
        }

        return $array;
    }

}
