<?php

class Response {
    
    function Response($data)
        {
            header('Content-Type:application/json;charset=utf-8');
            echo json_encode($data);
        }
        
        function ResponseZipFile($filename, $path = TEMP_FILE_PATH)
        {
            assert(isset($filename) && strlen($filename) > 0 && strlen($filename) < 64 && preg_match('/[A-Za-z0-9_.-]+/', $filename));
            $attachment_location = $path . $filename;
            
            if (file_exists($attachment_location)) {
    
                header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                header("Cache-Control: public"); // needed for internet explorer
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($attachment_location));
                header("Content-Disposition: attachment; filename=file.zip");
                readfile($attachment_location);
                die();        
            } else {
                die("Error: File not found");
            } 
        }
}