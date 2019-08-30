<?php


function msectime() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

/**
 * 日期转毫秒
 */
function getDateToMesc($mescdate)
{
    list($usec, $sec) = explode(".", $mescdate);
    $date = strtotime($usec);
    $return_data = str_pad($date . $sec, 13, "0", STR_PAD_RIGHT);
    return $msectime = $return_data;
}