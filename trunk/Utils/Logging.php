<?php

class Logging {
    
     public static function var_error_log($message, $object, $fileName){
        ob_start(); // start buffer capture
        var_dump($object);
        $contents = ob_get_contents();
        ob_end_clean();
        error_log($message . $contents, 3, $fileName);
    }
}

