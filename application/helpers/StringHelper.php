<?php
if (!defined('ENT_SUBSTITUTE')) define('ENT_SUBSTITUTE', 8);
class StringHelper{

    public static function filterCountry($string_value)
    {
        //return htmlentities($string_value, ENT_SUBSTITUTE, "cp1252");
        return iconv ( "WINDOWS-1252" , "UTF-8" , $string_value );
        //return $string_value;
    }

    public static function filterString($string_value)
    {
        //return htmlentities($string_value, ENT_SUBSTITUTE, "cp1252");
        //return iconv ( "WINDOWS-1252" , "UTF-8" , $string_value );
        return $string_value;
    }
}