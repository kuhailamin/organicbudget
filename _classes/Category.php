<?php

class Category {

    private $name;
    private $description;
    private $color;
    private $type;
    
    
    function __construct($name = "", $description = "", $color = "",$type="") {
        $this->name = $name;
        $this->description = $description==NULL || $description=="null"?"":$description;
        $this->color = $color;
        $this->type=$type;
    }

    function get_name() {
        return $this->name;
    }

    function get_description() {
        return $this->description;
    }

    function get_color() {
        return $this->color;
    }
    
    function edit($database, $id,$userID) {
        $values = array("Name" => $this->name, "Description" => $this->description, "Color" => $this->color,"Type" => $this->type);
        return $database->update("Category", $values, "ID='$id' AND UserID='$userID'");
    }
    
    static function get_category_name_color($database,$category_id){
        $final=array("Name"=>"","Color"=>"");
        $result = $database->select_fields_where("Category", "Name,Color", "ID='$category_id'");
        if (!$result)
            return $final;
        $row = mysqli_fetch_assoc($result);
        $final["Name"]=$row["Name"];
        $final["Color"]=$row["Color"];
        return $final;
    }
    

    function delete($database, $id,$userID) {
        return $database->delete_where("Category", "ID='$id' AND UserID='$userID'");
    }

    function to_JSON($id) {
        return json_encode(to_array($id));
    }
    function to_array($id){
        $name=  ucfirst($this->name);
        return array("category_id"=>$id,"category_type"=>$this->type,"category_color"=>$this->color,"category_title"=>$name,"category_description"=>$this->description);
    }
}
?> 
