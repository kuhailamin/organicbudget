<?php

function convert_money($amount, $from, $to) {

    $url = file_get_contents('https://free.currencyconverterapi.com/api/v5/convert?q=' . $from . '_' . $to . '&compact=ultra&apiKey=5ff533471a1e50e2d035');
    $json = json_decode($url, true);
    $rate = implode(" ",$json);
    $total = $rate * $amount;
    return round($total);
    }
    
    echo convert_money(200,"USD","AED");

?>