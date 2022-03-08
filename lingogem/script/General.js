var GENERAL = new General(); //GENERAL object

function General() {
    this.COURSES_POPULATED_EVENT = "course_populated_event";
    this.NUM_MILLISECONDS_IN_DAY = 1000 * 60 * 60 * 24;
    this.MENU_ICON = "#menu_icon";
    this.LINKS_DIV = "#links";
    this.RUN_BUTTON_CLASS = ".run_button";
    this.RUN_BUTTON_CONTENT="&#9658; Run";
    this.RUN_ANIMATION_BUTTON_CLASS = ".run_animation_button";
    this.RUN_ANIMATION_BUTTON_CONTENT="&#9658; Run Animation";
    this.RESTART_ANIMATION_BUTTON_CONTENT="&#8635; Restart Animation";
    this.LESSONS_REQUEST = "lessons";
    this.LESSON_NAVIGATION_CLASS_DIV = ".lesson_navigation";
    this.LESSON_NAVIGATION_SELECTED_CLASS_NAME = "lesson_navigation_selected";
    this.LESSON_NAVIGATION_TEMPLATE = "#lesson-navigation-template";
    this.LESSION_NAVIGATION_DIV_ID = "#lesson_navigation_";
    this.CLOSE_LESSONS_BUTTON_ID = "#close-lessons";
    this.SHOW_LESSONS_BUTTON_ID = "#show-lessons";
    this.COURSE_HOME_BUTTON_ID = "#course-home";
    this.NEXT_PREV_CLASS_DIV = ".next_prev_button";
    this.NEXT_LESSON_ID = "#next-button";
    this.PREV_LESSON_ID = "#prev-button";
    this.INACTIVE_CLASS_NAME = "inactive";
    this.COURSE_TITLE_SPAN_ID = "#course_title";
    this.COURSE_CONTENT_DIV_ID = "#course_content";
    this.COURSE_NAVIGATION_WIDTH = 260;
    this.LESSON_INDEX_PROP = "data-lesson-index";
    this.COURSE_ID_PROP = "data-course-id";
    this.LESSON_ID_ATTR = "lesson_id";
    this.MAJOR_LESSON_ATTR = "major_lesson";
    this.LESSON_LINK_ATTR = "lesson_link";
    this.COURSE_ID_PARAM = "course_id";
    this.LESSON_INDEX_PARAM = "lesson_index";
    this.COURSE_NAME_PARAM = "course_name";
    this.COURSE_NAVIGATION_DIV_ID = "#course_navigation";
    this.POPULAR_COURSES_REQUEST = "popular_courses";
    this.POPULAR_CATEGORIES_REQUEST = "popular_categories";
    this.COURSES_FOR_CATEGORY = "courses_for_category";
    this.CATALOG_REQUEST = "catalog";
    this.ALL_COURSE_NAMES_REQUEST = "all_course_names";
    this.CATEGORY_TEMPLATE = "#category-template";
    this.POPULAR_CATEGORY_TEMPLATE = "#popular-category-template";
    this.CATEGORIES_DIV_ID = "#categories";
    this.POPULAR_CATEGORIES_DIV_ID = "#popular_categories";
    this.ALL_COURSES_AVAILABLE_ID = "#all_courses_navigation";
    this.COURSES_DIV_ID = "#courses";
    this.COURSE_TEMPLATE = "#course-template";
    this.COURSE_DIV_ID = "#course_";
    this.COURSE_SEARCH_SUGGESTION_TEMPLATE = "#course-search-suggestion-template";
    this.CATEGORY_DIV_ID = "#category_";
    this.PROGRAMMING_LANGUAGE_TEMPLATE = "#programming-language-template";
    this.COURSE_AVAILABLE_IN_ROW_ID = "#course_available_in_row_";
    this.COURSE_AVAILABLE_IN_CELL_ID = "#course_available_in_cell_";
    this.LOADING_ICON_ID = "#loading_icon";
    this.COURSE_VIEWS_PROP = "course_views";
    this.CATEGORY_PARAM = "category";
    this.COURSE_PARAM = "course";
    this.SEE_ALL_BUTTON_ID = "#see_all_";
    this.DATETIME_LS = "datetime";
    this.COURSES_LS = "courses";
    this.COURSE_SEARCH_BOX_ID = "#course_search_box";
    this.COURSE_SEARCH_BUTTON_ID = "#course_search_button";
    this.COURSE_SUGGESTIONS_WRAPPER = "#course_suggestions_wrapper";
    this.COURSE_SUGGESTION_CLASS = ".search_suggestion";
    this.COURSE_HIGHLIGHTED_SUGGESTION_CLASS = ".search_suggestion_highlighted";
    this.COURSE_HIGHLIGHTED_SUGGESTION_NAME = "search_suggestion_highlighted";
    this.COURSE_NAME_PROP = "data-course-name";
    this.LOADING_ICON_TEMPLATE_ID="#loading-icon-template";
    this.MAX_COURSE_SEARCH_SUGGESTIONS = 6;


    /** attribtues **/
    this.COURSE_ID_ATTR = "course_id";
    this.COURSE_NAME_ATTR = "course_name";
    this.COURSE_DESCRIPTION_ATTR = "course_description";
    this.COURSE_KEYWORDS_ATTR = "course_keywords";
    this.COURSE_LINK_ATTR = "course_link";
    this.COURSE_LESSONS_ATTR = "lessons";
    this.COURSE_SEARCH_SUGGESTION_ID = "#course_search_suggestion_";
    this.COURSES_DB = null;

    this.DOWN_KEY = 40;
    this.UP_KEY = 38;
    this.RIGHT_KEY = 39;
    this.LEFT_KEY = 37;
    this.ENTER_KEY = 13;
    this.isMobile = {
        Android: function () {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function () {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function () {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function () {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function () {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function () {
            return (GENERAL.isMobile.Android() || GENERAL.isMobile.BlackBerry() || GENERAL.isMobile.iOS() || GENERAL.isMobile.Opera() || GENERAL.isMobile.Windows());
        }
    };
}



General.prototype.get_lessons_for_course = function () {
    var course_id = decodeURI(GENERAL.get_page_param(GENERAL.COURSE_ID_PARAM, window.location.href));
    var lesson_index = decodeURI(GENERAL.get_page_param(GENERAL.LESSON_INDEX_PARAM, window.location.href));
    $("body").on(GENERAL.COURSES_POPULATED_EVENT, function () {
        var course_info = GENERAL.find_course_info_by_id(course_id);
        $(GENERAL.COURSE_TITLE_SPAN_ID).html(course_info[0]);
        var lessons = course_info[2];
        if (lessons.length > 1) {//lessons have already been retreived from before
            GENERAL.show_lessons(lessons, course_info[1]);
            GENERAL.select_lesson(lesson_index, course_id, course_info[1]);
            GENERAL.course_basic_nav_event_handler(lesson_index, lessons.length, course_id);
        }
        else {
            $.ajax({
                method: constants.POST_REQUEST,
                url: constants.CONTROLLER_PATH,
                dataType: "json",
                data: {type: GENERAL.LESSONS_REQUEST, course_id: course_id},
                success: function (data) {
                    GENERAL.show_lessons(data, course_info[1]);
                    GENERAL.update_course_lessons(course_id, data);
                    GENERAL.select_lesson(lesson_index, course_id, course_info[1]);
                    GENERAL.course_basic_nav_event_handler(lesson_index, data.length, course_id);
                }
            });
        }
        $("body").off(GENERAL.COURSES_POPULATED_EVENT);
    });
    GENERAL.refresh_courses_db();
    $(GENERAL.SHOW_LESSONS_BUTTON_ID).on("click", function () {
        GENERAL.show_lesson_navigation();
        $(GENERAL.SHOW_LESSONS_BUTTON_ID).hide();
        $(GENERAL.CLOSE_LESSONS_BUTTON_ID).show();
    });

    $(GENERAL.CLOSE_LESSONS_BUTTON_ID).on("click", function () {
        GENERAL.hide_lesson_navigation();
        $(GENERAL.SHOW_LESSONS_BUTTON_ID).show();
        $(GENERAL.CLOSE_LESSONS_BUTTON_ID).hide();
    });
};

General.prototype.show_lesson_navigation = function () {
    var scrollbar_width = GENERAL.isMobile.any() ? 10 : 30;
    var course_content_width = window.innerWidth - GENERAL.COURSE_NAVIGATION_WIDTH - scrollbar_width;
    $(GENERAL.COURSE_NAVIGATION_DIV_ID).css("left", "0px");
    $(GENERAL.COURSE_CONTENT_DIV_ID).css("left", GENERAL.COURSE_NAVIGATION_WIDTH + "px");
    $(GENERAL.COURSE_CONTENT_DIV_ID).css("width", course_content_width + "px");
    GENERAL.set_video_dimensions();
};

General.prototype.hide_lesson_navigation = function () {
    var scrollbar_width = GENERAL.isMobile.any() ? 10 : 30;
    var course_content_width = window.innerWidth - scrollbar_width;

    $(GENERAL.COURSE_CONTENT_DIV_ID).css("left", "0px");
    $(GENERAL.COURSE_NAVIGATION_DIV_ID).css("left", "-" + GENERAL.COURSE_NAVIGATION_WIDTH + "px");
    $(GENERAL.COURSE_CONTENT_DIV_ID).css("width", course_content_width + "px");
    GENERAL.set_video_dimensions();
};

General.prototype.set_video_dimensions = function () {
    var scrollbar_width = GENERAL.isMobile.any() ? 10 : 30;
    var course_content_width = window.innerWidth - scrollbar_width;
    var original_video_width=$("iframe").width();
    var original_video_height=$("iframe").height();
    var video_width=500;
    if(course_content_width<=800 && course_content_width>=500)
        video_width=course_content_width/1.5;
    else if(course_content_width<500)
        video_width=course_content_width-40;
    
    var video_height=original_video_height*video_width/original_video_width;
    $("iframe").css("width",video_width+"px");
    $("iframe").css("height",video_height+"px"); 
};

General.prototype.show_lessons = function (data, course_link) {
    var html = GENERAL.get_html_data(GENERAL.LESSON_NAVIGATION_TEMPLATE, data);
    $(GENERAL.COURSE_NAVIGATION_DIV_ID).html(html);
    for (var i = 0; i < data.length; ++i) {
        var id = data[i][GENERAL.LESSON_ID_ATTR];
        var lesson_navigation_div_html = $(GENERAL.LESSION_NAVIGATION_DIV_ID + id).html();
        var major_lesson = data[i][GENERAL.MAJOR_LESSON_ATTR];
        var course_id = data[i][GENERAL.COURSE_ID_ATTR];
        if (major_lesson == 1)
            $(GENERAL.LESSION_NAVIGATION_DIV_ID + id).html("<b>" + lesson_navigation_div_html + "</b>");
        GENERAL.on_lesson_navigation_clicked(id, course_id);
    }
};

General.prototype.course_basic_nav_event_handler = function (lesson_index, lesson_size, course_id) {
    var index = parseInt(lesson_index);
    $(GENERAL.COURSE_HOME_BUTTON_ID).on("click", function () {
        window.location = constants.ROOT_PATH + "courses/?course_id=" + course_id + "&lesson_index=0";
    });
    if (index == 0) {
        $(GENERAL.PREV_LESSON_ID).addClass(GENERAL.INACTIVE_CLASS_NAME);
    }
    else {
        $(GENERAL.PREV_LESSON_ID).on("click", function () {
            window.location = constants.ROOT_PATH + "courses/?course_id=" + course_id + "&lesson_index=" + (index - 1);
        });
    }
    if (index + 1 == lesson_size) {
        $(GENERAL.NEXT_LESSON_ID).addClass(GENERAL.INACTIVE_CLASS_NAME);
    }
    else {
        $(GENERAL.NEXT_LESSON_ID).on("click", function () {
            window.location = constants.ROOT_PATH + "courses/?course_id=" + course_id + "&lesson_index=" + (index + 1);
        });
    }
};

General.prototype.select_lesson = function (lesson_index, course_id, course_link) {
    var lesson_index_int = parseInt(lesson_index);
    var lesson = GENERAL.find_lesson_info_by_index(course_id, lesson_index_int);
    var lesson_id = lesson[GENERAL.LESSON_ID_ATTR];
    var lesson_link = lesson[GENERAL.LESSON_LINK_ATTR];

    $(GENERAL.LESSON_NAVIGATION_CLASS_DIV).removeClass(GENERAL.LESSON_NAVIGATION_SELECTED_CLASS_NAME);
    $(GENERAL.LESSION_NAVIGATION_DIV_ID + lesson_id).addClass(GENERAL.LESSON_NAVIGATION_SELECTED_CLASS_NAME);

    var lesson_navigation_position = $(GENERAL.LESSION_NAVIGATION_DIV_ID + lesson_id).position().top;
    $(GENERAL.COURSE_NAVIGATION_DIV_ID).animate({scrollTop: lesson_navigation_position});
    
    GENERAL.refresh_content_screen();
    $(window).resize(GENERAL.refresh_content_screen);
    var loading_icon_html=$(GENERAL.LOADING_ICON_TEMPLATE_ID).html();
    $(GENERAL.COURSE_CONTENT_DIV_ID).html(loading_icon_html);
    $.ajax({
        url: constants.ROOT_PATH + "/courses/" + course_link + "/lessons/" + lesson_link + ".html",
        data: 'foo',
        success: function (data) {
            $(GENERAL.COURSE_CONTENT_DIV_ID).html(data); 
            $(GENERAL.RUN_BUTTON_CLASS).html(GENERAL.RUN_BUTTON_CONTENT);
            $(GENERAL.RUN_ANIMATION_BUTTON_CLASS).html(GENERAL.RUN_ANIMATION_BUTTON_CONTENT);
            $(GENERAL.RUN_BUTTON_CLASS).attr("target","_blank");
            $(GENERAL.COURSE_CONTENT_DIV_ID).children().last().css("margin-bottom", "100px");
            GENERAL.update_image_sources(course_link); 
            GENERAL.add_script(course_link);
            GENERAL.refresh_content_screen();    
        },
        cache: false
    });
};


   
General.prototype.update_image_sources = function (course_link) {
    //var images=$(GENERAL.COURSE_CONTENT_DIV_ID).children("img");
    $.each( $(GENERAL.COURSE_CONTENT_DIV_ID+" img"), function() {
        var old_src=$(this).attr('src');
        var new_src=constants.ROOT_PATH+ "courses/" + course_link+"/images/"+old_src;
        $(this).attr("src",new_src);
    });
};

//add script for showing the code nicely
General.prototype.add_script=function(course_link){
    Prism.highlightAll(); /** handles code **/
    MathJax.Hub.Typeset(); /**handles math equations**/
    /** this script handles graphs and charts**/
    $.each( $(GENERAL.COURSE_CONTENT_DIV_ID+" .graph"), function() {
        var script_name=$(this).attr('data-script');
        $.getScript(constants.ROOT_PATH+"courses/" + course_link+"/chart_script/"+script_name+".js");
    });    
    /** this script handles animations**/
    $.each( $(GENERAL.COURSE_CONTENT_DIV_ID+" .animation"), function() {
        var id=[$(this).attr('id')];
        var script_name=$(this).attr('data-script');
        var style_name=$(this).attr("data-style");
        var animation_object_name=$(this).attr("data-script-object-name");
        var function_name=$(this).attr('data-function-name');
        var script_path=constants.ROOT_PATH+"courses/" + course_link+"/animations/"+script_name+".js";
        var css_path=constants.ROOT_PATH+"courses/" + course_link+"/animations/"+style_name+".css";
        $('head').append('<link rel="stylesheet" type="text/css" href="'+css_path+'">');

    $.getScript( script_path, function() { 
        // find object
        var object=window[animation_object_name];
        var fn = object[function_name];
        // is object a function?
        if (typeof fn === "function") fn.apply(null, id);
    });
    }           
            );  
    
    /** handle what happens when run animation button gets clicked **/
    
    $(GENERAL.RUN_ANIMATION_BUTTON_CLASS).click(function(){
        var animation_object_name=$(this).parent().attr("data-script-object-name");
        var function_name=$(this).attr("data-function-name");
        var object=window[animation_object_name];
        var fn = object[function_name];
        if (typeof fn === "function") fn.apply(this);    
    });
    
    /** handle task solutions **/
    $(GENERAL.COURSE_CONTENT_DIV_ID+" .task_solution").on("click",function() {
        var id=$(this).attr('id');
        var task_solution_detail=$("#"+id+"_detail");
        var display=task_solution_detail.css("display");
        if(display=="none"){
            task_solution_detail.show();
            $(this).text("Hide Solution");
        }
        else{
            task_solution_detail.hide();
            $(this).text("Show Solution");
        }
    });    
};

General.prototype.refresh_content_screen = function () {
    if ($(GENERAL.CLOSE_LESSONS_BUTTON_ID).css("display") == "none") {
        GENERAL.hide_lesson_navigation();        
    }
    else {
        GENERAL.show_lesson_navigation();
    }
};

General.prototype.on_lesson_navigation_clicked = function (lesson_id, course_id) {
    $(GENERAL.LESSION_NAVIGATION_DIV_ID + lesson_id).on("click", function () {
        var lesson_index = $(GENERAL.LESSION_NAVIGATION_DIV_ID + lesson_id).attr(GENERAL.LESSON_INDEX_PROP);
        window.location = constants.ROOT_PATH + "courses/?course_id=" + course_id + "&lesson_index=" + lesson_index;
    });
};

General.prototype.init = function () {
    $(GENERAL.MENU_ICON).on("click", function (e) {
        e.stopPropagation();
        var display = $(GENERAL.LINKS_DIV).css("display");
        if (display === "block")
            $(GENERAL.LINKS_DIV).hide();
        else
            $(GENERAL.LINKS_DIV).show();
    });

    $("html").on("click", function () {
        $(GENERAL.COURSE_SUGGESTIONS_WRAPPER).hide();
    });

    GENERAL.refresh_courses_db();

    $.ajax({
        method: constants.POST_REQUEST,
        url: constants.CONTROLLER_PATH,
        dataType: "json",
        data: {type: GENERAL.POPULAR_CATEGORIES_REQUEST},
        success: function (data) {
            GENERAL.show_popular_categories(data);
        }
    });

    $.ajax({
        method: constants.POST_REQUEST,
        url: constants.CONTROLLER_PATH,
        dataType: "json",
        data: {type: GENERAL.POPULAR_COURSES_REQUEST},
        success: function (data) {
            $(GENERAL.LOADING_ICON_ID).hide();
            GENERAL.show_popular_courses(data);
        }
    });

    $(GENERAL.COURSE_SEARCH_BOX_ID).on("keyup", function (e) {
        if (e.which == GENERAL.DOWN_KEY || e.which == GENERAL.UP_KEY || e.which == GENERAL.ENTER_KEY)//if the arrow keys/ENTER are typed, ignore
            return;
        var text = $(GENERAL.COURSE_SEARCH_BOX_ID).val();
        GENERAL.find_courses(text, GENERAL.COURSE_SUGGESTIONS_WRAPPER, GENERAL.COURSE_SEARCH_SUGGESTION_TEMPLATE);
    });

    $(GENERAL.COURSE_SEARCH_BOX_ID).on("keydown", function (e) {
        GENERAL.on_course_search_box_arrow_typed(GENERAL.COURSE_SUGGESTIONS_WRAPPER, e);
    });


    $(GENERAL.COURSE_SEARCH_BUTTON_ID).on("click", function () {
        var text = $(GENERAL.COURSE_SEARCH_BOX_ID).val();
        if (text === null || text.trim() === "" || text.trim().length === 0)
            return;
        window.location = constants.ROOT_PATH + "course_search/?course=" + text;
    });
};

General.prototype.course_search_init = function () {
    var text = decodeURI(GENERAL.get_page_param(GENERAL.COURSE_PARAM, window.location.href));
    $(GENERAL.COURSE_SEARCH_BOX_ID).val(text);
    GENERAL.search_for_courses(text);
    $(GENERAL.COURSE_SEARCH_BUTTON_ID).off("click");
    $(GENERAL.COURSE_SEARCH_BUTTON_ID).on("click", function (e) {
        $(GENERAL.COURSE_SUGGESTIONS_WRAPPER).hide();
        e.stopPropagation();
        var text = $(GENERAL.COURSE_SEARCH_BOX_ID).val();
        GENERAL.search_for_courses(text);
    });
    $(GENERAL.COURSE_SEARCH_BOX_ID).off("keydown");
    $(GENERAL.COURSE_SEARCH_BOX_ID).off("keyup");
    $(GENERAL.COURSE_SEARCH_BOX_ID).on("keyup", function (e) {
        if (e.which == GENERAL.DOWN_KEY || e.which == GENERAL.UP_KEY || e.which == GENERAL.ENTER_KEY)//if the arrow keys/ENTER are typed, ignore
            return;
        var text = $(GENERAL.COURSE_SEARCH_BOX_ID).val();
        GENERAL.find_courses(text, GENERAL.COURSE_SUGGESTIONS_WRAPPER, GENERAL.COURSE_SEARCH_SUGGESTION_TEMPLATE);
    });
    $(GENERAL.COURSE_SEARCH_BOX_ID).on("keydown", function (e) {
        GENERAL.on_course_search_box_arrow_typed(GENERAL.COURSE_SUGGESTIONS_WRAPPER, e);
    });
};

General.prototype.on_course_search_box_arrow_typed = function (div, e) {
    var course_suggestions_size = $(GENERAL.COURSE_SUGGESTION_CLASS).size();
    if (course_suggestions_size === 0) //there are no suggestions at all
        return;
    var selected_size = $(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_CLASS).size();
    if (e.which == GENERAL.DOWN_KEY) { //down key
        if (selected_size == 0) { //no item is selected, select the first one
            $(GENERAL.COURSE_SUGGESTION_CLASS).eq(0).addClass(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_NAME);
            return;
        }
        else {
            var index = $(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_CLASS).index();
            if (index == course_suggestions_size - 1)
                index = -1;
            $(GENERAL.COURSE_SUGGESTION_CLASS).removeClass(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_NAME);
            $(GENERAL.COURSE_SUGGESTION_CLASS).eq(index + 1).addClass(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_NAME);
        }
    }
    else if (e.which == GENERAL.UP_KEY) {
        if (selected_size == 0) {
            $(GENERAL.COURSE_SUGGESTION_CLASS).eq(course_suggestions_size - 1).addClass(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_NAME);
            return;
        }
        else {
            var index = $(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_CLASS).index();
            if (index == 0)
                index = course_suggestions_size;
            $(GENERAL.COURSE_SUGGESTION_CLASS).removeClass(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_NAME);
            $(GENERAL.COURSE_SUGGESTION_CLASS).eq(index - 1).addClass(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_NAME);

        }
    }
    else if (e.which == GENERAL.ENTER_KEY) {
        //if the suggestions aren't shown up, then we want this to search
        if ($(div).css('display') == "none") {
            $(GENERAL.COURSE_SEARCH_BUTTON_ID).trigger("click");
            return;
        }
        if (selected_size == 0) //nothing is selected, ignore
            return;
        e.stopPropagation();
        var course_name = $(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_CLASS).attr(GENERAL.COURSE_NAME_PROP);
        $(GENERAL.COURSE_SEARCH_BOX_ID).val(course_name);
        $(div).hide();
    }
};

General.prototype.is_course_db_old = function () {
    if (localStorage.getItem(GENERAL.DATETIME_LS) == null)
        return true;
    var last_date_time = Date.parse(localStorage.getItem(GENERAL.DATETIME_LS));
    var current_date_time = new Date();
    var difference_in_ms = current_date_time.getTime() - last_date_time;
    var difference_in_days = difference_in_ms / GENERAL.NUM_MILLISECONDS_IN_DAY;
    return difference_in_days > 1;
};

General.prototype.refresh_courses_db = function () {
    if (localStorage.getItem(GENERAL.COURSES_LS) == null || General.prototype.is_course_db_old())
        GENERAL.get_all_courses();
    else
        $("body").trigger(GENERAL.COURSES_POPULATED_EVENT);
};


General.prototype.search_for_courses = function (text) {
    $(GENERAL.LOADING_ICON_ID).show();
    text = text === null || text.length === 0 || text.trim().length === 0 ? "" : text.toLowerCase().trim();
    $(GENERAL.COURSES_DIV_ID).html("");
    $(GENERAL.LOADING_ICON_ID).hide();
    $("body").on(GENERAL.COURSES_POPULATED_EVENT, function () {
        var courses_db = JSON.parse(localStorage.getItem(GENERAL.COURSES_LS));
        for (var i = 0; i < courses_db.length; ++i) {
            var course_name = courses_db[i][GENERAL.COURSE_NAME_ATTR];
            var course_description = courses_db[i][GENERAL.COURSE_DESCRIPTION_ATTR];
            var course_keywords = courses_db[i][GENERAL.COURSE_KEYWORDS_ATTR];
            if (GENERAL.search_word_by_word(text, course_name) || GENERAL.search_word_by_word(text, course_description) || GENERAL.search_word_by_word(text, course_keywords)) {
                GENERAL.show_course(null, courses_db[i]);
            }
        }
        $("body").off(GENERAL.COURSES_POPULATED_EVENT);
    });
    GENERAL.refresh_courses_db();
};

General.prototype.find_course_info_by_id = function (course_id) {
    var course_info = [];
    var courses_db = JSON.parse(localStorage.getItem(GENERAL.COURSES_LS));
    for (var i = 0; i < courses_db.length; ++i) {
        if (courses_db[i][GENERAL.COURSE_ID_ATTR] == course_id) {
            course_info.push(courses_db[i][GENERAL.COURSE_NAME_ATTR]);
            course_info.push(courses_db[i][GENERAL.COURSE_LINK_ATTR]);
            course_info.push(courses_db[i][GENERAL.COURSE_LESSONS_ATTR]);
            return course_info;
        }
    }
    return course_info;
};

General.prototype.find_lesson_info_by_index = function (course_id, lesson_index) {
    var course_info = GENERAL.find_course_info_by_id(course_id);
    if (lesson_index < 0 || lesson_index >= course_info[2].length)
        lesson_index = 0;
    return course_info[2][lesson_index];
};

General.prototype.update_course_lessons = function (course_id, lessons) {
    var course_info = [];
    var courses_db = JSON.parse(localStorage.getItem(GENERAL.COURSES_LS));
    for (var i = 0; i < courses_db.length; ++i) {
        if (courses_db[i][GENERAL.COURSE_ID_ATTR] == course_id) {
            courses_db[i][GENERAL.COURSE_LESSONS_ATTR] = lessons;
            localStorage.removeItem(GENERAL.COURSES_LS);
            localStorage.setItem(GENERAL.COURSES_LS, JSON.stringify(courses_db));
        }
    }
};

General.prototype.find_courses = function (text, div, template) {
    text = text === null || text.length === 0 || text.trim().length === 0 ? "" : text.toLowerCase().trim();
    $(div).show();
    $(div).html(""); //empty search suggestions
    GENERAL.refresh_courses_db();
    var courses_db = JSON.parse(localStorage.getItem(GENERAL.COURSES_LS));
    //var courses_db=GENERAL.COURSES_DB;
    var hit_count = 0;
    for (var i = 0; i < courses_db.length; ++i) {
        var course_id = courses_db[i][GENERAL.COURSE_ID_ATTR];
        var course_name = courses_db[i][GENERAL.COURSE_NAME_ATTR];
        var course_description = courses_db[i][GENERAL.COURSE_DESCRIPTION_ATTR];
        var course_keywords = courses_db[i][GENERAL.COURSE_KEYWORDS_ATTR];
        if (GENERAL.search_word_by_word(text, course_name) || GENERAL.search_word_by_word(text, course_description) || GENERAL.search_word_by_word(text, course_keywords)) {
            var highlighted_course_name = GENERAL.highlight_word_by_word(course_name, text);
            var course_object = {course_id: course_id, course_name: course_name, highlighted_course_name: highlighted_course_name};
            var html = GENERAL.get_html_data(template, course_object);
            $(div).append(html);
            GENERAL.bind_course_suggestion_events(course_id, div);
            ++hit_count;
            if (hit_count === GENERAL.MAX_COURSE_SEARCH_SUGGESTIONS)
                break; //don't bring more search matches

        }
    }
};



General.prototype.search_word_by_word = function (text, search_text) {
    if (search_text !== null && text !== null) {
        search_text = search_text.toLowerCase().trim();
        text = text.toLowerCase().trim();
        var words = text.match(/\S+/g);
        if (words.length === 0)
            return false;
        for (var i = 0; i < words.length; ++i) {
            if (search_text.indexOf(words[i]) === -1)
                return false;
        }
        return true;
    }
    return false;
};

General.prototype.bind_course_suggestion_events = function (course_id, suggestions_div) {
    $(GENERAL.COURSE_SEARCH_SUGGESTION_ID + course_id).on("click", function (e) {
        e.stopPropagation();
        var course_name = $(GENERAL.COURSE_SEARCH_SUGGESTION_ID + course_id).attr(GENERAL.COURSE_NAME_PROP);
        $(GENERAL.COURSE_SEARCH_BOX_ID).val(course_name);
        $(suggestions_div).hide();
    });
};

General.prototype.get_all_courses = function () {
    $.ajax({
        method: constants.POST_REQUEST,
        url: constants.CONTROLLER_PATH,
        dataType: "text",
        data: {type: GENERAL.ALL_COURSE_NAMES_REQUEST},
        success: function (data) {
            localStorage.removeItem(GENERAL.COURSES_LS);
            localStorage.setItem(GENERAL.COURSES_LS, data);
            localStorage.setItem(GENERAL.DATETIME_LS, new Date());
            $("body").trigger(GENERAL.COURSES_POPULATED_EVENT);
        }
    });
};

General.prototype.get_courses_for_category = function () {
    var category_id = GENERAL.get_page_param(GENERAL.CATEGORY_PARAM, window.location.href);
    $.ajax({
        method: constants.POST_REQUEST,
        url: constants.CONTROLLER_PATH,
        dataType: "json",
        data: {type: GENERAL.COURSES_FOR_CATEGORY, category_id: category_id},
        success: function (data) {
            $(GENERAL.LOADING_ICON_ID).hide();
            GENERAL.show_courses(data);
        }
    });
    GENERAL.bind_search_box_events();
};

General.prototype.get_catalog_categories = function () {
    $.ajax({
        method: constants.POST_REQUEST,
        url: constants.CONTROLLER_PATH,
        dataType: "json",
        data: {type: GENERAL.CATALOG_REQUEST},
        success: function (data) {
            $(GENERAL.LOADING_ICON_ID).hide();
            GENERAL.show_categories(data);
        }
    });

    GENERAL.bind_search_box_events();
};

General.prototype.bind_search_box_events = function () {
    $(GENERAL.COURSE_SEARCH_BOX_ID).off("click");
    $(GENERAL.COURSE_SEARCH_BOX_ID).off("keydown");
    $(GENERAL.COURSE_SEARCH_BOX_ID).off("keyup");

    $(GENERAL.COURSE_SEARCH_BOX_ID).on("keyup", function (e) {
        if (e.which == GENERAL.DOWN_KEY || e.which == GENERAL.UP_KEY || e.which == GENERAL.ENTER_KEY)//if the arrow keys/ENTER are typed, ignore
            return;
        var text = $(GENERAL.COURSE_SEARCH_BOX_ID).val();
        GENERAL.find_courses(text, GENERAL.COURSE_SUGGESTIONS_WRAPPER, GENERAL.COURSE_SEARCH_SUGGESTION_TEMPLATE);
    });



    $(GENERAL.COURSE_SEARCH_BOX_ID).on("keydown", function (e) {
        GENERAL.on_course_search_box_arrow_typed(GENERAL.COURSE_SUGGESTIONS_WRAPPER, e);
    });

    $(GENERAL.COURSE_SEARCH_BUTTON_ID).off("click");

    $(GENERAL.COURSE_SEARCH_BUTTON_ID).on("click", function () {
        var text = $(GENERAL.COURSE_SEARCH_BOX_ID).val();
        if (text === null || text.trim() === "" || text.trim().length === 0)
            return;
        window.location = constants.ROOT_PATH + "course_search/?course=" + text;
    });
};


General.prototype.show_categories = function (data) {
    var html = GENERAL.get_html_data(GENERAL.CATEGORY_TEMPLATE, data);
    $(GENERAL.CATEGORIES_DIV_ID).html(html);
};

General.prototype.show_courses = function (courses) {
    var courses_array = [];
    for (var key in courses) {
        courses_array.push(courses[key]);
    }
    GENERAL.sort_assoc_array_by(courses_array, GENERAL.COURSE_VIEWS_PROP, constants.DESC_SORT);

    var category_name = courses_array.length > 0 ? courses_array[0]["category_name"] : "";
    var category_object = {category_id: "", category_name: category_name};
    var html = GENERAL.get_html_data(GENERAL.CATEGORY_TEMPLATE, category_object);
    $(GENERAL.COURSES_DIV_ID).html(html);
    $(GENERAL.ALL_COURSES_AVAILABLE_ID).html(category_name);

    for (var i = 0; i < courses_array.length; ++i) {
        GENERAL.show_course(null, courses_array[i]);
    }
};
General.prototype.show_popular_categories = function (data) {
    var html = GENERAL.get_html_data(GENERAL.POPULAR_CATEGORY_TEMPLATE, data);
    $(GENERAL.POPULAR_CATEGORIES_DIV_ID).html(html);
};

General.prototype.show_popular_courses = function (data) {
    $(GENERAL.CATEGORIES_DIV_ID).html("");
    for (var key in data) {
        GENERAL.show_category(data[key]);
    }
};

General.prototype.show_category = function (category) {
    var html = GENERAL.get_html_data(GENERAL.CATEGORY_TEMPLATE, category);
    $(GENERAL.CATEGORIES_DIV_ID).append(html);
    var category_id = category["category_id"];

    $(GENERAL.SEE_ALL_BUTTON_ID + category_id).on("click", function (e) {
        e.stopPropagation();
        window.location = window.location + "all_courses/?category=" + category_id;
    });

    var courses = category["courses"];

    var courses_length = 0;
    var courses_array = [];
    for (var key in courses) {
        courses_array.push(courses[key]);
        ++courses_length;
    }
    GENERAL.sort_assoc_array_by(courses_array, GENERAL.COURSE_VIEWS_PROP, constants.DESC_SORT);
    for (var i = 0; i < courses_array.length; ++i) {
        GENERAL.show_course(category_id, courses_array[i]);
    }

    if (courses_length === 0)
        $(GENERAL.CATEGORY_DIV_ID + category_id).hide();
};


General.prototype.show_course = function (category_id, course) {
    var html = GENERAL.get_html_data(GENERAL.COURSE_TEMPLATE, course);
    if (category_id !== null)
        $(GENERAL.CATEGORY_DIV_ID + category_id).append(html);
    else
        $(GENERAL.COURSES_DIV_ID).append(html);

    var course_id = course[GENERAL.COURSE_ID_ATTR];
    $(GENERAL.COURSE_DIV_ID + course_id).on("click", function () {
        window.location = constants.ROOT_PATH + "courses?course_id=" + course_id + "&lesson_index=0";
    });

    var programming_languages = course["programming_languages"];
    if (programming_languages.length === 0)
        $(GENERAL.COURSE_AVAILABLE_IN_ROW_ID + course_id).hide();
    
    $(GENERAL.COURSE_AVAILABLE_IN_CELL_ID + course_id).html("");
    for (var i = 0; i < programming_languages.length; ++i)
        GENERAL.show_programming_language(course_id, programming_languages[i]);
};

General.prototype.show_programming_language = function (course_id, programming_language) {
    var programming_language_object = {programming_language_name: programming_language};
    var html = GENERAL.get_html_data(GENERAL.PROGRAMMING_LANGUAGE_TEMPLATE, programming_language_object);
    $(GENERAL.COURSE_AVAILABLE_IN_CELL_ID + course_id).append(html);
};

General.prototype.get_html_data = function (template, data) {
    var template_html = $(template).html();
    var html_maker = new htmlMaker(template_html);
    var html = html_maker.getHTML(data);
    return html;
};


General.prototype.sort_assoc_array_by = function (array, attr, sort_type) {
    if (sort_type == constants.ASC_SORT) {
        array.sort(function (a, b) {
            if (a[attr] == b[attr])
                return 0
            if (a[attr] > b[attr])
                return 1;
            else
                return -1;
        });
    }
    else if (sort_type == constants.DESC_SORT) {
        array.sort(function (a, b) {
            if (a[attr] == b[attr])
                return 0
            if (a[attr] < b[attr])
                return 1;
            else
                return -1;
        });
    }
};

General.prototype.get_page_param = function (param, url) {
    var results = new RegExp('[\?&]' + param + '=([^&#]*)').exec(url);
    if (results == null) {
        return null;
    }
    else {
        return results[1] || 0;
    }
};

General.prototype.highlight_word_by_word = function (text, match) {
    var words = match.match(/\S+/g);
    if (words.length === 0)
        return text;
    var html = text;
    words = words.filter(GENERAL.only_unique);
    for (var i = 0; i < words.length; ++i) {
        html = GENERAL.highlight(html, words[i]);
    }
    return html;
};


General.prototype.highlight = function (text, match) {
    match = match.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
    if (match == undefined || match.trim().length == 0 || text == undefined || text.trim().length == 0)
        return text;
    var html = "";

    var match_index = text.toLowerCase().trim().search(match.toLowerCase().trim());

    while (text.length > 0) {
        if (match_index != -1) {
            html += text.substring(0, match_index) + "<b>" + text.substring(match_index, match_index + match.length) + "</b>";
            text = text.substring(match_index + match.length, text.length);
            match_index = text.toLowerCase().trim().search(match.toLowerCase().trim());
        }
        else {
            html += text;
            break;
        }
    }
    return html;
};

General.prototype.only_unique = function (value, index, self) {
    return self.indexOf(value) === index;
};

General.prototype.getChar=function(index) {
    return 'ABCDEFGHIJKLMNOPQRSTUVWXZY'[index];
};

