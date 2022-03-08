var constants=new Constants();

function Constants(){
    
    /** key codes **/
    
    this.DOWN_KEY=40;
    this.UP_KEY=38;
    this.RIGHT_KEY=39;
    this.LEFT_KEY=37;
    this.ENTER_KEY=13;
    
    /*** general **/
    this.ROOT_PATH="http://organicbudget.us/kalima/"
    this.COURSE_PATH=this.ROOT_PATH+"course/?course_id=";
    this.COURSE_SEARCH_PATH=this.ROOT_PATH+"course_search/?course_name="
    this.CONTROLLER_PATH=this.ROOT_PATH+"logic/controller.php";
    this.POST_REQUEST="POST";
    
    /** sort **/
    this.ASC_SORT="ASC";
    this.DESC_SORT="DESC";    
}

