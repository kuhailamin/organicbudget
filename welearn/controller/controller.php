<?php


include "sanitization.php";
include "login.php";
include "../logic/database.php";
include "../logic/CourseList.php";
include "../logic/CategoryList.php";



$database = new database($db_hostname, $db_database, $db_username, $db_password); //establish connection
$connection = $database->get_connection();


if (isset($_POST["type"])) {
    $type = sanitizeMYSQL($connection, $_POST["type"]);
    $returned_value = $type; //default value

    switch ($type) { 
        case "popular_courses":
            $returned_value = popular_courses($database);
            break; 
        case "courses_for_category":
            $returned_value = courses_for_category($database,sanitizeMYSQL($connection, $_POST["category_id"]));
            break; 
        case "lessons":
            $returned_value = lessons($database,sanitizeMYSQL($connection, $_POST["course_id"]));
            break;          
        case "catalog":
            $returned_value = categories($database);
            break;
        case "popular_categories":
            $returned_value = popular_categories($database);            
            break;
        case "all_course_names":
            $returned_value = all_course_names($database);
            break;          
    }
    echo $returned_value;
}

function all_course_names($database){
    $course_list=new CourseList();
    $result=$course_list->get_all_course_names($database);
    return json_encode($result);      
}

function categories($database){
    $category_list=new CategoryList();
    $result=$category_list->get_categories($database);
    return json_encode($result);      
}

function popular_categories($database){
    $category_list=new CategoryList();
    $result=$category_list->get_popular_categories($database);
    return json_encode($result);      
}


function popular_courses($database){
    $course=new CourseList();
    $result=$course->get_popular_courses($database);
    return json_encode($result);      
}

function courses_for_category($database,$category_id){
    $course=new CourseList();
    $result=$course->get_courses_for_category($database, $category_id);
    return json_encode($result);      
}

function lessons($database,$course_id){
    $course=new CourseList();
    $result=$course->get_lessons($database, $course_id);
    return json_encode($result);      
}

?>



