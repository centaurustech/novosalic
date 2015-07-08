<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Array
 *
 * @author 01129075125
 */
class TratarArray {
    public static function multi_array_search($variavelprocurada, $Array) {
        $Key = '';
        $co = 0;
        foreach ($Array as $key => $value) {
            if (is_array($value)) {
                $value = array_search($variavelprocurada, $value);
                
                if (!empty($value)) {//needle is found
                    $Key[$co] = $key;
                }
            }
            $co++;
        }
        return $Key;
    }
}
?>
