<?php

function get_navegador(){

    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
    elseif (strpos($user_agent, 'Edge')) return 'Edge';
    elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
    elseif (strpos($user_agent, 'Safari')) return 'Safari';
    elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
    elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
    return 'Other';
}

function get_icon_navegador($browser){
    switch ($browser){
        case 'Firefox':
            $icon = 'firefox.svg';break;
        case 'Chrome':
            $icon = 'chrome.png';break;
        case "Internet Explorer":
            $icon = 'ie.png'; break;
        case "Edge":
            $icon = "edge.png";break;
        case "Opera":
            $icon = "opera.png";break;
        case "Safari":
            $icon = "safari.png";break;
        case "Other":
            $icon = "transparente.png";break;
    }

    return plugins_url() . '/logLogin/img/' . $icon;
}

function total_rows(){
    global $wpdb;
    $table_name = $wpdb-> prefix . 'log_logins';
    $wpdb->get_results("select * from $table_name");
    $rowcount = $wpdb->num_rows;
    return $rowcount;
}
?>