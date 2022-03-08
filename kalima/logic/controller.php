<?php


include "sanitization.php";
include "login.php";
include "database.php";
include "CourseList.php";




$database = new database($db_hostname, $db_database, $db_username, $db_password); //establish connection
$connection = $database->get_connection();

if (isset($_POST["type"])) {
    $type = sanitizeMYSQL($connection, $_POST["type"]);
    $returned_value = $type; //default value

    switch ($type) { 
        case "all_courses":
            $returned_value=all_courses($database);
            break;
        case "popular_courses":
            $returned_value = popular_courses($database);
            break;  
        case "course_lesson_plan_display":
            $returned_value=lesson_plan_display($database,sanitizeMYSQL($connection, $_POST["course_id"]));
            break;
    }
    echo $returned_value;
}

function all_courses($database){
    $course=new CourseList();
    $result=$course->get_all_courses($database);
    return json_encode($result);
}

function lesson_plan_display($database,$course_id){
   $course=new CourseList();
   $result=$course->get_course_lesson_plan_display($database, $course_id);
   return json_encode($result);
}

function popular_courses($database){
   $course=new CourseList();
   $result=$course->get_popular_courses($database);
   return json_encode($result);
}


?>



