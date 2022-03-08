<?php

class CategoryList {

    public static $NUM_POPULAR_CATEGORIES = 4;

    function __construct() {
        
    }

    function get_categories($database) {
        $categories = array();
        $result = $database->select_fields_where("Category", "*", "", "ORDER BY Name");

        if (!$result)
            return $categories;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $category_id = $row["ID"];
            $category_name = $row["Name"];
            $category = array("category_id" => $category_id, "category_name" => $category_name);
            $categories[] = $category;
        }

        return $categories;
    }

    function get_popular_categories($database) {
        $categories = array();
        $sql = "SELECT DISTINCT Category.Name, Category.ID FROM Category";
        $sql.=" INNER JOIN Course ON Course.CategoryID=Category.ID ";
        $sql.=" GROUP BY Course.ID";
        $sql.=" HAVING SUM(Course.ID)>=4";
        $sql.=" LIMIT " . CategoryList::$NUM_POPULAR_CATEGORIES;
        $result = $database->send_SQL($sql);
        if (!$result)
            return $categories;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $category_id = $row["ID"];
            $category_name = $row["Name"];
            $category = array("category_id" => $category_id, "category_name" => $category_name);
            $categories[] = $category;
        }

        return $categories;
    }

}
