<?php


require_once "min.php";

$version="?1";
$destination_script_path="../_a_production/script/";
$destination_style_path="../_a_production/style/";
$destination_html_path="../_a_production/";

$css_path="../style/";
$js_path="../script/";
$html_path="../";


$app_css=array("general.css","main.css","account.css","budget.css","transaction.css","analytics.css");
$app_css_name="app.css";
$app_css_files=array("app/index.html");
produce_min_css($app_css, $app_css_name, $css_path, $destination_style_path);
produce_html_style_files($html_path, $destination_html_path, $app_css_files, $app_css, $app_css_name, $css_path,$version);

$help_center_css=array("general.css","main.css","help_center.css");
$help_center_app_css_name="help_center_app.css";
$help_center_css_files=array("help_center/index.html","help_center/detail.html","about_us/index.html","contact_us/index.html","features/index.html","forgot_password/index.html","privacy_policy/index.html","reset_password/index.html","terms_of_service/index.html","signup/index.html");
produce_min_css($help_center_css, $help_center_app_css_name, $css_path, $destination_style_path);
produce_html_style_files($html_path, $destination_html_path, $help_center_css_files, $help_center_css, $help_center_app_css_name, $css_path,$version);

$login_css=array("general.css","new_login.css");
$login_app_css_name="login_app.css";
$login_css_files=array("login/index.html","signup/index_1.html");
produce_min_css($login_css, $login_app_css_name, $css_path, $destination_style_path);
produce_html_style_files($html_path, $destination_html_path, $login_css_files, $login_css, $login_app_css_name, $css_path,$version);

$app_js=array("General_Object.js","Login_Object.js","Category.js","Budget.js","Account.js","Transaction.js","Analytics.js","Constants.js","general.js","main.js","DatePicker.js");
$app_js_name="app.js";
produce_min_js($app_js, $app_js_name, $js_path, $destination_script_path);
$app_js_files=array("app/index.html");
produce_html_script_files($destination_html_path,$destination_html_path,$app_js_files,$app_js,$app_js_name,$js_path,$version);


$help_center_js=array("general.js","Constants.js","General_Object.js","Help_Center.js");
$help_center_js_name="help_center_app.js";
produce_min_js($help_center_js, $help_center_js_name, $js_path, $destination_script_path);
$help_center_js_files=array("help_center/index.html","help_center/detail.html","contact_us/index.html","forgot_password/index.html","reset_password/index.html");
produce_html_script_files($destination_html_path,$destination_html_path,$help_center_js_files,$help_center_js,$help_center_js_name,$js_path,$version);

$login_center_js=array("General_Object.js","Login_Object.js","Constants.js","general.js","login.js");
$login_center_js_name="login_app.js";
produce_min_js($login_center_js, $login_center_js_name, $js_path, $destination_script_path);
$login_js_files=array("login/index.html");
produce_html_script_files($destination_html_path,$destination_html_path,$login_js_files,$login_center_js,$login_center_js_name,$js_path,$version);


$signup_js=array("homepage.js","General_Object.js","Login_Object.js","Constants.js","general.js","login.js");
$signup_js_name="signup_app.js";
produce_min_js($signup_js, $signup_js_name, $js_path, $destination_script_path);
$signup_js_files=array("signup/index_1.html");
produce_html_script_files($destination_html_path,$destination_html_path,$signup_js_files,$signup_js,$signup_js_name,$js_path,$version);




function produce_min_js($js_files_array,$new_js_file,$js_path,$destination_js_path){
    $js_content="";
for($i=0;$i<sizeof($js_files_array);++$i){
    $js_content.="\n".file_get_contents($js_path.$js_files_array[$i]);
}
file_put_contents($destination_js_path.$new_js_file, Minifier::minify($js_content));
}

function produce_min_css($css_files_array,$new_css_file,$css_path,$destination_css_path){
    $css_content="";
for($i=0;$i<sizeof($css_files_array);++$i){
    $css_content.="\n".file_get_contents($css_path.$css_files_array[$i]);
}
file_put_contents($destination_css_path.$new_css_file, $css_content);
}

function produce_html_script_files($path,$destination_path,$html_file_array,$js_array,$new_js_file_name,$js_path,$version){
    for($i=0;$i<sizeof($html_file_array);++$i){
        $file_contents=replace_script_tag($path.$html_file_array[$i], $js_array, $new_js_file_name,$js_path,$version);
        file_put_contents($destination_path.$html_file_array[$i], $file_contents);
    }
}

function replace_script_tag($file,$script_array,$new_js_file_name,$js_path,$version){
    $file_contents=file_get_contents($file);
    $replacement_script="<script type='text/javascript' src='$js_path$new_js_file_name$version'></script>";
    for($i=0;$i<sizeof($script_array);++$i){
        $script_tag="<script type='text/javascript' src='$js_path$script_array[$i]'></script>";
        if($i==0){
            $file_contents=str_replace($script_tag,$replacement_script,$file_contents);
        }
        else{
            $file_contents=str_replace($script_tag,"",$file_contents);
        }
    }
    return $file_contents;
}

function produce_html_style_files($path,$destination_path,$html_file_array,$style_array,$new_css_file_name,$css_path,$version){
    for($i=0;$i<sizeof($html_file_array);++$i){
        $file_contents=replace_style_tag($path.$html_file_array[$i], $style_array, $new_css_file_name,$css_path,$version);
        file_put_contents($destination_path.$html_file_array[$i], $file_contents);
    }
}

function replace_style_tag($file,$style_array,$new_css_file_name,$css_path,$version){
    $file_contents=file_get_contents($file);
    $replacement_style="<link rel='stylesheet' type='text/css' href='$css_path$new_css_file_name$version'>";
    for($i=0;$i<sizeof($style_array);++$i){
        $style_tag="<link rel='stylesheet' type='text/css' href='$css_path$style_array[$i]'>";
        if($i==0){
            $file_contents=str_replace($style_tag,$replacement_style,$file_contents);
        }
        else{
            $file_contents=str_replace($style_tag,"",$file_contents);
        }
    }
    return $file_contents;
}