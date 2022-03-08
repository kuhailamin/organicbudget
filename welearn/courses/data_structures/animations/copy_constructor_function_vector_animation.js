var CCFVA = new Copy_Constructor_Function_Animation();

function Copy_Constructor_Function_Animation() {
    this.cell_to_animate = 0;
    this.animation_done = false;
    this.animation_interval;
    this.visible_class="data_cell";
    this.invisible_class="empty_cell";
}

Copy_Constructor_Function_Animation.prototype.start_animation=function(container) {
    
    //nothing to do
};

Copy_Constructor_Function_Animation.prototype.restart_animation=function(button) {
    for (var i = 3; i >= 0; --i)
        $("#cc_empty_cell_" + i).attr("class",CCFVA.invisible_class);
    
    $(button).html(GENERAL.RUN_ANIMATION_BUTTON_CONTENT);
    CCFVA.cell_to_animate = 0;
    CCFVA.animation_done = false;
};



Copy_Constructor_Function_Animation.prototype.run_animation=function() {
    if (!CCFVA.animation_done) {
        //animate the 6th cell
        CCFVA.animate(this);
        //now set the timer to animate the other two cells
        CCFVA.animation_interval = setInterval(CCFVA.animate, 1000, this);
    }
    else {
        CCFVA.restart_animation(this);
        clearInterval(CCFVA.animation_interval);
    }
};

Copy_Constructor_Function_Animation.prototype.animate=function(button) {
    if (CCFVA.cell_to_animate <= 3) {
        $("#cc_empty_cell_" + CCFVA.cell_to_animate).attr("class",CCFVA.visible_class);
        CCFVA.cell_to_animate++;
    }
    else {
        CCFVA.animation_done = true;
        $(button).html(GENERAL.RESTART_ANIMATION_BUTTON_CONTENT);
    }
};








