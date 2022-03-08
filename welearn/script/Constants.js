var constants=new Constants();

function Constants(){
    
    /** key codes **/
    
    this.DOWN_KEY=40;
    this.UP_KEY=38;
    this.RIGHT_KEY=39;
    this.LEFT_KEY=37;
    this.ENTER_KEY=13;
    
    /*** general **/
    this.ROOT_PATH="http://organicbudget.us/welearn/"
    this.CONTROLLER_PATH=this.ROOT_PATH+"controller/controller.php";
    this.POST_REQUEST="POST";
    
    /** sort **/
    this.ASC_SORT="ASC";
    this.DESC_SORT="DESC";    
}

