<?php

include "connection.php";
include "utility.php";


if (isset($_POST["type"])) {
    $type = sanitizeMYSQL($connection,$_POST["type"]);
    $returned_value = ""; //default value

    switch ($type) {       
        case "tasks": //this is a call for displaying the tasks
            $returned_value = display_tasks($connection);
            break;  
        case "complete":
            $returned_value = complete_task($connection,sanitizeMYSQL($connection,$_POST["id"]),sanitizeMYSQL($connection,$_POST["status"]));
            break;
        case "edit":
            $returned_value = edit_task($connection,sanitizeMYSQL($connection,$_POST["id"]),sanitizeMYSQL($connection,$_POST["name"]),sanitizeMYSQL($connection,$_POST["date"]),sanitizeMYSQL($connection,$_POST["priority"]));
            break;    
        case "delete":
            $returned_value = delete_task($connection,sanitizeMYSQL($connection,$_POST["id"]));
            break; 
        case "add":
            $returned_value = add_task($connection,sanitizeMYSQL($connection,$_POST["name"]),sanitizeMYSQL($connection,$_POST["deadline"]),sanitizeMYSQL($connection,$_POST["priority"]));
            break; 
        case "search":
            $returned_value = task_search($connection,sanitizeMYSQL($connection,$_POST["name"]),sanitizeMYSQL($connection,$_POST["priority"]),sanitizeMYSQL($connection,$_POST["month"]),sanitizeMYSQL($connection,$_POST["year"]),sanitizeMYSQL($connection,$_POST["status"]));
            break;
        case "sort":
            $returned_value = sort_task($connection,sanitizeMYSQL($connection,$_POST["sort_by"]),sanitizeMYSQL($connection,$_POST["sort_type"]));           
            break;
            
    }
    echo $returned_value;
}

function complete_task($connection,$id,$status){
    $query = "UPDATE Task SET Completed=$status WHERE ID=$id";
    $result = mysqli_query($connection, $query);
    if(!$result)
        return "fail";
    return "success";
}

function add_task($connection,$name,$deadline,$priority){
    $query = "INSERT INTO Task(Name,Deadline,Priority,Completed) VALUES('$name','$deadline','$priority',FALSE)";
    $result = mysqli_query($connection, $query);
    if(!$result)
        return "fail";
    return "success";
}

function edit_task($connection,$id,$name,$date,$priority){

    $query = "UPDATE Task SET Name='$name', Deadline='$date', Priority='$priority' WHERE ID=$id";
    $result = mysqli_query($connection, $query);
    if(!$result)
        return "fail";
    return "success";
}

function delete_task($connection,$id){
    $query = "DELETE FROM Task WHERE ID=$id";
    $result = mysqli_query($connection, $query);
    if(!$result)
        return "fail";
    return "success";
}

function task_search($connection,$name,$priority,$month,$year,$status){
    $name_string=$name==""?"TRUE":" Name LIKE '%$name%' ";
    $priority_string=$priority=="any"?"TRUE":" Priority='$priority'";
    $month_string=$month=="any"?"TRUE":" Month(Deadline)=$month";
    $year_string=$year=="any"?"TRUE":" Year(Deadline)=$year";
    $status_string=$status=="any"?"TRUE":" Completed=$status";
    
    $query = "SELECT * FROM Task WHERE  $name_string  AND  $priority_string  AND $month_string  AND $year_string  AND $status_string";
    $result = mysqli_query($connection, $query);
    return get_data_for_tasks($result);    
}

function sort_task($connection,$sort_by,$sort_type){
    if($sort_by=="Priority"){
        $query=" SELECT * FROM  Task WHERE Completed=FALSE "
               . " ORDER BY "
               ." (CASE WHEN Priority='H' THEN 3"
               ."       WHEN Priority='M' THEN 2"
               ."       WHEN Priority='L' THEN 1"
               ."       ELSE -1 END) $sort_type";
     }
    else{
        $query=" SELECT * FROM Task WHERE Completed=FALSE ORDER BY $sort_by  $sort_type";
    }
    
    $result = mysqli_query($connection, $query);
    return get_data_for_tasks($result);    
}


function display_tasks($connection) {
    $query = "SELECT * FROM Task WHERE Completed=FALSE Order By Deadline ASC";
    $result = mysqli_query($connection, $query);
    return get_data_for_tasks($result);
}

function get_data_for_tasks($result){
    $final_result=array();
    if ($result) {
        $row_count = mysqli_num_rows($result);
        for($i=0;$i<$row_count;++$i){
            $row = mysqli_fetch_array($result);
            $status=$row["Completed"]?"checked":"";
            $high_selected=$row["Priority"]=="H"?"selected":"";
            $medium_selected=$row["Priority"]=="M"?"selected":"";
            $low_selected=$row["Priority"]=="L"?"selected":"";
            $date=date_create($row["Deadline"]);
            $formatted_date=date_format($date,"d/m/Y");
            $item = array("id"=>$row["ID"],"name"=>$row["Name"], "checked"=>$status,"date"=>$formatted_date,"priority"=>$row["Priority"],"high_selected"=>$high_selected,"medium_selected"=>$medium_selected,"low_selected"=>$low_selected);
            $final_result["tasks"][]=$item;
        }
    }
    return json_encode($final_result);   
}




