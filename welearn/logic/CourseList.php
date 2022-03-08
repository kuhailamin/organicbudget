<?php

class CourseList {

    public static $NUM_POPULAR_COURSES_PER_CATEGORY = 4;
    private $course_sql=" SELECT Course.ID course_id, Course.Name course_name, Course.Views course_views, "
                . " CASE
               WHEN Course.Difficulty=1 THEN 'Easy'
               WHEN Course.Difficulty=2 THEN 'Intermediate'
               WHEN Course.Difficulty=3 THEN 'Advanced'
               END course_difficulty,"
                . " Category.ID category_id, Category.Name category_name, ProgrammingLanguage.Name programming_language_name, Course.course_link course_link"
                . " FROM Course"
                . " INNER JOIN Category"
                . " ON Course.CategoryID=Category.ID"
                . " LEFT JOIN CourseProgrammingLanguage"
                . " ON Course.ID=CourseProgrammingLanguage.CourseID"
                . " LEFT JOIN ProgrammingLanguage"
                . " ON ProgrammingLanguage.ID=CourseProgrammingLanguage.ProgrammingLanguageID";

    function __construct() {
        
    }
    function get_lessons($database,$course_id) {
        $lessons=array();
        $result=$database->select_fields_where("Lesson", "ID,Name,lesson_link,major_lesson,CourseID","CourseID='$course_id'", "ORDER BY lesson_number");
        if(!$result)
            return $lessons;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $lesson_id = $row["ID"];
            $lesson_name = $row["Name"];
            $major_lesson = $row["major_lesson"];
            $lesson_link = $row["lesson_link"];
            $course_id=$row["CourseID"];
            $lesson = array("lesson_id"=>$lesson_id,"lesson_name"=>$lesson_name,"major_lesson"=>$major_lesson,
                "lesson_link"=>$lesson_link,"course_id"=>$course_id
            );  
            $lessons[]=$lesson;
        }
        
        return $lessons;        
    }    
    
    function get_courses_for_category($database,$category_id) {
        $course_map=array();
        $sql=$this->course_sql;
        $sql.=" WHERE Category.ID='$category_id'";
        $result = $database->send_SQL($sql);
        if(!$result)
            return $course_map;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $course_id = $row["course_id"];
            $course_name = $row["course_name"];
            $course_difficulty = $row["course_difficulty"];
            $course_link = $row["course_link"];
            $category_name=$row["category_name"];
            $programming_language_name = $row["programming_language_name"];
            $course_views=$row["course_views"];
            $programming_languages=$programming_language_name==NULL || !isset($programming_language_name)?array():array($programming_language_name);
            $course = array("course_id"=>$course_id,"course_name" => $course_name, "course_difficulty" => $course_difficulty,
                "course_link" => $course_link, "programming_languages" => $programming_languages,"course_views"=>$course_views,"category_name"=>$category_name
            );  
            if (array_key_exists($course_id, $course_map)) {
                    $course_map[$course_id]["programming_languages"][]=$programming_language_name;
                } else {
                    $course_map[$course_id] = $course;
                }
        }
        
        return $course_map;        
    }
    function get_all_course_names($database) {
        $courses=array();
        $course_sql=" SELECT Course.Keywords course_keywords, Course.Description course_description, Course.ID course_id, Course.Name course_name, Course.Views course_views, "
                . " CASE
               WHEN Course.Difficulty=1 THEN 'Easy'
               WHEN Course.Difficulty=2 THEN 'Intermediate'
               WHEN Course.Difficulty=3 THEN 'Advanced'
               END course_difficulty,"
                . " CategoryID category_id, ProgrammingLanguage.Name programming_language_name, Course.course_link course_link"
                . " FROM Course"
                . " LEFT JOIN CourseProgrammingLanguage"
                . " ON Course.ID=CourseProgrammingLanguage.CourseID"
                . " LEFT JOIN ProgrammingLanguage"
                . " ON ProgrammingLanguage.ID=CourseProgrammingLanguage.ProgrammingLanguageID";

        $result = $database->send_SQL($course_sql);

        if(!$result)
            return $courses;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $course_id = $row["course_id"];
            $course_name = $row["course_name"];
            $course_description=$row["course_description"];
            $course_link=$row["course_link"];
            $course_difficulty = $row["course_difficulty"];
            $category_id=$row["category_id"];
            $category_keywords=$row["course_keywords"];
            $programming_language_name = $row["programming_language_name"];
            $programming_languages=$programming_language_name==NULL || !isset($programming_language_name)?array():array($programming_language_name);
            //check if course exists
            $course_found=false;
            foreach ($courses as $key => $value) {
                if($courses[$key]["course_id"]==$course_id){
                    $course_found=true;
                    $courses[$key]["programming_languages"][]=$programming_language_name;
                    break;
                }
            }
            if(!$course_found){
                $course = array("course_keywords"=>$category_keywords,"category_id"=>$category_id,"course_id"=>$course_id,"course_difficulty"=>$course_difficulty, "course_name" => $course_name,"course_description"=>$course_description,"course_link"=>$course_link, "programming_languages" => $programming_languages,"lessons"=>array()); 
                $courses[]=$course;
            }
        }
        
        return $courses;            
    }    

    function get_popular_courses($database) {
        $category_map = array();
        $category_sql=" SELECT Category.ID FROM Category";
        $result = $database->send_SQL($category_sql);
        if(!$result)
            return $category_map;
        
        $course_sql="";
        $index=0;
         while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
             $category_id=$row["ID"];
             $sql = "( SELECT ID FROM Course"
                     ." WHERE CategoryID=$category_id Order By Views DESC"
                    ." LIMIT ". CourseList::$NUM_POPULAR_COURSES_PER_CATEGORY." ) "; 
             if($index>0)
                 $course_sql.=" UNION $sql";
             else
                 $course_sql=$sql;
             ++$index;
         }
        $result = $database->send_SQL($course_sql);
        if(!$result)
            return $category_map;
        $index=0;
        $final_sql=$this->course_sql;
         while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
             $course_id=$row["ID"];
             if($index>0)
                 $final_sql.=" OR Course.ID= $course_id";
             else if($index==0)
                 $final_sql.=" WHERE Course.ID=$course_id";
             ++$index;
         }
                 
        $result = $database->send_SQL($final_sql);
        if(!$result)
            return $category_map;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $category_id = $row["category_id"];
            $course_id = $row["course_id"];
            $course_name = $row["course_name"];
            $course_difficulty = $row["course_difficulty"];
            $course_link = $row["course_link"];
            $category_name = $row["category_name"];
            $programming_language_name = $row["programming_language_name"];
            $course_views=$row["course_views"];
            $programming_languages=$programming_language_name==NULL || !isset($programming_language_name)?array():array($programming_language_name);
            $course = array("course_id"=>$course_id,"course_name" => $course_name, "course_difficulty" => $course_difficulty,
                "course_link" => $course_link, "programming_languages" => $programming_languages,"course_views"=>$course_views
            );

            $category_array = array("category_id"=>$category_id,"category_name" => $category_name, "courses" => array());
            if (array_key_exists($category_id, $category_map)) {
                $category_array = $category_map[$category_id];
                $courses = $category_array["courses"];
                if (array_key_exists($course_id, $courses)) {
                    $category_map[$category_id]["courses"][$course_id]["programming_languages"][]=$programming_language_name;
                } else {
                    $category_map[$category_id]["courses"][$course_id] = $course;
                }
            } else {
                $category_array["courses"] = array($course_id=>$course);
                $category_map[$category_id] = $category_array;
            }
        }
        
        return $category_map;
    }
    

}
