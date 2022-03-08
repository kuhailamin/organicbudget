var PBFVA = new Push_Back_Function_Vector_Animation();

function Push_Back_Function_Vector_Animation() {
    this.index_position = 30;
    this.cell_width = 22;
    this.cell_to_animate = 3;
    this.animation_done = false;
    this.animation_interval;
}

Push_Back_Function_Vector_Animation.prototype.start_animation = function (container) {
    
    //add capacity div
    
    var capacity_html="<div class='capacity_wrapper'><div id='capacity_line'></div><span id='capacity_num'>capacity=5</span></div>"
    $("#" + container).append(capacity_html);
    $(".capacity_wrapper").css({position:"absolute",top:"75px","left":"30px","width":PBFVA.cell_width*5+"px","text-align":"center"});
    $("#capacity_line").css({width:"100%","border-bottom":"1px solid black","margin-top":"-5px"});
    $("#capacity_num").css({"display":"inline-block","text-align":"center","margin-top":"-12px","background-color":"white","font-size":"12px"});

    for (var i = 0; i < 5; ++i) {
        var index_cell = "<div class='index_cell' id='pb_index_cell_" + i + "'>" + i + "</div>";
        $("#" + container).append(index_cell);
        $("#pb_index_cell_" + i).css("left", i * PBFVA.cell_width + PBFVA.index_position);
        if (i < 3) {
            var pb_filled_cell_html = "<div class='filled_cell' id='pb_filled_cell_" + i + "'>" + GENERAL.getChar(i) + "</div>";
            $("#" + container).append(pb_filled_cell_html);
            $("#pb_filled_cell_" + i).css("left", i * PBFVA.cell_width + PBFVA.index_position);
        }
        else {
            var cell_html = "<div class='cell' + id='pb_cell_" + i + "'></div>";
            $("#" + container).append(cell_html);
            $("#pb_cell_" + i).css("left", i * PBFVA.cell_width + PBFVA.index_position);
        }
    }

    for (var i = 5; i < 10; ++i) {
        
        var index_cell = "<div class='index_cell additional_cell' id='pb_index_cell_" + i + "'>" + i + "</div>";
        $("#" + container).append(index_cell);
        $("#pb_index_cell_" + i).css("left", i * PBFVA.cell_width + PBFVA.index_position);
        $("#pb_index_cell_" + i).hide();
        
        var cell_html = "<div class='cell additional_cell' + id='pb_cell_" + i + "'></div>";
        $("#" + container).append(cell_html);
        $("#pb_cell_" + i).css("left", i * PBFVA.cell_width + PBFVA.index_position);
        $("#pb_cell_" + i).hide();

    }

//add three items at the end
    for (var i = 3; i < 6; ++i) {
        var pb_filled_cell_html = "<div class='filled_cell cell_to_add' id='pb_filled_cell_d_" + i + "'>" + GENERAL.getChar(i) + "</div>";
        $("#" + container).append(pb_filled_cell_html);
        $("#pb_filled_cell_d_" + i).css("left", i * PBFVA.cell_width + PBFVA.index_position);
        $("#pb_filled_cell_d_" + i).css("background-color","rgb(196, 255, 160)");
        $("#pb_filled_cell_d_" + i).hide();
    }
};

Push_Back_Function_Vector_Animation.prototype.restart_animation = function (button) {
    
    $(".additional_cell").fadeOut("slow");
    $(".cell_to_add").fadeOut("slow");
    $(".capacity_wrapper").css({"width":PBFVA.cell_width*5+"px"});
    $("#capacity_num").text("capacity=5");    
    $(button).html(GENERAL.RUN_ANIMATION_BUTTON_CONTENT);
    PBFVA.cell_to_animate = 3;
    PBFVA.animation_done = false;
};



Push_Back_Function_Vector_Animation.prototype.run_animation = function () {
    if (!PBFVA.animation_done) {
        //animate the 6th cell
        PBFVA.animate(this);
        //now set the timer to animate the other two cells
        PBFVA.animation_interval = setInterval(PBFVA.animate, 1000, this);
    }
    else {
        PBFVA.restart_animation(this);
        clearInterval(PBFVA.animation_interval);
    }
};

Push_Back_Function_Vector_Animation.prototype.animate = function (button) {
    if (PBFVA.cell_to_animate <= 4) {
        $("#pb_filled_cell_d_" + PBFVA.cell_to_animate).fadeIn("slow");
        PBFVA.cell_to_animate++;
    }
    else if(PBFVA.cell_to_animate==5){
        $(".additional_cell").fadeIn("slow");
        $(".capacity_wrapper").css({"width":PBFVA.cell_width*10+"px"});
        $("#capacity_num").text("capacity=10");
        PBFVA.cell_to_animate++;
    }
    else {
        $("#pb_filled_cell_d_5").fadeIn("slow");
        PBFVA.animation_done = true;
        $(button).html(GENERAL.RESTART_ANIMATION_BUTTON_CONTENT);
    }
};







