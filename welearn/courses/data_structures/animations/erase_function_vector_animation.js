var EFVA = new Erase_Function_Vector_Animation();

function Erase_Function_Vector_Animation() {
    this.index_position = 30;
    this.cell_width = 22;
    this.cell_to_animate = 4;
    this.animation_done = false;
    this.animation_interval;
}

Erase_Function_Vector_Animation.prototype.start_animation=function(container) {

    for (var i = 0; i < 10; ++i) {
        var index_cell = "<div class='index_cell' id='e_index_cell_" + i + "'>" + i + "</div>";
        $("#" + container).append(index_cell);
        $("#e_index_cell_" + i).css("left", i * EFVA.cell_width + EFVA.index_position);
        if (i < 7) {
            var e_filled_cell_html = "<div class='filled_cell' id='e_filled_cell_" + i + "'>" + GENERAL.getChar(i) + "</div>";
            $("#" + container).append(e_filled_cell_html);
            $("#e_filled_cell_" + i).css("left", i * EFVA.cell_width + EFVA.index_position);
        }
        else {
            var cell_html = "<div class='cell' + id='e_cell_" + i + "'></div>";
            $("#" + container).append(cell_html);
            $("#e_cell_" + i).css("left", i * EFVA.cell_width + EFVA.index_position);
        }
    }

    for (var i = 6; i >= 4; --i) {
        var e_filled_cell_html = "<div class='filled_cell' id='e_filled_cell_d_" + i + "'>" + GENERAL.getChar(i) + "</div>";
        $("#" + container).append(e_filled_cell_html);
        $("#e_filled_cell_d_" + i).css("left", i * EFVA.cell_width + EFVA.index_position);
    }
    
    //make the last cell look gray because it will look out of range/excluded later on
    $("#e_filled_cell_6").css("background-color","rgb(240,240,240)"); 
    $("#e_filled_cell_6").css("color","gray"); 
    $("#e_filled_cell_6").css("border-color","gray"); 
    
    //color the cell you want to delete yellow
    $("#e_filled_cell_" + 3).css("background-color", "yellow");
};

Erase_Function_Vector_Animation.prototype.restart_animation=function(button) {
    for (var i = 6; i >= 4; --i){
        $("#e_filled_cell_d_" + i).css("left", i * EFVA.cell_width + EFVA.index_position);
        $("#e_filled_cell_d_" + i).css("background-color","#a2ddf6");
    }
    
    $(button).html(GENERAL.RUN_ANIMATION_BUTTON_CONTENT);
    EFVA.cell_to_animate = 4;
    EFVA.animation_done = false;
};



Erase_Function_Vector_Animation.prototype.run_animation=function() {
    if (!EFVA.animation_done) {
        //animate the 6th cell
        EFVA.animate(this);
        //now set the timer to animate the other two cells
        EFVA.animation_interval = setInterval(EFVA.animate, 1000, this);
    }
    else {
        EFVA.restart_animation(this);
        clearInterval(EFVA.animation_interval);
    }
};

Erase_Function_Vector_Animation.prototype.animate=function(button) {
    if (EFVA.cell_to_animate <= 6) {
        $("#e_filled_cell_d_" + EFVA.cell_to_animate).css("left", (EFVA.cell_to_animate - 1) * EFVA.cell_width + EFVA.index_position);
        $("#e_filled_cell_d_" + EFVA.cell_to_animate).css("background-color","#c4ffa0");
        EFVA.cell_to_animate++;
    }
    else {
        EFVA.animation_done = true;
        $(button).html(GENERAL.RESTART_ANIMATION_BUTTON_CONTENT);
    }
};






