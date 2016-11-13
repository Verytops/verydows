<?php
function truncate($string, $length = 30, $replace = '...')
{
    if(mb_strlen($string, 'utf-8') > $length)
    {
        $length -= min($length, strlen($replace));
        return mb_substr($string, 0, $length, 'utf-8') . $replace;
    }
    return $string;
}

function transtime($timestamp, $formatdate = 'Y-m-d')
{
    $distance = $_SERVER['REQUEST_TIME'] - $timestamp;
    if($distance < 300)
    {
        $string = '刚刚';
    }
    elseif($distance < 3600)
    {
        $string = floor($distance / 60).'分钟前';
    }
    elseif($distance < 86400)
    {
        $string = floor($distance / 3600).'小时前';
    }
    elseif($distance < 604800)
    {
        $string = floor($distance / 86400).'天前';
    }
    else
    {
        $string = date($formatdate, $timestamp); 
    }
    return $string;
}