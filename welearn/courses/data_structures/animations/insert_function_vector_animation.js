var IFVA = new Insert_Function_Vector_Animation();

function Insert_Function_Vector_Animation() {
    this.index_position = 30;
    this.cell_width = 22;
    this.cell_to_animate = 6;
    this.animation_done = false;
    this.animation_interval;
}

Insert_Function_Vector_Animation.prototype.start_animation=function(container) {

    for (var i = 0; i < 10; ++i) {
        var index_cell = "<div class='index_cell' id='index_cell_" + i + "'>" + i + "</div>";
        $("#" + container).append(index_cell);
        $("#index_cell_" + i).css("left", i * IFVA.cell_width + IFVA.index_position);
        if (i < 7) {
            var filled_cell_html = "<div class='filled_cell' id='filled_cell_" + i + "'>" + GENERAL.getChar(i) + "</div>";
            $("#" + container).append(filled_cell_html);
            $("#filled_cell_" + i).css("left", i * IFVA.cell_width + IFVA.index_position);
        }
        else {
            var cell_html = "<div class='cell' + id='cell_" + i + "'></div>";
            $("#" + container).append(cell_html);
            $("#cell_" + i).css("left", i * IFVA.cell_width + IFVA.index_position);
        }
    }

    for (var i = 6; i >= 4; --i) {
        var filled_cell_html = "<div class='filled_cell' id='filled_cell_d_" + i + "'>" + GENERAL.getChar(i) + "</div>";
        $("#" + container).append(filled_cell_html);
        $("#filled_cell_d_" + i).css("left", i * IFVA.cell_width + IFVA.index_position);
    }

    //add cell M
    filled_cell_html = "<div class='filled_cell' id='filled_cell_M'>M</div>";
    $("#" + container).append(filled_cell_html);
    $("#filled_cell_M").css("left", 4 * IFVA.cell_width + IFVA.index_position + "px");
    $("#filled_cell_M").css("top", 80 + "px");
};

Insert_Function_Vector_Animation.prototype.restart_animation=function(button) {
    for (var i = 6; i >= 4; --i){
        $("#filled_cell_d_" + i).css("background-color","#a2ddf6");
        $("#filled_cell_d_" + i).css("left", i * IFVA.cell_width + IFVA.index_position);
    }

    $("#filled_cell_M").css("left", 4 * IFVA.cell_width + IFVA.index_position + "px");
    $("#filled_cell_M").css("top", 80 + "px");
    $(button).html(GENERAL.RUN_ANIMATION_BUTTON_CONTENT);
    IFVA.cell_to_animate = 6;
    IFVA.animation_done = false;
};



Insert_Function_Vector_Animation.prototype.run_animation=function() {
    if (!IFVA.animation_done) {
        //animate the 6th cell
        IFVA.animate(this);
        //now set the timer to animate the other two cells
        IFVA.animation_interval = setInterval(IFVA.animate, 1000, this);
    }
    else {
        IFVA.restart_animation(this);
        clearInterval(IFVA.animation_interval);
    }
};

Insert_Function_Vector_Animation.prototype.animate=function(button) {
    if (IFVA.cell_to_animate >= 4) {
        $("#filled_cell_d_" + IFVA.cell_to_animate).css("left", (IFVA.cell_to_animate + 1) * IFVA.cell_width + IFVA.index_position);
        $("#filled_cell_d_" + IFVA.cell_to_animate).css("background-color","#c4ffa0");
        IFVA.cell_to_animate--;
    }
    else {
        $("#filled_cell_M").css("top", 30 + "px"); //bring M up
        IFVA.animation_done = true;
        $(button).html(GENERAL.RESTART_ANIMATION_BUTTON_CONTENT);
    }
};




