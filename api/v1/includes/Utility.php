<?php

class Utility {
    public static function isValidIntegerArrayQueryParameter($parameter) {
        $elements = explode(',', $parameter);
    
        foreach ($elements as $value) {
            if (!ctype_digit($value)) {
                return false;
            }
        }
    
        return true;
    }
}

?>