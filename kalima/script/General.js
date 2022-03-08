var GENERAL = new General(); //GENERAL object

function General() {
    //constants
    this.NUM_MILLISECONDS_IN_DAY = 1000 * 60 * 60 * 24;
    this.LEVEL_LABELS={"1":"Level 1","2":"Level 2","3":"Level 3"};
    this.COURSES_DB = null;
    this.DOWN_KEY = 40;
    this.UP_KEY = 38;
    this.RIGHT_KEY = 39;
    this.LEFT_KEY = 37;
    this.ENTER_KEY = 13;  
    this.MAX_COURSE_SEARCH_SUGGESTIONS = 6;
    
    //requests
    this.POPULAR_COURSES_REQUEST="popular_courses";
    this.COURSE_LESSON_PLAN_DISPLAY = "course_lesson_plan_display";
    this.ALL_COURSES_REQUEST="all_courses";
    
    //events
    this.COURSES_POPULATED_EVENT = "course_populated_event";
    
    //element IDs
    this.MENU_ICON = "#menu_icon";
    this.LOGO="#logo";
    this.LINKS_DIV = "#links";
    this.COURSE_LESSON_PLAN_DISPLAY_DIV="#course_lesson_plan_display";
    this.LESSONS_REQUEST = "lessons";
    this.LEVELS_DIV_ID="#levels";
    this.LEVEL_DIV_ID="#level_";
    this.COURSES_DIV_ID="#courses";
    this.LOADING_ICON_ID="#loading_icon";
    this.COURSE_PREREQUISITE_LINK_DIV_ID="#pre_link_";
    this.COURSE_PREREQUISITES_NONE_ID="#course_prerequisites_none";
    this.COURSE_SEARCH_BOX_ID="#course_search_box";
    this.COURSE_SUGGESTIONS_WRAPPER_ID="#course_suggestions_wrapper";
    this.COURSE_SEARCH_SUGGESTION_ID="#course_search_suggestion_";
    this.COURSE_SEARCH_NAME_SUGGESTION_ID="#course_name_suggestion_";
    this.COURSE_SEARCH_LEVEL_SUGGESTION_ID="#course_level_suggestion_";
    this.COURSE_SEARCH_BUTTON_ID = "#course_search_button";
    this.STUDENT_RELATION_TO_GUARDIAN_ID="#student_relation_to_guardian_";


     
    
    //element classes
    this.COURSE_CLASS=".course";
    this.COURSE_SUGGESTION_CLASS = ".search_suggestion";
    this.COURSE_HIGHLIGHTED_SUGGESTION_CLASS = ".search_suggestion_highlighted";
    this.COURSE_HIGHLIGHTED_SUGGESTION_NAME = "search_suggestion_highlighted"; 
    this.COUNTRY_COMBOBOX_CLASS=".country_combobox";
    this.STUDENT_GENDER_COMBOBOX_CLASS=".student_gender_combobox";

    
    //template IDs
    this.LEVEL_TEMPLATE_ID="#level-template";
    this.COURSE_TEMPLATE_ID="#course-template";
    this.COURSE_LESSON_PLAN_DISPLAY_TEMPLATE="#course-lesson-plan-display-template";
    this.COURSE_SEARCH_SUGGESTION_TEMPLATE_ID="#course-search-suggestion-template";
    this.COUNTRY_COMBOBOX_TEMPLATE_ID="#country-combobox-template";
    this.STATE_SUGGESTION_TEMPLATE_ID="#state-suggestion-template";
    this.CITY_SUGGESTION_TEMPLATE_ID="#city-suggestion-template";
    this.STUDENT_MALE_OPTIONS_TEMPLATE_ID="#student-male-options-template";
    this.STUDENT_FEMALE_OPTIONS_TEMPLATE_ID="#student-female-options-template";
    
    
    //params
    this.COURSE_ID_PARAM="course_id";
    this.COURSE_PARAM="course";
    this.COURSE_NAME_PARAM="course_name";
    this.COURSE_LEVEL_PARAM="course_level";
    
    //properties
    this.COURSE_ID_PROP="data-course-id";
    this.COURSE_NAME_PROP="data-course-name";
    this.COURSE_LEVEL_PROP="data-course-level";
    this.STUDENT_ID_PROP="data-student-id";
    
    //option values
    this.MALE_OPTION_VALUE="male";
    this.FEMALE_OPTION_VALUE="female";
    
    //data attributes
    this.COURSE_PREREQUISITES_D_ATTR="prerequisites";
    this.COURSE_ID_D_ATTR="course_id";
    this.COURSE_ID_ATTR="course_id";
    this.COURSE_NAME_ATTR="course_name";
    this.COURSE_LEVEL_ATTR="course_level";
    this.COURSE_DESCRIPTION_ATTR="course_description";
    this.COURSE_DIALECT_ATTR="course_dialect";
    this.COURSE_ICON_ATTR="course_icon";
    this.COURSE_NUM_LESSONS_ATTR="course_num_lessons";
    this.COURSE_ARABIC_NAME_ATTR="course_arabic_name";
    
    //local storage
    
    this.DATETIME_LS = "datetime";
    this.COURSES_LS = "courses";
    

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

General.prototype.signup_init=function(){
  GENERAL.set_html(GENERAL.COUNTRY_COMBOBOX_TEMPLATE_ID,DATA.COUNTRIES,GENERAL.COUNTRY_COMBOBOX_CLASS);
  $(GENERAL.STUDENT_GENDER_COMBOBOX_CLASS).on("change",function(){
     var value=$(this).val();
     var student_id=$(this).attr(GENERAL.STUDENT_ID_PROP);
     switch(value){
         case GENERAL.MALE_OPTION_VALUE:
              GENERAL.set_html_no_data(GENERAL.STUDENT_MALE_OPTIONS_TEMPLATE_ID,GENERAL.STUDENT_RELATION_TO_GUARDIAN_ID+student_id);
              break;
         case GENERAL.FEMALE_OPTION_VALUE:
              GENERAL.set_html_no_data(GENERAL.STUDENT_FEMALE_OPTIONS_TEMPLATE_ID,GENERAL.STUDENT_RELATION_TO_GUARDIAN_ID+student_id);
             break;             
     }
  });
  
};

General.prototype.course_search_init = function () {
    GENERAL.refresh_courses_db();
    var course_name = decodeURI(GENERAL.get_page_param(GENERAL.COURSE_NAME_PARAM, window.location.href));
    var course_level= decodeURI(GENERAL.get_page_param(GENERAL.COURSE_LEVEL_PARAM, window.location.href));
    var course_text=course_name+" ("+course_level+")";
    $(GENERAL.COURSE_SEARCH_BOX_ID).val(course_text);
    GENERAL.search_for_courses(course_text);
    $(GENERAL.COURSE_SEARCH_BUTTON_ID).off("click");
    $(GENERAL.COURSE_SEARCH_BUTTON_ID).on("click", function (e) {
        $(GENERAL.COURSE_SUGGESTIONS_WRAPPER_ID).hide();
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
        GENERAL.find_courses_live_suggestions(text, GENERAL.COURSE_SUGGESTIONS_WRAPPER_ID, GENERAL.COURSE_SEARCH_SUGGESTION_TEMPLATE_ID);
    });
    $(GENERAL.COURSE_SEARCH_BOX_ID).on("keydown", function (e) {
        GENERAL.on_course_search_box_arrow_typed(GENERAL.COURSE_SUGGESTIONS_WRAPPER_ID, e);
    });
};

General.prototype.search_for_courses = function (text) {
    $(GENERAL.LOADING_ICON_ID).show();
    var result=GENERAL.find_courses(text);
    $(GENERAL.COURSES_DIV_ID).html("");
    $(GENERAL.LOADING_ICON_ID).hide();
    if(result.length>0)
        GENERAL.set_html(GENERAL.COURSE_TEMPLATE_ID,result,GENERAL.COURSES_DIV_ID);
    GENERAL.bind_course_events();
};


General.prototype.init = function () {
    
    GENERAL.refresh_courses_db();
    
    GENERAL.menu_event_setup();
    
    GENERAL.get_courses();
    
    GENERAL.bind_main_page_events();
    
    GENERAL.bind_search_button_events();
};

General.prototype.bind_main_page_events=function(){
  $(GENERAL.COURSE_SEARCH_BOX_ID).on("keyup",function(e){
      if (e.which == GENERAL.DOWN_KEY || e.which == GENERAL.UP_KEY || e.which == GENERAL.ENTER_KEY)//if the arrow keys/ENTER are typed, ignore
            return;
      GENERAL.find_courses_live_suggestions($(GENERAL.COURSE_SEARCH_BOX_ID).val(),GENERAL.COURSE_SUGGESTIONS_WRAPPER_ID,GENERAL.COURSE_SEARCH_SUGGESTION_TEMPLATE_ID); 
  });
  
    $(GENERAL.COURSE_SEARCH_BOX_ID).on("keydown", function (e) {
        GENERAL.on_course_search_box_arrow_typed(GENERAL.COURSE_SUGGESTIONS_WRAPPER_ID, e);
    });
  
      $("html").on("click", function () {
        $(GENERAL.COURSE_SUGGESTIONS_WRAPPER_ID).hide();
    });
    
};

General.prototype.bind_search_button_events=function(){
    $(GENERAL.COURSE_SEARCH_BUTTON_ID).on("click", function (e) {
        GENERAL.search_button_event_handler();
    });   
};

General.prototype.search_button_event_handler=function(){
    var course_search_text=$(GENERAL.COURSE_SEARCH_BOX_ID).val();
    var course_search_object=GENERAL.break_course_name_level(course_search_text);
    var course_name=course_search_object.course_name;
    var course_level=course_search_object.course_level;
    window.open(constants.COURSE_SEARCH_PATH+course_name+"&"+GENERAL.COURSE_LEVEL_PARAM+"="+course_level);   
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
        var course_level=$(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_CLASS).attr(GENERAL.COURSE_LEVEL_PROP);
        var course_id=$(GENERAL.COURSE_HIGHLIGHTED_SUGGESTION_CLASS).attr(GENERAL.COURSE_ID_PROP);
        $(GENERAL.COURSE_SEARCH_BOX_ID).val(course_name+" ("+course_level+")");
        $(GENERAL.COURSE_SEARCH_BOX_ID).attr(GENERAL.COURSE_ID_PROP,course_id);
        $(div).hide();
    }
};

General.prototype.get_course_lesson_plan_display= function () {
    var course_id = decodeURI(GENERAL.get_page_param(GENERAL.COURSE_ID_PARAM, window.location.href));   
    $.ajax({
        method: constants.POST_REQUEST,
        url: constants.CONTROLLER_PATH,
        dataType: "json",
        data: {type: GENERAL.COURSE_LESSON_PLAN_DISPLAY,course_id:course_id},
        success: function (data) {
            $(GENERAL.LOADING_ICON_ID).hide();
            GENERAL.set_html(GENERAL.COURSE_LESSON_PLAN_DISPLAY_TEMPLATE,data,GENERAL.COURSE_LESSON_PLAN_DISPLAY_DIV);
            GENERAL.assign_course_prerequisite_links(data);
        }
    });
};

General.prototype.assign_course_prerequisite_links= function (course_data) {
    $(GENERAL.COURSE_PREREQUISITES_NONE_ID).hide(); 
    
    var prerequisites=course_data[GENERAL.COURSE_PREREQUISITES_D_ATTR];
    var prerequisites_length=prerequisites.length;
    if(prerequisites_length==0){ //no prerequesited
        $(GENERAL.COURSE_PREREQUISITES_NONE_ID).show();
        return;
    }
    
    
      for (i=0;i<prerequisites_length;i++){
          var prerequisite=prerequisites[i];
          var course_id=prerequisite[GENERAL.COURSE_ID_D_ATTR];
          $(GENERAL.COURSE_PREREQUISITE_LINK_DIV_ID+course_id).attr("href",constants.COURSE_PATH+course_id);
  }
  
  var last_pre_course_id=prerequisites[prerequisites_length-1][GENERAL.COURSE_ID_D_ATTR];
  $("#pre_link_comma_"+last_pre_course_id).hide();
    
};

General.prototype.get_courses= function () {
    $.ajax({
        method: constants.POST_REQUEST,
        url: constants.CONTROLLER_PATH,
        dataType: "json",
        data: {type: GENERAL.POPULAR_COURSES_REQUEST},
        success: function (data) {
            $(GENERAL.LOADING_ICON_ID).hide();
            GENERAL.show_popular_courses(data);
            GENERAL.bind_course_events();
        }
    });
};





General.prototype.course_lesson_plan_display_init = function () {
    GENERAL.menu_event_setup();
    GENERAL.get_course_lesson_plan_display();  
};

General.prototype.menu_event_setup = function (data) {
    $(GENERAL.MENU_ICON).on("click", function (e) {
        e.stopPropagation();
        var display = $(GENERAL.LINKS_DIV).css("display");
        if (display === "block")
            $(GENERAL.LINKS_DIV).hide();
        else
            $(GENERAL.LINKS_DIV).show();
    });
    
    $(GENERAL.LOGO).click(function(e){
        e.stopPropagation();
        window.location=constants.ROOT_PATH;
    });
};


General.prototype.show_popular_courses = function (data) {
  for (var level_id in data){
      var level_name=GENERAL.LEVEL_LABELS[level_id];
      var label_data=[{"level_id":level_id,"level_name":level_name}];
      GENERAL.append_html(GENERAL.LEVEL_TEMPLATE_ID,label_data,GENERAL.LEVELS_DIV_ID);
      var course_data=data[level_id];
      GENERAL.set_html(GENERAL.COURSE_TEMPLATE_ID,course_data,GENERAL.LEVEL_DIV_ID+level_id);
  }  
};

General.prototype.bind_course_events = function () {
    $(GENERAL.COURSE_CLASS).off("click");
    $(GENERAL.COURSE_CLASS).on("click",function(){
        var course_id=$(this).attr(GENERAL.COURSE_ID_PROP);
       window.open(constants.COURSE_PATH+course_id); 
    });
};

General.prototype.get_all_courses = function () {
    $.ajax({
        method: constants.POST_REQUEST,
        url: constants.CONTROLLER_PATH,
        dataType: "text",
        data: {type: GENERAL.ALL_COURSES_REQUEST},
        success: function (data) {
            localStorage.removeItem(GENERAL.COURSES_LS);
            localStorage.setItem(GENERAL.COURSES_LS, data);
            localStorage.setItem(GENERAL.DATETIME_LS, new Date());
        }
    });
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
};

General.prototype.find_courses_live_suggestions = function (text, div, template) {
    $(div).show();
    $(div).html(""); //empty search suggestions
    
    var result=GENERAL.find_courses(text);
    if(result.length==0){
        $(div).hide();
        return;
    }
    
    var restricted_result=result.slice(0,GENERAL.MAX_COURSE_SEARCH_SUGGESTIONS);
    GENERAL.set_html(template,restricted_result,div);
    
    for (var i = 0; i < restricted_result.length; ++i) {
        var course_id = restricted_result[i][GENERAL.COURSE_ID_ATTR];
        GENERAL.bind_course_suggestion_events(course_id,div);
    }
    
};

General.prototype.break_course_name_level=function(text){
    var course_text={course_name:"",course_level:""};
    text = text === null || text.length === 0 || text.trim().length === 0 ? "" : text.toLowerCase().trim();
    if(text=="")
        return course_text;
    
    var course_level_matches=/\(([^)]+)\)/.exec(text);
    var course_level_text=course_level_matches==null?"":course_level_matches.length==0?"":course_level_matches[course_level_matches.length-1];
    var course_name=text.replace(/\(([^)]+)\)/, "");      
    course_text.course_name=course_name;
    course_text.course_level=course_level_text;
    
    return course_text;
};

General.prototype.find_courses = function (text) {
    var result=[];
    var course_search_text=GENERAL.break_course_name_level(text);
    if(course_search_text.course_name==="" && course_search_text.course_level==="")
        return result;
    
    GENERAL.refresh_courses_db();
    var courses_db = JSON.parse(localStorage.getItem(GENERAL.COURSES_LS));
    
    var course_search_name=course_search_text.course_name;
    var course_search_level=course_search_text.course_level;

    for (var i = 0; i < courses_db.length; ++i) {
        var course_id = courses_db[i][GENERAL.COURSE_ID_ATTR];
        var course_name = courses_db[i][GENERAL.COURSE_NAME_ATTR];
        var course_description = courses_db[i][GENERAL.COURSE_DESCRIPTION_ATTR];
        var course_dialect = courses_db[i][GENERAL.COURSE_DIALECT_ATTR];
        var course_level=courses_db[i][GENERAL.COURSE_LEVEL_ATTR];
        var course_icon=courses_db[i][GENERAL.COURSE_ICON_ATTR];
        var course_num_lessons=courses_db[i][GENERAL.COURSE_NUM_LESSONS_ATTR];
        var course_arabic_name=courses_db[i][GENERAL.COURSE_ARABIC_NAME_ATTR];
        var course_level_search_flag=course_search_level==""?true:GENERAL.search_word_by_word(course_search_level, course_level);
        if ((GENERAL.search_word_by_word(course_search_name, course_name) || GENERAL.search_word_by_word(course_search_name, course_description) || GENERAL.search_word_by_word(course_search_name, course_dialect))&&course_level_search_flag) {
            var highlighted_course_name = GENERAL.highlight_word_by_word(course_name, text);
            var highlighted_course_level = GENERAL.highlight_word_by_word(course_level, text);
            var course_object = {course_id: course_id, course_name: course_name, highlighted_course_name: highlighted_course_name,
                                 course_level:course_level,highlighted_course_level:highlighted_course_level,course_icon:course_icon,
                                 course_num_lessons:course_num_lessons,course_dialect:course_dialect,course_arabic_name:course_arabic_name};
            result.push(course_object);

        }
    }
    return result;
};

General.prototype.bind_course_suggestion_events = function (course_id, suggestions_div) {
    $(GENERAL.COURSE_SEARCH_SUGGESTION_ID + course_id).on("click", function (e) {
        e.stopPropagation();
        var course_name = $(GENERAL.COURSE_SEARCH_NAME_SUGGESTION_ID + course_id).text();
        var course_level=$(GENERAL.COURSE_SEARCH_LEVEL_SUGGESTION_ID + course_id).text();
        $(GENERAL.COURSE_SEARCH_BOX_ID).val(course_name+" "+course_level+"");
        $(suggestions_div).hide();
        $(GENERAL.COURSE_SEARCH_BOX_ID).attr(GENERAL.COURSE_ID_PROP,course_id);
    });
};


General.prototype.append_html = function (template_id, data, div_id) {
    var html=GENERAL.get_html_data(template_id,data);
    $(div_id).append(html);
};

General.prototype.set_html_no_data = function (template_id, div_id) {
    var html = $(template_id).html();
    $(div_id).html(html);
};

General.prototype.set_html = function (template_id, data, div_id) {
    var html=GENERAL.get_html_data(template_id,data);
    $(div_id).html(html);
};


General.prototype.get_html_data = function (template_id, data) {
    var template_html = $(template_id).html();
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

