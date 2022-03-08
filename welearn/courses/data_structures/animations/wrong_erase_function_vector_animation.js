var WEFVA = new Erase_Function_Vector_Animation();

function Erase_Function_Vector_Animation() {
    this.index_position = 30;
    this.cell_width = 22;
    this.cell_to_animate = 6;
    this.animation_done = false;
    this.animation_interval;
}

Erase_Function_Vector_Animation.prototype.start_animation = function (container) {

    for (var i = 0; i < 10; ++i) {
        var index_cell = "<div class='index_cell' id='w_e_index_cell_" + i + "'>" + i + "</div>";
        $("#" + container).append(index_cell);
        $("#w_e_index_cell_" + i).css("left", i * WEFVA.cell_width + WEFVA.index_position);
        if (i < 7) {
            var w_e_filled_cell_html = "<div class='filled_cell' id='w_e_filled_cell_" + i + "'>" + GENERAL.getChar(i) + "</div>";
            $("#" + container).append(w_e_filled_cell_html);
            $("#w_e_filled_cell_" + i).css("left", i * WEFVA.cell_width + WEFVA.index_position);
        }
        else {
            var cell_html = "<div class='cell' + id='w_e_cell_" + i + "'></div>";
            $("#" + container).append(cell_html);
            $("#w_e_cell_" + i).css("left", i * WEFVA.cell_width + WEFVA.index_position);
        }
    }

    /*  for (var i = 6; i >= 4; --i) {
     var w_e_filled_cell_html = "<div class='filled_cell' id='w_e_filled_cell_d_" + i + "'>" + GENERAL.getChar(i) + "</div>";
     $("#" + container).append(w_e_filled_cell_html);
     $("#w_e_filled_cell_d_" + i).css("left", i * WEFVA.cell_width + WEFVA.index_position);
     }*/

    //make the last cell look gray because it will look out of range/excluded later on
    $("#w_e_filled_cell_6").css("background-color", "rgb(240,240,240)");
    $("#w_e_filled_cell_6").css("color", "gray");
    $("#w_e_filled_cell_6").css("border-color", "gray");

    //color the cell you want to delete yellow
    $("#w_e_filled_cell_" + 3).css("background-color", "yellow");

    //add a duplicate last cell, this is the one to animate

    w_e_filled_cell_html = "<div class='filled_cell' id='w_e_filled_cell_d_" + 6 + "'>" + GENERAL.getChar(6) + "</div>";
    $("#" + container).append(w_e_filled_cell_html);
    $("#w_e_filled_cell_d_" + 6).css("left", 6 * WEFVA.cell_width + WEFVA.index_position);
};

Erase_Function_Vector_Animation.prototype.restart_animation = function (button) {
    for (var i = 5; i >= 4; --i) {
        $("#w_e_filled_cell_" + i).text(GENERAL.getChar(i));
        $("#w_e_filled_cell_" + i).css("background-color", "#a2ddf6");
    }

    //put the animated cell where it was
    $("#w_e_filled_cell_d_" + 6).css("left", 6 * WIFVA.cell_width + WIFVA.index_position);
    $("#w_e_filled_cell_d_" + 6).css("background-color", "#a2ddf6");

    $(button).html(GENERAL.RUN_ANIMATION_BUTTON_CONTENT);
    WEFVA.cell_to_animate = 6;
    WEFVA.animation_done = false;
};



Erase_Function_Vector_Animation.prototype.run_animation = function () {
    if (!WEFVA.animation_done) {
        //animate the 6th cell
        WEFVA.animate(this);
        //now set the timer to animate the other two cells
        WEFVA.animation_interval = setInterval(WEFVA.animate, 1000, this);
    }
    else {
        WEFVA.restart_animation(this);
        clearInterval(WEFVA.animation_interval);
    }
};

Erase_Function_Vector_Animation.prototype.animate = function (button) {
    if (WEFVA.cell_to_animate >= 4) {
        $("#w_e_filled_cell_d_" + 6).css("left", (WEFVA.cell_to_animate - 1) * WEFVA.cell_width + WEFVA.index_position);
        $("#w_e_filled_cell_d_" + 6).css("background-color", "#c4ffa0");
        WEFVA.cell_to_animate--;
        if (WEFVA.cell_to_animate < 5) {
            $("#w_e_filled_cell_" + (WEFVA.cell_to_animate + 1)).css("background-color", "#c4ffa0");
            $("#w_e_filled_cell_" + (WEFVA.cell_to_animate + 1)).text(GENERAL.getChar(6));
        }

    }
    else {
        WEFVA.animation_done = true;
        $(button).html(GENERAL.RESTART_ANIMATION_BUTTON_CONTENT);
    }
};









