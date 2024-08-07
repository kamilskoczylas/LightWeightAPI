<?php

class FakeResponse extends Response {
    
    function Response($data)
        {
            return $data;
        }
        
        function ResponseZipFile($filename, $path = TEMP_FILE_PATH)
        {
            assert(isset($filename) && strlen($filename) > 0 && strlen($filename) < 64 && preg_match('/[A-Za-z0-9_.-]+/', $filename));
            $attachment_location = $path . $filename;
            
            return $attachment_location;
        }
}