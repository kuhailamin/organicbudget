<?php

class CourseList {

    public static $NUM_POPULAR_COURSES_PER_LEVEL = 4;
    public static $LEVELS =array("1"=>"1","2"=>"2","3"=>"3");
    public static $DIALECTS=array("x"=>"All","1"=>"Fusha","2"=>"Palestinian","3"=>"Egyptian","4"=>"Iraqi","5"=>"Saudi");

    function __construct() {
        
    }
    
    function get_course_lesson_plan_display($database,$course_id){
        $course=array();
        $course["lessons"]=array();
        $course["prerequisites"]=array();
        
        //get basic course info
        $course_sql=" SELECT Course.ID 'course_id', Course.NAME 'course_name', Course.Description 'course_description', 
                      Course.Icon 'course_icon',Course.ageFrom 'course_age_from', Course.Level 'course_level',
                      Course.Course_Arabic_Name 'course_arabic_name', Course.Dialect 'course_dialect', Course.VideoLink 'course_video_link'
                      FROM Course
                      WHERE Course.ID='$course_id'";
        $course_result = $database->send_SQL($course_sql);
        if(!$course_result)
            return $course;
        $course_row = mysqli_fetch_assoc($course_result);
        $course["course_id"]=$course_row["course_id"];
        $course["course_name"]=$course_row["course_name"];
        $course["course_description"]=$course_row["course_description"];
        $course["course_icon"]=$course_row["course_icon"];
        $course["course_age_from"]=$course_row["course_age_from"];
        $course["course_level"]=CourseList::$LEVELS[$course_row["course_level"]];
        $course["course_level_as_number"]=$course_row["course_level"];
        $course["course_arabic_name"]=$course_row["course_arabic_name"];
        $course["course_dialect"]=CourseList::$DIALECTS[$course_row["course_dialect"]];
        $course["course_dialect_as_number"]=$course_row["course_dialect"];
        $course["course_video_link"]=$course_row["course_video_link"];
        
        //get basic course prerequesite information
        $pre_sql="SELECT Prerequisite.PrerequisiteID 'course_id', Course.Name 'course_name' FROM Prerequisite
                  INNER JOIN Course
                  ON Prerequisite.PrerequisiteID=Course.ID
                  WHERE Prerequisite.CourseID='$course_id'";
        $pre_result = $database->send_SQL($pre_sql);
        if(!$pre_result)
            return $course;    
        while ($pre_row = mysqli_fetch_assoc($pre_result)) {//fetch the next row
             $pre=array();
             $pre["course_id"]=$pre_row["course_id"];
             $pre["course_name"]=$pre_row["course_name"];
             $course["prerequisites"][]=$pre;
        }        
        
        //get lesson information
        
        $lesson_sql="SELECT Lesson.Number 'lesson_number', Lesson.Name 'lesson_name', Lesson.NumberOfSessions 'lesson_no_sessions'
                     FROM Lesson
                     WHERE Lesson.CourseID='$course_id'";
        
        $lesson_result = $database->send_SQL($lesson_sql);
        if(!$lesson_result)
            return $course;           
        
        while ($lesson_row = mysqli_fetch_assoc($lesson_result)) {//fetch the next row
             $lesson=array();
             $lesson["lesson_number"]=$lesson_row["lesson_number"];
             $lesson["lesson_name"]=$lesson_row["lesson_name"];
             $lesson["lesson_no_sessions"]=$lesson_row["lesson_no_sessions"];
             $course["lessons"][]=$lesson;
             
        }
        
        return $course;
    }
    
    function get_all_courses($database){
        $courses = array();
        $course_sql="  SELECT Count(Lesson.ID) AS num_lessons,Course.ICON AS course_icon, Course.Description AS course_description, Course.ID AS course_id, Course.Name AS course_name, Course.Level AS course_level,
                       Course.Course_Arabic_Name AS course_arabic_name, Course.Dialect AS course_dialect
                      FROM Course
                      LEFT JOIN Lesson
                      ON Course.ID=Lesson.CourseID
                      Group by Course.ID
                      ";
        $result = $database->send_SQL($course_sql);
        
        if(!$result)
            return $courses;
        
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $course=array();
            $course["course_description"]=$row["course_description"];
            $course["course_id"]=$row["course_id"];
            $course["course_name"]=$row["course_name"];
            $course["course_arabic_name"]=$row["course_arabic_name"];
            $course["course_icon"]=$row["course_icon"];
            $course["course_num_lessons"]=$row["num_lessons"];
            $course["course_level"]=  CourseList::$LEVELS[$row["course_level"]];
            $course["course_dialect"]=CourseList::$DIALECTS[$row["course_dialect"]];
            $courses[]=$course;                        
        }
        return $courses;   
    }
   

    function get_popular_courses($database) {
        
        $courses = array();
        //only the levels that are 1 and 2
        $level_sql=" SELECT DISTINCT Course.Level AS course_level FROM Course WHERE Course.Level<=2";
        $result = $database->send_SQL($level_sql);
        if(!$result)
            return $courses;
          $sql="";
        $index=0;
         while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
             $level_id=$row["course_level"];
             $courses+= array($level_id => array());
             $sub_sql = " (SELECT Course.ID AS course_id, Count(Lesson.ID) AS num_lessons,Course.Name AS course_name, Course.Description AS course_description, Course.Icon AS course_icon,
                       Course.ageFrom AS course_age_from, Course.ageTo AS course_age_to, Course.Level AS course_level,
                       Course.Course_Arabic_Name AS course_arabic_name, Course.Dialect AS course_dialect,
                       COUNT(Enrollment.ID) AS enrollment_count
                       FROM Course
                       LEFT JOIN Enrollment
                       ON Course.ID=Enrollment.CourseID
                       LEFT JOIN Lesson
                       ON Course.ID=Lesson.CourseID
                       WHERE Course.Level=$level_id
                       GROUP BY Course.ID, Course.Level
                       ORDER BY enrollment_count
                       LIMIT ". CourseList::$NUM_POPULAR_COURSES_PER_LEVEL." ) "; 
             if($index>0)
                 $sql.=" UNION $sub_sql";
             else
                 $sql.=$sub_sql;
             ++$index;
         }
        $result = $database->send_SQL($sql);
         $courses=array();
        if(!$result)
            return $courses;
        while ($row = mysqli_fetch_assoc($result)) {//fetch the next row
            $course_level = $row["course_level"];
            $course_id=$row["course_id"];
            $course_num_lessons=$row["num_lessons"];
            $course_name=utf8_encode($row["course_name"]);
            $course_description=$row["course_description"];
            $course_icon=$row["course_icon"];
            $course_age_from=$row["course_age_from"];
            $course_age_to=$row["course_age_to"];
            $course_arabic_name= $row["course_arabic_name"];
            $course_level_name=  CourseList::$LEVELS[$course_level];
            $course_dialect_id=$row["course_dialect"];
            $course_dialect=CourseList::$DIALECTS[$row["course_dialect"]];
            $course=array("course_dialect_id"=>$course_dialect_id,"course_name"=>$course_name,"course_description"=>$course_description,"course_icon"=>$course_icon,
                    "course_age_from"=>$course_age_from,"course_age_to"=>$course_age_to,"course_id"=>$course_id,"course_level"=>$course_level_name,
                    "course_arabic_name"=>$course_arabic_name,"course_dialect"=>$course_dialect,"course_num_lessons"=>$course_num_lessons
                    );
            $courses[$course_level][]=$course;
            
        }
                 
           return $courses;      
    }

    

}
