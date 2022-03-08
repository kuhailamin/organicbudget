<?php

class Budget {

    private $id;
    private $name;
    private $description;
    private $start;
    private $end;
    static public $MAX_YEAR="2030";
    static public $MIN_YEAR="2016";
    static public $MIN_MONTH="1";
    static public $MAX_MONTH="12";

    function __construct($id,$name = "", $description = "", $start = "", $end = "") {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->start = $start;
        $this->end = $end;
    }
    
    static function delete_all_targets($database,$budget_id){
        $result = $database->delete_where("Target","BudgetID='$budget_id'");
        if(!$result)
            return false;        
        return true;
    }    
    


    public static function fixDates($start_month,$start_year, $end_month,$end_year) {
        $start_month=  intval($start_month);
        $start_year=  intval($start_year);
        $end_month=  intval($end_month);
        $end_year=  intval($end_year);
        
        
        if($start_month>Budget::$MAX_MONTH)
            $start_month=Budget::$MAX_MONTH;
        if($end_month>Budget::$MAX_MONTH)
            $end_month=Budget::$MAX_MONTH;
        
        if($start_month<Budget::$MIN_MONTH)
            $start_month=Budget::$MIN_MONTH;
        if($end_month<Budget::$MIN_MONTH)
            $end_month=Budget::$MIN_MONTH;        
        
        if($start_year>Budget::$MAX_YEAR)
            $start_year=Budget::$MAX_YEAR;
        if($end_year>Budget::$MAX_YEAR)
            $end_year=Budget::MAX_YEAR;
        
        if($start_year<Budget::$MIN_YEAR)
            $start_year=Budget::$MIN_YEAR;
        if($end_year<Budget::$MIN_YEAR)
            $end_year=Budget::MIN_YEAR;        
        

        if ($end_year < $start_year || (($end_year == $start_year) && $end_month < $start_month)) {
            $end_month = $start_month;
            $end_year = $start_year;
        }
        $start_date="$start_year/$start_month/1";
        $end_date="$end_year/$end_month/1";

        return array("Start" => $start_date, "End" => $end_date);
    }

    function edit($database, $id, $userID) {
        $dates = Budget::fixDates($this->start, $this->end);
        $values = array("Name" => $this->name, "Description" => $this->description, "Start" => $dates["Start"], "End" => $dates["End"]);
        return $database->update("Budget", $values, "ID='$id' AND UserID='$userID'");
    }

    function delete($database, $id, $userID) {
        return $database->delete_where("Budget", "ID='$id' AND UserID='$userID'");
    }
      
    function to_array($database) {
        $name=ucfirst($this->name);
        $target_categories="";
        $target_result = $database->select_fields_where("Target", "CategoryID", "BudgetID='$this->id'");
        if (!$target_result){
            //do nothing
        }
        else{
            $index=0;
            while ($row = mysqli_fetch_assoc($target_result)) {//fetch the next row
                if($index>0)
                    $target_categories.=" ";
                $target_categories.=$row['CategoryID'];
                ++$index;                
            }            
            }
           
        
        $start_date=new DateTime($this->start);
        $start_formatted=  date_format($start_date,"n/Y");
        $start_month=  date_format($start_date,"n");
        $start_year=  date_format($start_date,"Y");
        
        
        $end_date=new DateTime($this->end);
        $end_month=  date_format($end_date,"n");
        $end_year=  date_format($end_date,"Y");
        
        $end_formatted=  date_format($end_date,"n/Y");
        if($start_date!=$end_date)
            $start_formatted.="&nbsp&nbsp-&nbsp&nbsp";
        else 
            $end_formatted="";
                
        return array("id" => $this->id, "budget_name" => $name, "budget_description" => $this->description, 
            "budget_start"=>$start_formatted,"budget_end"=>$end_formatted,"start_month"=>$start_month,
            "start_year"=>$start_year,"end_month"=>$end_month,"end_year"=>$end_year,"target_categories"=>$target_categories);
    }    
}
