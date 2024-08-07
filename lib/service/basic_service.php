<?php


class BasicService {
        
        protected $Response;
        
        function __construct(Response $responseClass = null) {
            $this->Response = isset($responseClass) ? $responseClass : new Response();
        }
        
        function Response($data) {
            return $this->Response->Response($data);
        }
        
        function ResponseZipFile($filename, $path) {
            return $this->Response->ResponseZipFile($filename, $path);
        }
        
        
    }