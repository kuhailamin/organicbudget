$(document).ready(get_tasks);
var database={};
var sort_icon_alpha_up="<i class='fas fa-sort-alpha-up'></i>";
var sort_icon_alpha_down="<i class='fas fa-sort-alpha-down-alt'></i>";
var sort_icon_amount_up="<i class='fas fa-sort-amount-up'></i>";
var sort_icon_amount_down="<i class='fas fa-sort-amount-down'></i>";
var sort_icon_default="<i class='fa fa-sort' aria-hidden='true'></i>";




function get_tasks(){
    $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "tasks"},
        success: function (data) {
            display_tasks(data);
        }
    });    
}

//used to complete/uncomplete task
function complete_task(checkbox){
    
    var id=$(checkbox).attr("data-task-id");
    var status=$(checkbox).is(":checked");
     $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "complete",id:id,status:status},
        success: function (data) {
            if(data.search("success")!=-1){
                get_tasks();
            }
        }
    });    
}


function add_task(){
    var name=$("#new_name").val();
    var deadline=$("#new_due_date").val();
    var priority=$("#priority").val();
    
      $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "add",name:name,deadline:deadline,priority:priority},
        success: function (data) {
            if(data.search("success")!=-1){
                get_tasks();
                alert("The task has been added successfully");
            }
        }
    });     
}

function make_editable(input){
    $(input).css("border","1px solid lightgray");
    
}

function make_priority_editable(input){
    $(input).hide();
    var id=$(input).attr("data-task-id");
    $("#priority_"+id).show();
}

function formatDate(date_string){
    var date_o=stringToDate(date_string,"dd/mm/yyyy","/");
    return date_o.getFullYear()+"-"+(date_o.getMonth()+1)+"-"+date_o.getDate();    
}

function edit_task(input){
    
    $(input).css("border-width","0px");

    var id=$(input).attr("data-task-id");
    var name=$("#name_"+id).val();
    var date_s=formatDate($("#date_"+id).val());
    var priority=$("#priority_"+id).val();
    
      $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "edit",id:id,name:name,date:date_s,priority:priority},
        success: function (data) {
            if(data.search("success")!=-1){
                get_tasks();
                alert("The task has been editted successfully");
            }
        }
    });     
}

function delete_task(element){
    var id=$(element).attr("data-task-id");
       $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "text",
        data: {type: "delete",id:id},
        success: function (data) {
            if(data.search("success")!=-1){
                get_tasks();
                alert("The task has been deleted successfully");
            }
        }
    });       
}

function task_search(){
    
    var task_name=$("#task_name_search").val();
    var task_priority=$("#task_priority_search").val();
    var task_month=$("#task_month_search").val();
    var task_year=$("#task_year_search").val();
    var task_status=$("#task_status_search").val();
    
           $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "search",name:task_name,priority:task_priority,month:task_month,year:task_year,status:task_status},
        success: function (data) {
            display_tasks(data);
        }
    }); 
}

function display_tasks(data){
    if(data.length==0){
        data={"tasks":[{"name":"...","date":"...","id":"...","high_selected":"...","low_selected":"...","medium_selected":"...","priority":"..."}]};
        var html=get_html("task-template",data);
        $("#tasks").html(html);
        return;
    }
    var html=get_html("task-template",data);
    $("#tasks").html(html);
 
 //create the visualization
 database={};
 for(var i=0;i<data["tasks"].length;++i){
     var date=data["tasks"][i]["date"];
     if(database.hasOwnProperty(date))
         database[date]++;
     else
         database[date]=1;
 }
 
 //load the chart
  google.charts.load("current", {packages:["calendar"]});
  google.charts.setOnLoadCallback(drawChart); 
}

function drawChart(){
    var dataTable = new google.visualization.DataTable();
    dataTable.addColumn({ type: 'date', id: 'Date' });
    dataTable.addColumn({ type: 'number', id: 'Num of Tasks' });
    var array=[];
    for(var key in database){
        var date_o=stringToDate(key,"dd/mm/yyyy","/");
        array.push([date_o,database[key]]);
    }
    dataTable.addRows(array);
    var chart = new google.visualization.Calendar(document.getElementById('calendar_basic'));

       var options = {
         title: "",
         height: 350,
       };


       var chart = new google.visualization.Calendar(document.getElementById('calendar_basic'));

       var options = {
         title: "",
         height: 350,
       };

      chart.draw(dataTable, options);
    
}

function add_new_task_row(){
    $("#add_new_task_details").show();
    $("#add_new_task").hide();
}

function sort(element){
    var sort_by=$(element).attr("data-sort-by");
    var sort_type=$(element).attr("data-sort-type");
    
    $(".sort_heading").attr("data-sort-type","default");
    $(".sort_icon").html(sort_icon_default);
             //fix the icons
    if(sort_by=="task" && sort_type=="default"){
            sort_type="asc";
    }
    else if((sort_by=="date" || sort_by=="priority") && sort_type=="default"){
        sort_type="desc";
    }    

    
           $.ajax({
        method: "POST",
        url: "server/controller.php",
        dataType: "json",
        data: {type: "sort",sort_by:sort_by=="date"?"Deadline":sort_by=="task"?"Name":"Priority",sort_type:sort_type},
        success: function (data) {
            display_tasks(data);
            
            //fix the icons
    if(sort_by=="task"){
        if(sort_type=="default" || sort_type=="asc"){
            $("#"+sort_by+"_sort_heading").attr("data-sort-type","desc");
            $("#task_sort_icon").html(sort_icon_alpha_down);
        }
        else{
            $("#"+sort_by+"_sort_heading").attr("data-sort-type","asc");
            $("#task_sort_icon").html(sort_icon_alpha_up);
        }
    }
    else if(sort_by=="date" || sort_by=="priority"){
        if(sort_type=="default" || sort_type=="desc"){
            $("#"+sort_by+"_sort_heading").attr("data-sort-type","asc");
            $("#"+sort_by+"_sort_icon").html(sort_icon_amount_up);
        }
        else{
            $("#"+sort_by+"_sort_heading").attr("data-sort-type","desc");
            $("#"+sort_by+"_sort_icon").html(sort_icon_amount_down);
        }        
    }            
        }
    });    
    
}