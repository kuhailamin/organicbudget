var WIFVA = new Wrong_Insert_Function_Vector_Animation();

function Wrong_Insert_Function_Vector_Animation() {
    this.index_position = 30;
    this.cell_width = 22;
    this.cell_to_change = 4;
    this.animation_done = false;
    this.animation_interval;
}

Wrong_Insert_Function_Vector_Animation.prototype.start_animation=function(container) {
    
    for (var i = 0; i < 10; ++i) {
        var index_cell = "<div class='index_cell' id='w_index_cell_" + i + "'>" + i + "</div>";
        $("#" + container).append(index_cell);
        $("#w_index_cell_" + i).css("left", i * WIFVA.cell_width + WIFVA.index_position);
        if (i < 7) {
            var filled_cell_html = "<div class='filled_cell' id='w_filled_cell_" + i + "'>" + GENERAL.getChar(i) + "</div>";
            $("#" + container).append(filled_cell_html);
            $("#w_filled_cell_" + i).css("left", i * WIFVA.cell_width + WIFVA.index_position);
        }
        else {
            var cell_html = "<div class='cell' + id='w_cell_" + i + "'></div>";
            $("#" + container).append(cell_html);
            $("#w_cell_" + i).css("left", i * WIFVA.cell_width + WIFVA.index_position);
        }
    }
//add the 4th cell, which will be animated
        filled_cell_html = "<div class='filled_cell' id='w_filled_cell_d_" + 4 + "'>" + GENERAL.getChar(4) + "</div>";
        $("#" + container).append(filled_cell_html);
        $("#w_filled_cell_d_" + 4).css("left", 4 * WIFVA.cell_width + WIFVA.index_position);
         $("#w_filled_cell_d_" + 4).css("z-index",100);

    //add cell M
    filled_cell_html = "<div class='filled_cell' id='w_filled_cell_M'>M</div>";
    $("#" + container).append(filled_cell_html);
    $("#w_filled_cell_M").css("left", 4 * WIFVA.cell_width + WIFVA.index_position + "px");
    $("#w_filled_cell_M").css("top", 80 + "px");
    $("#w_filled_cell_M").css("z-index", 101);
};

Wrong_Insert_Function_Vector_Animation.prototype.restart_animation=function(button) {
    for (var i = 6; i >= 4; --i){ //keep the cells text as they were
        $("#w_filled_cell_" + i).text(GENERAL.getChar(i));
        $("#w_filled_cell_" + i).css("background-color","#a2ddf6");
    }
    //put the animated cell where it was
    $("#w_filled_cell_d_" + 4).css("left", 4 * WIFVA.cell_width + WIFVA.index_position);
    

//put M where it was
    $("#w_filled_cell_M").css("left", 4 * WIFVA.cell_width + WIFVA.index_position + "px");
    $("#w_filled_cell_M").css("top", 80 + "px");
    $(button).html(GENERAL.RUN_ANIMATION_BUTTON_CONTENT);
    WIFVA.cell_to_change = 4;
    WIFVA.animation_done = false;
};



Wrong_Insert_Function_Vector_Animation.prototype.run_animation=function() {
    if (!WIFVA.animation_done) {
        //animate the 6th cell
        WIFVA.animate(this);
        //now set the timer to animate the other two cells
        WIFVA.animation_interval = setInterval(WIFVA.animate, 1000, this);
    }
    else {
        WIFVA.restart_animation(this);
        clearInterval(WIFVA.animation_interval);
    }
};

Wrong_Insert_Function_Vector_Animation.prototype.animate=function(button) {
    if (WIFVA.cell_to_change <= 6) {
        $("#w_filled_cell_d_" + 4).css("left", (WIFVA.cell_to_change + 1) * WIFVA.cell_width + WIFVA.index_position);
        $("#w_filled_cell_" + WIFVA.cell_to_change).text(GENERAL.getChar(4));
        $("#w_filled_cell_" + WIFVA.cell_to_change).css("background-color","#c4ffa0");
        WIFVA.cell_to_change++;
    }
    else {
        $("#w_filled_cell_M").css("top", 30 + "px"); //bring M up
        WIFVA.animation_done = true;
        $(button).html(GENERAL.RESTART_ANIMATION_BUTTON_CONTENT);
    }
};




