<?php
class TratarString
{
    public static function escapeString($string)
    {
        $string = str_replace("'", "''", $string);
        return $string;
    }
}