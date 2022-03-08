<?php

class CatagoryList {


    function __construct() {
        
    }
    
    function get_categories($database) {
        $categories=array();
        $result = $database->select_fields_where("Category", "*", "", "ORDER BY Name");

        if(!$result)
            return $categories;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $category_id = $row["ID"];
            $category_name = $row["Name"];
            $category = array("category_id"=>$category_id,"category_name" => $category_name); 
            $categories[]=$category;
        }
        
        return $categories;        
    }    

}

